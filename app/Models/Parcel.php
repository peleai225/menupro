<?php

namespace App\Models;

use App\Enums\ParcelStatus;
use App\Support\StorageUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Colis envoyé par un client, un commerçant ou un partenaire API.
 *
 * Le colis EST la course livreur (statut, livreur, étapes) : la table deliveries reste
 * réservée aux commandes de repas (order_id obligatoire, partout déréférencé).
 * Les briques communes sont réutilisées : livreurs (DeliveryDriver), recherche par
 * proximité (DriverAssignmentService), gains (driver_earnings.parcel_id), argent
 * liquide (driver_cash_debts.parcel_id), journal (LedgerService), push (PushChannel),
 * paiement (JekoGateway + webhook).
 */
class Parcel extends Model
{
    public const SENDER_CUSTOMER = 'customer';
    public const SENDER_RESTAURANT = 'restaurant';
    public const SENDER_PARTNER = 'partner';

    public const PAYMENT_JEKO = 'jeko';
    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_PARTNER = 'partner_billing';

    public const PAY_PENDING = 'pending';
    public const PAY_PAID = 'paid';
    public const PAY_FAILED = 'failed';
    public const PAY_REFUNDED = 'refunded';
    public const PAY_BILLED = 'billed_to_partner';

    public const SIZES = ['small', 'medium', 'large'];

    protected $fillable = [
        'reference', 'tracking_token', 'sender_type', 'customer_id', 'restaurant_id',
        'delivery_partner_id', 'created_by_user_id', 'external_reference', 'pickup_contact',
        'delivery_city_id', 'city',
        'pickup_address', 'pickup_latitude', 'pickup_longitude',
        'dropoff_address', 'dropoff_latitude', 'dropoff_longitude',
        'sender_name', 'sender_phone', 'recipient_name', 'recipient_phone',
        'size', 'description', 'note',
        'distance_km', 'price', 'driver_share', 'platform_commission', 'pricing_breakdown', 'cod_amount',
        'payment_method', 'payment_status', 'jeko_operator', 'payment_reference', 'payment_link_id',
        'paid_at', 'cash_collected', 'cash_collected_at', 'refund_status', 'refunded_at',
        'status', 'driver_id', 'pickup_photo_path',
        'handover_code', 'handover_attempts', 'handover_locked_at',
        'driver_search_started_at', 'last_dispatch_at', 'dispatch_attempts', 'no_driver_alerted_at',
        'assigned_at', 'picked_up_at', 'delivering_at', 'delivered_at', 'cancelled_at', 'failed_at',
        'cancelled_by', 'cancellation_reason', 'failure_reason',
    ];

    protected $hidden = ['handover_code'];

    protected $casts = [
        'status' => ParcelStatus::class,
        'pickup_contact' => 'array',
        'pricing_breakdown' => 'array',
        'pickup_latitude' => 'float',
        'pickup_longitude' => 'float',
        'dropoff_latitude' => 'float',
        'dropoff_longitude' => 'float',
        'distance_km' => 'float',
        'price' => 'integer',
        'driver_share' => 'integer',
        'platform_commission' => 'integer',
        'cod_amount' => 'integer',
        'cash_collected' => 'boolean',
        'handover_code' => 'encrypted',
        'handover_attempts' => 'integer',
        'dispatch_attempts' => 'integer',
        'paid_at' => 'datetime',
        'cash_collected_at' => 'datetime',
        'refunded_at' => 'datetime',
        'handover_locked_at' => 'datetime',
        'driver_search_started_at' => 'datetime',
        'last_dispatch_at' => 'datetime',
        'no_driver_alerted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivering_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // ─── Relations ──────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(DeliveryPartner::class, 'delivery_partner_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function deliveryCity(): BelongsTo
    {
        return $this->belongsTo(DeliveryCity::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeActiveForDriver(Builder $query): Builder
    {
        return $query->whereIn('status', ParcelStatus::driverActiveValues());
    }

    // ─── Machine d'états ────────────────────────────────────────────────────

    /**
     * Change le statut si la transition est autorisée (voir ParcelStatus::allowedTransitions)
     * et horodate l'étape. Ne sauvegarde PAS : l'appelant sauvegarde dans sa transaction.
     */
    public function transitionTo(ParcelStatus $next): bool
    {
        if (!$this->status->canTransitionTo($next)) {
            return false;
        }

        $this->status = $next;

        match ($next) {
            ParcelStatus::SEARCHING_DRIVER => $this->driver_search_started_at ??= now(),
            ParcelStatus::ASSIGNED => $this->assigned_at = now(),
            ParcelStatus::PICKED_UP => $this->picked_up_at = now(),
            ParcelStatus::DELIVERING => $this->delivering_at = now(),
            ParcelStatus::DELIVERED => $this->delivered_at = now(),
            ParcelStatus::CANCELLED => $this->cancelled_at = now(),
            ParcelStatus::FAILED => $this->failed_at = now(),
            default => null,
        };

        return true;
    }

    // ─── Outils ─────────────────────────────────────────────────────────────

    public static function generateReference(): string
    {
        do {
            // Sans 0/O, 1/I/L : lisible au téléphone
            $ref = 'COL-' . strtr(strtoupper(Str::random(6)), ['0' => 'X', 'O' => 'Y', '1' => 'Z', 'I' => 'W', 'L' => 'K']);
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    public static function generateTrackingToken(): string
    {
        return Str::random(48);
    }

    public static function generateHandoverCode(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function checkHandoverCode(string $code): bool
    {
        $expected = (string) $this->handover_code;

        return $expected !== '' && hash_equals($expected, $code);
    }

    public function isHandoverLocked(): bool
    {
        return $this->handover_locked_at !== null;
    }

    public function isCash(): bool
    {
        return $this->payment_method === self::PAYMENT_CASH;
    }

    public function isJeko(): bool
    {
        return $this->payment_method === self::PAYMENT_JEKO;
    }

    public function isPartner(): bool
    {
        return $this->sender_type === self::SENDER_PARTNER;
    }

    public function pickupPhotoUrl(): ?string
    {
        return StorageUrl::url($this->pickup_photo_path);
    }

    public function publicTrackingUrl(): string
    {
        return rtrim(config('apps.client_url'), '/') . '/?parcel=' . $this->tracking_token;
    }

    public function sizeLabel(): string
    {
        return config('parcels.sizes.' . $this->size, $this->size);
    }

    /** Prénom seul (suivi public, aucune donnée sensible). */
    public static function firstName(?string $name): ?string
    {
        $name = trim((string) $name);

        return $name === '' ? null : explode(' ', $name)[0];
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }
}

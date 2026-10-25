<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne du journal des transactions (registre unique des mouvements d'argent).
 *
 * Écriture seule : les montants sont figés à l'insertion (frais Jeko calculés avec le
 * taux en vigueur à ce moment-là). Seul le statut évolue (voir LedgerService::updateStatus).
 */
class LedgerEntry extends Model
{
    // ─── Types ────────────────────────────────────────────────────────────────
    public const TYPE_SUBSCRIPTION_RESTAURANT = 'subscription_restaurant';
    public const TYPE_SUBSCRIPTION_STAND = 'subscription_stand';
    public const TYPE_ORDER_APP_DELIVERY = 'order_app_delivery';
    public const TYPE_ORDER_APP_PICKUP = 'order_app_pickup';
    public const TYPE_ORDER_ONLINE_MENU = 'order_online_menu';
    public const TYPE_ORDER_POS = 'order_pos';
    public const TYPE_ORDER_STAND = 'order_stand';
    public const TYPE_PAYOUT_RESTAURANT = 'payout_restaurant';
    public const TYPE_REFUND_CUSTOMER = 'refund_customer';
    public const TYPE_DRIVER_COMMISSION = 'driver_commission';

    /** Pseudo-type de filtre : lignes portant des frais Jeko. */
    public const FILTER_JEKO_FEES = 'jeko_fees';

    public const TYPE_LABELS = [
        self::TYPE_SUBSCRIPTION_RESTAURANT => 'Abonnement restaurant',
        self::TYPE_SUBSCRIPTION_STAND => 'Abonnement stand',
        self::TYPE_ORDER_APP_DELIVERY => 'Commande app client — livraison',
        self::TYPE_ORDER_APP_PICKUP => 'Commande app client — à emporter',
        self::TYPE_ORDER_ONLINE_MENU => 'Commande menu en ligne / QR sur place',
        self::TYPE_ORDER_POS => 'Commande caisse (POS)',
        self::TYPE_ORDER_STAND => 'Commande stand',
        self::TYPE_PAYOUT_RESTAURANT => 'Reversement au restaurant',
        self::TYPE_REFUND_CUSTOMER => 'Remboursement client',
        self::TYPE_DRIVER_COMMISSION => 'Commission livreur',
    ];

    public const ORDER_TYPES = [
        self::TYPE_ORDER_APP_DELIVERY,
        self::TYPE_ORDER_APP_PICKUP,
        self::TYPE_ORDER_ONLINE_MENU,
        self::TYPE_ORDER_POS,
        self::TYPE_ORDER_STAND,
    ];

    public const SUBSCRIPTION_TYPES = [
        self::TYPE_SUBSCRIPTION_RESTAURANT,
        self::TYPE_SUBSCRIPTION_STAND,
    ];

    // ─── Statuts ──────────────────────────────────────────────────────────────
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PAID_OUT = 'paid_out';

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'En attente',
        self::STATUS_SUCCEEDED => 'Réussi',
        self::STATUS_FAILED => 'Échoué',
        self::STATUS_REFUNDED => 'Remboursé',
        self::STATUS_PAID_OUT => 'Reversé',
    ];

    /** Statuts dont les montants comptent dans les totaux (argent réellement passé). */
    public const SETTLED_STATUSES = [
        self::STATUS_SUCCEEDED,
        self::STATUS_PAID_OUT,
        self::STATUS_REFUNDED,
    ];

    // ─── Moyens / canaux ──────────────────────────────────────────────────────
    public const METHOD_LABELS = [
        'jeko' => 'Jeko',
        'wave' => 'Wave',
        'cash' => 'Espèces',
        'other' => 'Autre',
    ];

    public const CHANNEL_LABELS = [
        'client_app' => 'App client',
        'public_menu' => 'Menu public / QR',
        'pos' => 'Caisse (POS)',
        'back_office' => 'Back-office',
        'restaurant_space' => 'Espace restaurant',
        'system' => 'Automatique',
    ];

    public const VIA_LABELS = [
        'webhook' => 'Webhook passerelle',
        'back_office' => 'Back-office',
        'restaurant' => 'Espace restaurant',
        'driver_app' => 'App livreur',
        'client_api' => 'API app client',
        'site' => 'Site public',
        'system' => 'Tâche automatique',
        'reconstruction' => 'Reconstitution',
    ];

    protected $fillable = [
        'idempotency_key', 'occurred_at', 'type', 'direction', 'status', 'channel',
        'recorded_via', 'method', 'method_detail', 'restaurant_id', 'restaurant_name',
        'restaurant_plan', 'restaurant_plan_label', 'is_demo', 'source_type', 'source_id',
        'order_id', 'subscription_id', 'order_reference', 'payer_type', 'payer_name',
        'payer_phone', 'beneficiary_type', 'beneficiary_name', 'gateway_reference',
        'gateway_reference_alt', 'gross_amount', 'gateway_fee', 'gateway_fee_rate',
        'menupro_commission', 'restaurant_share', 'driver_share', 'net_menupro',
        'currency', 'description', 'metadata',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'is_demo' => 'boolean',
        'gross_amount' => 'integer',
        'gateway_fee' => 'integer',
        'gateway_fee_rate' => 'float',
        'menupro_commission' => 'integer',
        'restaurant_share' => 'integer',
        'driver_share' => 'integer',
        'net_menupro' => 'integer',
        'metadata' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class)->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withoutGlobalScope('restaurant');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class)->withoutGlobalScope('restaurant');
    }

    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_demo', false);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHOD_LABELS[$this->method] ?? ($this->method ?: '—');
    }

    public function getChannelLabelAttribute(): string
    {
        return self::CHANNEL_LABELS[$this->channel] ?? ($this->channel ?: '—');
    }

    public function getViaLabelAttribute(): string
    {
        return self::VIA_LABELS[$this->recorded_via] ?? ($this->recorded_via ?: '—');
    }

    /** Libellé des frais : « Frais Jeko » pour Jeko, « Frais passerelle » sinon. */
    public function getFeeLabelAttribute(): string
    {
        return $this->method === 'jeko' ? 'Frais Jeko' : 'Frais passerelle';
    }
}

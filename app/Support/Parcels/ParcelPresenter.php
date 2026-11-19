<?php

namespace App\Support\Parcels;

use App\Enums\ParcelStatus;
use App\Models\DeliveryDriver;
use App\Models\DeliveryCity;
use App\Models\Parcel;

/**
 * Représentations JSON d'un colis selon le lecteur (contrat : docs/api-colis.md).
 *  - sender  : l'expéditeur (client / commerçant) — tout, y compris le code de remise ;
 *  - partner : le partenaire API — sans le code (il le reçoit à la création) ;
 *  - public  : le destinataire sans compte — aucune donnée sensible ;
 *  - driver  : le livreur — adresses et téléphones, jamais le code.
 */
class ParcelPresenter
{
    public static function steps(Parcel $p): array
    {
        $current = $p->status;
        $steps = [
            ['key' => 'created', 'label' => 'Demande créée', 'at' => $p->created_at],
            ['key' => 'searching_driver', 'label' => 'Recherche d\'un livreur', 'at' => $p->driver_search_started_at],
            ['key' => 'assigned', 'label' => 'Livreur trouvé', 'at' => $p->assigned_at],
            ['key' => 'picked_up', 'label' => 'Colis ramassé', 'at' => $p->picked_up_at],
            ['key' => 'delivering', 'label' => 'En route vers le destinataire', 'at' => $p->delivering_at],
            ['key' => 'delivered', 'label' => 'Livré', 'at' => $p->delivered_at],
        ];

        if ($current === ParcelStatus::CANCELLED) {
            $steps[] = ['key' => 'cancelled', 'label' => 'Annulé', 'at' => $p->cancelled_at];
        } elseif ($current === ParcelStatus::FAILED) {
            $steps[] = ['key' => 'failed', 'label' => 'Échec de livraison', 'at' => $p->failed_at];
        }

        return array_map(fn ($s) => [
            'key' => $s['key'],
            'label' => $s['label'],
            'done' => $s['at'] !== null,
            'current' => $s['key'] === $current->value,
            'at' => $s['at']?->toIso8601String(),
        ], $steps);
    }

    /** Minutes estimées avant la remise (livreur en route vers le destinataire), sinon null. */
    public static function etaMinutes(Parcel $p, ?DeliveryDriver $driver): ?int
    {
        if (!$driver || !$driver->latitude || !$driver->longitude) {
            return null;
        }

        $road = (float) config('parcels.road_factor', 1.3);

        if (in_array($p->status, [ParcelStatus::PICKED_UP, ParcelStatus::DELIVERING], true)) {
            $km = DeliveryCity::haversineKm((float) $driver->latitude, (float) $driver->longitude, $p->dropoff_latitude, $p->dropoff_longitude) * $road;

            return max(1, (int) ceil($km / 25 * 60));
        }

        if ($p->status === ParcelStatus::ASSIGNED) {
            $km = (DeliveryCity::haversineKm((float) $driver->latitude, (float) $driver->longitude, $p->pickup_latitude, $p->pickup_longitude)
                + $p->distance_km / $road) * $road;

            return max(1, (int) ceil($km / 25 * 60) + 5);
        }

        return null;
    }

    private static function driverBlock(Parcel $p, bool $withContact): ?array
    {
        $d = $p->driver;
        if (!$d) {
            return null;
        }

        $live = $p->status->isActiveForDriver();

        return array_filter([
            'first_name' => Parcel::firstName($d->name),
            'name' => $withContact ? $d->name : null,
            'phone' => $withContact && $live ? $d->phone : null,
            'vehicle' => $d->vehicle_type,
            'photo_url' => $d->photo_path ? \App\Support\StorageUrl::url($d->photo_path) : null,
            'latitude' => $live && $d->latitude ? (float) $d->latitude : null,
            'longitude' => $live && $d->longitude ? (float) $d->longitude : null,
            'location_updated_at' => $live ? $d->location_updated_at?->toIso8601String() : null,
        ], fn ($v) => $v !== null);
    }

    private static function base(Parcel $p): array
    {
        return [
            'reference' => $p->reference,
            'status' => $p->status->value,
            'status_label' => $p->status->label(),
            'is_final' => $p->status->isFinal(),
            'city' => $p->city,
            'size' => $p->size,
            'size_label' => $p->sizeLabel(),
            'description' => $p->description,
            'note' => $p->note,
            'pickup' => [
                'address' => $p->pickup_address,
                'latitude' => $p->pickup_latitude,
                'longitude' => $p->pickup_longitude,
            ],
            'dropoff' => [
                'address' => $p->dropoff_address,
                'latitude' => $p->dropoff_latitude,
                'longitude' => $p->dropoff_longitude,
            ],
            'sender' => ['name' => $p->sender_name, 'phone' => $p->sender_phone],
            'recipient' => ['name' => $p->recipient_name, 'phone' => $p->recipient_phone],
            'distance_km' => $p->distance_km,
            'price' => $p->price,
            'payment' => [
                'method' => $p->payment_method,
                'status' => $p->payment_status,
                'paid_at' => $p->paid_at?->toIso8601String(),
                'refund_status' => $p->refund_status,
            ],
            'tracking_token' => $p->tracking_token,
            'tracking_url' => $p->publicTrackingUrl(),
            'pickup_photo_url' => $p->pickupPhotoUrl(),
            'eta_minutes' => self::etaMinutes($p, $p->driver),
            'steps' => self::steps($p),
            'cancellation_reason' => $p->cancellation_reason,
            'failure_reason' => $p->failure_reason,
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }

    public static function forSender(Parcel $p): array
    {
        return array_merge(self::base($p), [
            // Code à transmettre au destinataire : le livreur le lui demande à la remise
            'handover_code' => $p->status->isFinal() ? null : $p->handover_code,
            'driver' => self::driverBlock($p, true),
            'can_cancel' => $p->status->isCancellableBySender(),
        ]);
    }

    public static function forPartner(Parcel $p): array
    {
        return array_merge(self::base($p), [
            'external_reference' => $p->external_reference,
            'pickup_contact' => $p->pickup_contact,
            'driver' => self::driverBlock($p, true),
            'can_cancel' => $p->status->isCancellableBySender(),
            'cod_amount' => null, // phase 2
        ]);
    }

    /** Suivi public : aucun téléphone, aucune adresse, aucun code. */
    public static function forPublic(Parcel $p): array
    {
        return [
            'reference' => $p->reference,
            'status' => $p->status->value,
            'status_label' => $p->status->label(),
            'is_final' => $p->status->isFinal(),
            'city' => $p->city,
            'size_label' => $p->sizeLabel(),
            'recipient_first_name' => Parcel::firstName($p->recipient_name),
            'driver' => $p->driver ? [
                'first_name' => Parcel::firstName($p->driver->name),
                'vehicle' => $p->driver->vehicle_type,
            ] : null,
            'eta_minutes' => self::etaMinutes($p, $p->driver),
            'steps' => self::steps($p),
        ];
    }

    public static function forDriver(Parcel $p, ?DeliveryDriver $driver = null): array
    {
        $distanceToPickup = null;
        if ($driver && $driver->latitude && $driver->longitude) {
            $distanceToPickup = round(DeliveryCity::haversineKm((float) $driver->latitude, (float) $driver->longitude, $p->pickup_latitude, $p->pickup_longitude), 1);
        }

        $assigned = $driver && $p->driver_id === $driver->id;

        return [
            'kind' => 'parcel',
            'reference' => $p->reference,
            'status' => $p->status->value,
            'status_label' => $p->status->label(),
            'city' => $p->city,
            'size' => $p->size,
            'size_label' => $p->sizeLabel(),
            'description' => $p->description,
            'note' => $p->note,
            'pickup' => [
                'address' => $p->pickup_address,
                'latitude' => $p->pickup_latitude,
                'longitude' => $p->pickup_longitude,
                // Contact au ramassage : vendeuse (partenaire) sinon l'expéditeur — après acceptation seulement
                'contact_name' => $assigned ? ($p->pickup_contact['name'] ?? $p->sender_name) : null,
                'contact_phone' => $assigned ? ($p->pickup_contact['phone'] ?? $p->sender_phone) : null,
            ],
            'dropoff' => [
                'address' => $p->dropoff_address,
                'latitude' => $p->dropoff_latitude,
                'longitude' => $p->dropoff_longitude,
                'recipient_name' => $assigned ? $p->recipient_name : Parcel::firstName($p->recipient_name),
                'recipient_phone' => $assigned ? $p->recipient_phone : null,
            ],
            'distance_km' => $p->distance_km,
            'distance_to_pickup_km' => $distanceToPickup,
            'price' => $p->price,
            'driver_earning_estimate' => $p->driver_share,
            'payment_method' => $p->payment_method,
            // Espèces : montant à encaisser auprès de l'expéditeur AU RAMASSAGE
            'cash_to_collect' => $p->isCash() ? $p->price : 0,
            'cash_collected' => $p->cash_collected,
            'handover_attempts_left' => max(0, (int) config('parcels.handover_max_attempts', 5) - $p->handover_attempts),
            'handover_locked' => $p->isHandoverLocked(),
            'pickup_photo_url' => $p->pickupPhotoUrl(),
            'assigned_at' => $p->assigned_at?->toIso8601String(),
            'picked_up_at' => $p->picked_up_at?->toIso8601String(),
            'delivering_at' => $p->delivering_at?->toIso8601String(),
            'delivered_at' => $p->delivered_at?->toIso8601String(),
            'created_at' => $p->created_at?->toIso8601String(),
        ];
    }
}

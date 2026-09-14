<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Events\DeliveryStatusChanged;
use App\Events\DriverAssigned;
use App\Events\NewDeliveryAvailable;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\DriverEarning;
use App\Models\Order;
use App\Services\FcmService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverAssignmentService
{
    // Rayon de recherche initial, élargi par paliers si pas de livreur trouvé
    private const SEARCH_RADII_KM = [3, 6, 10];

    // Commission plateforme sur les frais de livraison (20%)
    public const PLATFORM_CUT_RATE = 0.20;

    public function __construct(
        private GeocodingService $geo,
        private FcmService $fcm,
    ) {}

    /**
     * Notifie les livreurs disponibles les plus proches qu'une course est disponible.
     * La course reste PENDING — le premier livreur qui accepte la prend.
     */
    public function notifyNearbyDrivers(Delivery $delivery, int $attempt = 1): int
    {
        $radiusKm = self::SEARCH_RADII_KM[min($attempt - 1, count(self::SEARCH_RADII_KM) - 1)];

        $drivers = $this->findNearbyDrivers($delivery, $radiusKm);

        $city = $delivery->restaurant->city ?? '';
        if ($city) {
            try {
                broadcast(new NewDeliveryAvailable($delivery, $city))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('NewDeliveryAvailable broadcast failed', ['error' => $e->getMessage()]);
            }
        }

        if ($drivers->isEmpty()) {
            Log::info('DriverAssignment: aucun livreur disponible', [
                'delivery_id' => $delivery->id,
                'radius_km'   => $radiusKm,
            ]);
            return 0;
        }

        $restaurant = $delivery->restaurant->name ?? 'Restaurant';
        $notified = 0;

        foreach ($drivers as $driver) {
            if (!$driver->fcm_token) {
                continue;
            }

            try {
                $this->fcm->sendToToken(
                    $driver->fcm_token,
                    '🛵 Nouvelle course disponible',
                    "Course disponible depuis {$restaurant}. Ouvrez l'application pour accepter.",
                    ['type' => 'new_delivery', 'delivery_id' => (string) $delivery->id],
                );
                $notified++;
            } catch (\Throwable $e) {
                Log::warning('FCM notification failed', [
                    'driver_id' => $driver->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('DriverAssignment: livreurs notifiés', [
            'delivery_id'    => $delivery->id,
            'radius_km'      => $radiusKm,
            'found'          => $drivers->count(),
            'notified'       => $notified,
        ]);

        return $notified;
    }

    /**
     * Assigne un livreur spécifique (appelé par accept()).
     */
    public function assign(Delivery $delivery): ?DeliveryDriver
    {
        $driver = $this->findNearest($delivery);

        if (!$driver) {
            return null;
        }

        $this->doAssign($delivery, $driver);

        return $driver;
    }

    /**
     * Trouve les livreurs disponibles dans un rayon donné, triés par distance.
     */
    public function findNearbyDrivers(Delivery $delivery, float $radiusKm = 3): \Illuminate\Support\Collection
    {
        $pickupLat = (float) $delivery->pickup_latitude;
        $pickupLng = (float) $delivery->pickup_longitude;

        return DeliveryDriver::available()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw(
                '*, ( 6371 * acos( cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude)) ) ) AS distance_km',
                [$pickupLat, $pickupLng, $pickupLat]
            )
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->limit(5)
            ->get();
    }

    /**
     * Cherche le livreur disponible le plus proche en élargissant le rayon progressivement.
     */
    public function findNearest(Delivery $delivery): ?DeliveryDriver
    {
        $pickupLat = (float) $delivery->pickup_latitude;
        $pickupLng = (float) $delivery->pickup_longitude;

        foreach (self::SEARCH_RADII_KM as $radiusKm) {
            $driver = $this->queryNearestDriver($pickupLat, $pickupLng, $radiusKm);
            if ($driver) {
                Log::info("DriverAssignment: livreur trouvé dans {$radiusKm}km", [
                    'driver_id'   => $driver->id,
                    'delivery_id' => $delivery->id,
                ]);
                return $driver;
            }
        }

        return null;
    }

    /**
     * Retourne les livreurs disponibles triés par distance depuis un point.
     */
    public function availableNearby(float $lat, float $lng, float $radiusKm = 5): array
    {
        $drivers = DeliveryDriver::available()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (DeliveryDriver $d) use ($lat, $lng) {
                $d->distance_km = round(
                    $this->geo->distanceKm($lat, $lng, (float) $d->latitude, (float) $d->longitude),
                    2
                );
                return $d;
            })
            ->filter(fn($d) => $d->distance_km <= $radiusKm)
            ->sortBy('distance_km')
            ->values()
            ->all();

        return $drivers;
    }

    /**
     * Libère un livreur après livraison et crédite ses gains.
     */
    public function completeDelivery(Delivery $delivery): void
    {
        DB::transaction(function () use ($delivery) {
            $delivery->update([
                'status'       => DeliveryStatus::DELIVERED->value,
                'delivered_at' => now(),
            ]);

            if ($delivery->driver_id) {
                $driver = $delivery->driver;
                $driver->increment('total_deliveries');

                // Rendre le livreur à nouveau disponible
                $driver->update(['is_available' => true]);

                // Créditer les gains
                $this->creditDriverEarning($delivery);
            }
        });
    }

    /**
     * Annule l'assignation (livreur refuse ou timeout).
     */
    public function unassign(Delivery $delivery, string $reason = ''): void
    {
        DB::transaction(function () use ($delivery, $reason) {
            if ($delivery->driver_id) {
                DeliveryDriver::where('id', $delivery->driver_id)
                    ->update(['is_available' => true]);

                DeliveryDriver::where('id', $delivery->driver_id)
                    ->increment('total_cancelled');
            }

            $delivery->update([
                'driver_id'           => null,
                'status'              => DeliveryStatus::PENDING->value,
                'assigned_at'         => null,
                'cancellation_reason' => $reason,
            ]);
        });
    }

    // -------------------------------------------------------------------------

    private function queryNearestDriver(float $lat, float $lng, float $radiusKm): ?DeliveryDriver
    {
        // Formule Haversine inline SQL pour perf
        $drivers = DeliveryDriver::available()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->selectRaw(
                '*, ( 6371 * acos( cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude)) ) ) AS distance_km',
                [$lat, $lng, $lat]
            )
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->limit(1)
            ->first();

        return $drivers;
    }

    private function doAssign(Delivery $delivery, DeliveryDriver $driver): void
    {
        DB::transaction(function () use ($delivery, $driver) {
            $delivery->update([
                'driver_id'   => $driver->id,
                'status'      => DeliveryStatus::ASSIGNED->value,
                'assigned_at' => now(),
            ]);

            $driver->update(['is_available' => false]);

            $delivery->order->update(['driver_assigned_at' => now()]);
        });

        broadcast(new DriverAssigned($delivery->fresh()->load(['order', 'restaurant']), $driver));

        if ($driver->fcm_token) {
            $restaurant = $delivery->restaurant->name ?? 'Restaurant';
            try {
                $this->fcm->sendToToken(
                    $driver->fcm_token,
                    '🛵 Nouvelle course assignée',
                    "Vous avez une course depuis {$restaurant}. Ouvrez l'application pour accepter.",
                    ['type' => 'delivery_assigned', 'delivery_id' => (string) $delivery->id],
                );
            } catch (\Throwable $e) {
                Log::warning('FCM driver assignment notification failed', ['error' => $e->getMessage()]);
            }
        }
    }

    private function creditDriverEarning(Delivery $delivery): void
    {
        $order = $delivery->order;

        // Pour cash_on_delivery, le livreur collecte l'argent physiquement.
        // Les gains seront crédités après confirmation du reversement au restaurant (Plan B).
        if ($order->payment_method === 'cash_on_delivery') {
            Log::info('creditDriverEarning: skipped for cash_on_delivery', [
                'delivery_id' => $delivery->id,
                'order_id'    => $order->id,
            ]);
            return;
        }

        $gross = $order->delivery_fee;

        if ($gross <= 0) {
            return;
        }

        $platformCut = (int) round($gross * self::PLATFORM_CUT_RATE);
        $net         = $gross - $platformCut;

        DriverEarning::create([
            'driver_id'    => $delivery->driver_id,
            'order_id'     => $order->id,
            'delivery_id'  => $delivery->id,
            'gross_amount' => $gross,
            'platform_cut' => $platformCut,
            'net_amount'   => $net,
            'status'       => 'available',
        ]);

        DeliveryDriver::where('id', $delivery->driver_id)
            ->increment('total_earnings_xof', $net);
    }
}

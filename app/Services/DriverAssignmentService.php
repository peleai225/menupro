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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverAssignmentService
{
    // Rayon de recherche initial, élargi par paliers si pas de livreur trouvé
    private const SEARCH_RADII_KM = [3, 6, 10];

    // Commission plateforme sur les frais de livraison (20%)
    private const PLATFORM_CUT_RATE = 0.20;

    public function __construct(private GeocodingService $geo) {}

    /**
     * Trouve et assigne le livreur disponible le plus proche du restaurant.
     * Retourne le livreur assigné ou null si aucun disponible.
     */
    public function assign(Delivery $delivery): ?DeliveryDriver
    {
        $driver = $this->findNearest($delivery);

        // Toujours broadcaster la disponibilité de la course à tous les livreurs de la ville
        $city = $delivery->restaurant->city ?? '';
        if ($city) {
            try {
                broadcast(new NewDeliveryAvailable($delivery, $city))->toOthers();
            } catch (\Throwable $e) {
                Log::warning('NewDeliveryAvailable broadcast failed', ['error' => $e->getMessage()]);
            }
        }

        if (!$driver) {
            Log::info('DriverAssignment: aucun livreur disponible', [
                'delivery_id' => $delivery->id,
                'order_id'    => $delivery->order_id,
            ]);
            return null;
        }

        $this->doAssign($delivery, $driver);

        return $driver;
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
    }

    private function creditDriverEarning(Delivery $delivery): void
    {
        $order = $delivery->order;
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

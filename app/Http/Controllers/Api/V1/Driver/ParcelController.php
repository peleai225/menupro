<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Enums\ParcelStatus;
use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Services\Parcels\ParcelService;
use App\Support\Parcels\ParcelPresenter;
use App\Support\Parcels\ParcelRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Courses colis dans l'app livreur. Mêmes conventions que DeliveryController
 * (auth:sanctum + delivery.driver), chaque élément porte kind: "parcel".
 */
class ParcelController extends Controller
{
    public function __construct(private ParcelService $parcels) {}

    /** Colis en attente de livreur dans la ville du livreur. */
    public function available(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;

        if (!$driver->is_available) {
            return response()->json(['data' => [], 'message' => 'Passez en ligne pour voir les courses.']);
        }

        $parcels = Parcel::where('status', ParcelStatus::SEARCHING_DRIVER->value)
            ->when($driver->city, fn ($q) => $q->where('city', $driver->city))
            ->oldest('driver_search_started_at')
            ->limit(50)
            ->get()
            ->map(fn (Parcel $p) => ParcelPresenter::forDriver($p, $driver))
            ->sortBy(fn ($p) => $p['distance_to_pickup_km'] ?? PHP_INT_MAX)
            ->values();

        return response()->json(['data' => $parcels]);
    }

    /** Colis en cours du livreur (null s'il n'en a pas). */
    public function active(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;
        $parcel = $driver->activeParcel();

        return response()->json([
            'data' => $parcel ? ParcelPresenter::forDriver($parcel, $driver) : null,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;

        $parcels = Parcel::where('driver_id', $driver->id)
            ->whereIn('status', [ParcelStatus::DELIVERED->value, ParcelStatus::FAILED->value, ParcelStatus::CANCELLED->value])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'data' => $parcels->getCollection()->map(fn (Parcel $p) => ParcelPresenter::forDriver($p, $driver))->values(),
            'meta' => [
                'current_page' => $parcels->currentPage(),
                'last_page' => $parcels->lastPage(),
                'total' => $parcels->total(),
            ],
        ]);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;

        return response()->json(['data' => ParcelPresenter::forDriver($this->parcels->driverParcel($reference, $driver), $driver)]);
    }

    public function accept(Request $request, string $reference): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;
        $parcel = $this->parcels->accept($reference, $driver);

        return response()->json([
            'message' => 'Course colis acceptée.',
            'data' => ParcelPresenter::forDriver($parcel, $driver),
        ]);
    }

    public function pickup(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:' . (int) config('parcels.pickup_photo_max_kb', 6144),
            'cash_received' => 'nullable|boolean',
        ], ParcelRules::messages(), ParcelRules::attributes());

        $driver = $request->user()->deliveryDriver;
        $parcel = $this->parcels->pickup($reference, $driver, $request->file('photo'), (bool) ($data['cash_received'] ?? false));

        return response()->json([
            'message' => 'Colis ramassé.',
            'data' => ParcelPresenter::forDriver($parcel, $driver),
        ]);
    }

    public function start(Request $request, string $reference): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;
        $parcel = $this->parcels->startDelivery($reference, $driver);

        return response()->json([
            'message' => 'Livraison démarrée.',
            'data' => ParcelPresenter::forDriver($parcel, $driver),
        ]);
    }

    public function deliver(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate(['code' => 'required|digits:4'], ParcelRules::messages(), ParcelRules::attributes());

        $driver = $request->user()->deliveryDriver;
        $parcel = $this->parcels->deliver($reference, $driver, (string) $data['code']);

        return response()->json([
            'message' => 'Colis livré. Merci !',
            'earning' => $parcel->driver_share,
            'data' => ParcelPresenter::forDriver($parcel, $driver),
        ]);
    }

    public function fail(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate(['reason' => 'required|string|min:3|max:500'], ParcelRules::messages(), ParcelRules::attributes());

        $driver = $request->user()->deliveryDriver;
        $parcel = $this->parcels->fail($reference, $driver, $data['reason']);

        return response()->json([
            'message' => $parcel->status === ParcelStatus::SEARCHING_DRIVER
                ? 'Course libérée : elle est proposée à un autre livreur.'
                : 'Échec enregistré. MenuPro va vous contacter pour le retour du colis.',
            'status' => $parcel->status->value,
        ]);
    }
}

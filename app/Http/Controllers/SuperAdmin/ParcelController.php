<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\ParcelStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\DriverCashDebt;
use App\Models\Parcel;
use App\Services\Parcels\ParcelException;
use App\Services\Parcels\ParcelPricingService;
use App\Services\Parcels\ParcelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ParcelController extends Controller
{
    public function __construct(private ParcelService $parcels) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => 'nullable|string|max:30',
            'city' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'search' => 'nullable|string|max:100',
            'sender' => 'nullable|in:customer,restaurant,partner',
            'refund' => 'nullable|in:pending',
        ]);

        $query = Parcel::query()
            ->with(['driver:id,name,phone', 'restaurant:id,name', 'partner:id,name'])
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['city'] ?? null, fn ($q, $c) => $q->where('city', $c))
            ->when($filters['sender'] ?? null, fn ($q, $s) => $q->where('sender_type', $s))
            ->when($filters['refund'] ?? null, fn ($q) => $q->where('refund_status', 'pending'))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', $d . ' 00:00:00'))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', $d . ' 23:59:59'))
            ->when($filters['search'] ?? null, fn ($q, $t) => $q->where(fn ($w) => $w
                ->where('reference', 'like', "%{$t}%")
                ->orWhere('external_reference', 'like', "%{$t}%")
                ->orWhere('sender_phone', 'like', "%{$t}%")
                ->orWhere('recipient_phone', 'like', "%{$t}%")));

        $parcels = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'today' => Parcel::where('created_at', '>=', now()->startOfDay())->count(),
            'searching' => Parcel::where('status', ParcelStatus::SEARCHING_DRIVER->value)->count(),
            'in_progress' => Parcel::whereIn('status', ParcelStatus::driverActiveValues())->count(),
            'refunds_pending' => Parcel::where('refund_status', 'pending')->count(),
        ];

        $cities = Parcel::query()->distinct()->orderBy('city')->pluck('city');

        return view('pages.super-admin.parcels.index', [
            'parcels' => $parcels,
            'stats' => $stats,
            'cities' => $cities,
            'statuses' => ParcelStatus::cases(),
        ]);
    }

    public function show(Parcel $parcel): View
    {
        $parcel->load(['driver', 'customer.user', 'restaurant', 'partner', 'createdBy']);

        $cashDebt = DriverCashDebt::where('parcel_id', $parcel->id)->first();

        return view('pages.super-admin.parcels.show', compact('parcel', 'cashDebt'));
    }

    public function cancel(Request $request, Parcel $parcel): RedirectResponse
    {
        $data = $request->validate(['reason' => 'required|string|min:3|max:300'], [
            'reason.required' => 'Indiquez le motif de l\'annulation.',
        ]);

        try {
            $parcel = $this->parcels->cancel($parcel, 'admin', $data['reason']);
        } catch (ParcelException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Colis annulé.' . ($parcel->refund_status === 'pending' ? ' Remboursement Jeko à effectuer, puis le marquer comme remboursé.' : ''));
    }

    public function refunded(Parcel $parcel): RedirectResponse
    {
        try {
            $this->parcels->markRefunded($parcel);
        } catch (ParcelException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Remboursement enregistré (journal mis à jour).');
    }

    public function unlock(Parcel $parcel): RedirectResponse
    {
        $this->parcels->unlockHandover($parcel);

        return back()->with('success', 'Code de remise débloqué : le livreur peut réessayer.');
    }

    public function settleCash(Request $request, Parcel $parcel): RedirectResponse
    {
        return $this->parcels->settleCashDebt($parcel, $request->user())
            ? back()->with('success', 'Commission reçue du livreur : dette soldée.')
            : back()->with('error', 'Aucune dette en attente pour ce colis.');
    }

    // ─── Tarifs par ville ────────────────────────────────────────────────────

    public function tariffs(ParcelPricingService $pricing): View
    {
        $cities = DeliveryCity::orderBy('name')->get();
        $rows = $cities->map(fn (DeliveryCity $c) => [
            'city' => $c,
            'effective' => $pricing->tariffFor($c),
            'override' => (array) $c->parcel_pricing,
        ]);

        return view('pages.super-admin.parcels.tariffs', [
            'rows' => $rows,
            'defaults' => $pricing->tariffFor(null),
        ]);
    }

    public function updateTariff(Request $request, DeliveryCity $city): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'base_fee' => 'nullable|integer|min:0|max:100000',
            'per_km_fee' => 'nullable|integer|min:0|max:10000',
            'medium' => 'nullable|integer|min:0|max:100000',
            'large' => 'nullable|integer|min:0|max:100000',
            'small' => 'nullable|integer|min:0|max:100000',
            'max_distance_km' => 'nullable|numeric|min:0.5|max:200',
            'driver_share_percent' => 'nullable|numeric|min:0|max:100',
        ], [], [
            'base_fee' => 'prix de base', 'per_km_fee' => 'prix au km', 'max_distance_km' => 'plafond de distance',
            'driver_share_percent' => 'part livreur', 'small' => 'supplément petit', 'medium' => 'supplément moyen', 'large' => 'supplément grand',
        ]);

        $override = array_filter([
            'enabled' => $request->boolean('enabled'),
            'base_fee' => $data['base_fee'] ?? null,
            'per_km_fee' => $data['per_km_fee'] ?? null,
            'max_distance_km' => $data['max_distance_km'] ?? null,
            'driver_share_percent' => $data['driver_share_percent'] ?? null,
            'size_supplements' => array_filter([
                'small' => $data['small'] ?? null,
                'medium' => $data['medium'] ?? null,
                'large' => $data['large'] ?? null,
            ], fn ($v) => $v !== null) ?: null,
        ], fn ($v) => $v !== null);

        $city->update(['parcel_pricing' => $override]);
        // GeocodingService::detectDeliveryCity met les villes actives en cache
        Cache::forget('delivery_cities:active');

        return back()->with('success', "Tarifs colis de {$city->name} enregistrés.");
    }
}

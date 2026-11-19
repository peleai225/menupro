<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\ParcelStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendPartnerWebhookJob;
use App\Models\DeliveryCity;
use App\Models\DeliveryPartner;
use App\Models\Parcel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeliveryPartnerController extends Controller
{
    public function index(): View
    {
        $partners = DeliveryPartner::withCount('parcels')->orderBy('name')->get();

        return view('pages.super-admin.partners.index', compact('partners'));
    }

    public function create(): View
    {
        return view('pages.super-admin.partners.form', [
            'partner' => new DeliveryPartner(['is_active' => true, 'rate_limit_per_minute' => 60]),
            'cities' => DeliveryCity::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $partner = DeliveryPartner::create($this->validated($request));
        $key = $partner->generateApiKey();
        $secret = $partner->generateWebhookSecret();

        return redirect()->route('super-admin.partners.show', $partner)
            ->with('success', 'Partenaire créé.')
            ->with('plain_api_key', $key)
            ->with('plain_webhook_secret', $secret);
    }

    public function show(DeliveryPartner $partner): View
    {
        $logs = $partner->webhookLogs()->with('parcel:id,reference')->latest('id')->limit(50)->get();
        $recentParcels = $partner->parcels()->latest()->limit(20)->get();

        return view('pages.super-admin.partners.show', [
            'partner' => $partner,
            'logs' => $logs,
            'recentParcels' => $recentParcels,
            'cities' => DeliveryCity::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, DeliveryPartner $partner): RedirectResponse
    {
        $partner->update($this->validated($request));

        return back()->with('success', 'Partenaire mis à jour.');
    }

    public function regenerateKey(DeliveryPartner $partner): RedirectResponse
    {
        $key = $partner->generateApiKey();

        return back()->with('success', 'Nouvelle clé générée : l\'ancienne ne fonctionne plus.')->with('plain_api_key', $key);
    }

    public function regenerateSecret(DeliveryPartner $partner): RedirectResponse
    {
        $secret = $partner->generateWebhookSecret();

        return back()->with('success', 'Nouveau secret de signature généré.')->with('plain_webhook_secret', $secret);
    }

    public function testWebhook(DeliveryPartner $partner): RedirectResponse
    {
        if (!$partner->webhook_url) {
            return back()->with('error', 'Renseignez d\'abord l\'URL du webhook.');
        }

        // Envoi immédiat, une seule tentative (le résultat apparaît dans le journal ci-dessous)
        (new SendPartnerWebhookJob($partner->id, null, 'ping', [
            'event' => 'ping',
            'sent_at' => now()->toIso8601String(),
            'data' => ['message' => 'Test de webhook MenuPro', 'partner' => $partner->name],
        ], SendPartnerWebhookJob::MAX_ATTEMPTS))->handle();

        $last = $partner->webhookLogs()->latest('id')->first();

        return back()->with(
            $last?->success ? 'success' : 'error',
            $last?->success
                ? "Webhook reçu par le partenaire (HTTP {$last->status_code})."
                : 'Échec de l\'envoi : ' . ($last?->error ?: 'HTTP ' . $last?->status_code),
        );
    }

    /** Récapitulatif mensuel des courses livrées à facturer, par partenaire. */
    public function billing(Request $request): View
    {
        $month = $request->validate(['month' => 'nullable|date_format:Y-m'])['month'] ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $end = $start->copy()->endOfMonth();

        $rows = Parcel::query()
            ->where('payment_method', Parcel::PAYMENT_PARTNER)
            ->where('status', ParcelStatus::DELIVERED->value)
            ->whereBetween('delivered_at', [$start, $end])
            ->groupBy('delivery_partner_id')
            ->select('delivery_partner_id', DB::raw('COUNT(*) as parcels_count'), DB::raw('SUM(price) as total_price'), DB::raw('SUM(driver_share) as total_driver_share'), DB::raw('SUM(platform_commission) as total_commission'))
            ->get();

        $partners = DeliveryPartner::whereIn('id', $rows->pluck('delivery_partner_id'))->get()->keyBy('id');

        $selectedPartner = $request->integer('partner') ?: null;
        $details = $selectedPartner
            ? Parcel::where('delivery_partner_id', $selectedPartner)
                ->where('payment_method', Parcel::PAYMENT_PARTNER)
                ->where('status', ParcelStatus::DELIVERED->value)
                ->whereBetween('delivered_at', [$start, $end])
                ->orderBy('delivered_at')
                ->get(['reference', 'external_reference', 'city', 'distance_km', 'size', 'price', 'delivered_at'])
            : collect();

        return view('pages.super-admin.partners.billing', compact('rows', 'partners', 'month', 'details', 'selectedPartner'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'is_active' => 'nullable|boolean',
            'cities' => 'nullable|array',
            'cities.*' => 'string|max:100',
            'contact_name' => 'nullable|string|max:120',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:150',
            'webhook_url' => 'nullable|url:https|max:500',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:1000',
            'notes' => 'nullable|string|max:2000',
            'pricing_base_fee' => 'nullable|integer|min:0|max:100000',
            'pricing_per_km_fee' => 'nullable|integer|min:0|max:10000',
        ], [
            'webhook_url.url' => 'L\'URL du webhook doit commencer par https://.',
        ], ['name' => 'nom', 'webhook_url' => 'URL du webhook', 'discount_percent' => 'remise']);

        $data['is_active'] = $request->boolean('is_active');
        $data['cities'] = array_values(array_filter($data['cities'] ?? [])) ?: null;
        $data['rate_limit_per_minute'] = $data['rate_limit_per_minute'] ?? 60;

        // Grille propre facultative (sinon tarif de la ville)
        $data['pricing'] = array_filter([
            'base_fee' => $data['pricing_base_fee'] ?? null,
            'per_km_fee' => $data['pricing_per_km_fee'] ?? null,
        ], fn ($v) => $v !== null) ?: null;
        unset($data['pricing_base_fee'], $data['pricing_per_km_fee']);

        return $data;
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\QrCampaign;
use App\Services\QrCodeImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Back-office « Campagnes QR » : liens courts suivis pour bâches et affiches.
 */
class QrCampaignController extends Controller
{
    public function index(): View
    {
        $campaigns = QrCampaign::withCount('restaurants')->latest()->paginate(30);
        $cities = DeliveryCity::orderBy('name')->pluck('name');

        return view('pages.super-admin.qr-campaigns.index', compact('campaigns', 'cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;

        $campaign = QrCampaign::create($data);

        return redirect()->route('super-admin.qr-campaigns.show', $campaign)
            ->with('success', "Campagne créée : {$campaign->short_url}");
    }

    public function show(QrCampaign $campaign): View
    {
        $from = now()->subDays(29)->startOfDay();

        $daily = $campaign->scans()
            ->where('scanned_at', '>=', $from)
            ->selectRaw('DATE(scanned_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = collect(range(0, 29))->map(function ($i) use ($from, $daily) {
            $day = $from->copy()->addDays($i)->toDateString();
            return ['day' => $day, 'total' => (int) ($daily[$day] ?? 0)];
        });

        $devices = $campaign->scans()
            ->selectRaw('device, COUNT(*) as total')
            ->groupBy('device')
            ->pluck('total', 'device');

        $signups = $campaign->restaurants()->latest()->limit(50)->get(['id', 'slug', 'name', 'city', 'status', 'created_at']);

        return view('pages.super-admin.qr-campaigns.show', [
            'campaign' => $campaign,
            'days' => $days,
            'maxDay' => max(1, $days->max('total')),
            'devices' => $devices,
            'signups' => $signups,
            'signupsCount' => $campaign->restaurants()->count(),
            'svgPreview' => preg_replace('/^<\?xml[^>]*>\s*/', '', app(QrCodeImageService::class)->svg($campaign->short_url, 320)),
        ]);
    }

    public function update(Request $request, QrCampaign $campaign): RedirectResponse
    {
        if ($request->has('toggle')) {
            $campaign->update(['is_active' => !$campaign->is_active]);
            return back()->with('success', $campaign->is_active ? 'Campagne réactivée.' : 'Campagne désactivée : le lien renvoie désormais une page introuvable.');
        }

        $campaign->update($this->validated($request));

        return back()->with('success', 'Campagne mise à jour.');
    }

    public function png(QrCampaign $campaign, QrCodeImageService $qr): Response
    {
        return response($qr->png($campaign->short_url), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $this->filename($campaign) . '.png"',
        ]);
    }

    public function svg(QrCampaign $campaign, QrCodeImageService $qr): Response
    {
        return response($qr->svg($campaign->short_url), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $this->filename($campaign) . '.svg"',
        ]);
    }

    private function filename(QrCampaign $campaign): string
    {
        return 'qr-' . $campaign->code . '-' . Str::slug($campaign->name);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'target_type' => ['required', Rule::in(array_keys(QrCampaign::TARGET_LABELS))],
            'target_path' => ['nullable', 'required_if:target_type,' . QrCampaign::TARGET_INTERNAL_URL, 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:160'],
        ], [
            'target_path.required_if' => 'Indiquez le chemin interne (ex. /tarifs).',
        ]);

        if ($data['target_type'] === QrCampaign::TARGET_INTERNAL_URL) {
            if (!QrCampaign::isSafeInternalPath($data['target_path'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'target_path' => 'URL interne uniquement : un chemin commençant par « / » (ex. /tarifs).',
                ]);
            }
        } else {
            $data['target_path'] = null;
        }

        return $data;
    }
}

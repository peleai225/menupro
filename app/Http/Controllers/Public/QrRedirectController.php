<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\QrCampaign;
use App\Models\QrCampaignScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Lien court suivi des campagnes QR : /q/{code} → compte le scan puis redirige.
 * Aucune donnée personnelle : seuls la date et un type d'appareil grossier sont gardés.
 */
class QrRedirectController extends Controller
{
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $campaign = QrCampaign::where('code', strtolower($code))->first();

        if (!$campaign || !$campaign->is_active) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($campaign, $request) {
                QrCampaignScan::create([
                    'qr_campaign_id' => $campaign->id,
                    'device' => QrCampaignScan::deviceFromUserAgent($request->userAgent()),
                    'scanned_at' => now(),
                ]);
                // Incrément atomique : sûr sous fort trafic simultané.
                QrCampaign::whereKey($campaign->id)->increment('scans_count');
            });
        } catch (\Throwable $e) {
            // Le comptage ne doit jamais empêcher la redirection.
            Log::warning('Campagne QR : scan non enregistré', ['code' => $campaign->code, 'error' => $e->getMessage()]);
        }

        // Attribution des inscriptions restaurant issues de ce QR (lue par RegisterController).
        if ($campaign->target_type === QrCampaign::TARGET_RESTAURANT_SIGNUP) {
            $request->session()->put(QrCampaign::SESSION_KEY, $campaign->id);
        }

        return redirect()->away($campaign->destinationUrl(), 302, ['Cache-Control' => 'no-store, private']);
    }
}

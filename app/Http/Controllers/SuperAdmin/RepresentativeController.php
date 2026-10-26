<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Crm\RepresentativeProfile;
use App\Models\Crm\RepresentativeStatement;
use App\Services\Crm\RepresentativeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Back-office : représentants de ville (réglages, attribution, rémunération, export).
 */
class RepresentativeController extends Controller
{
    public function __construct(private RepresentativeService $service) {}

    public function index(): View
    {
        $month = now()->format('Y-m');

        $profiles = RepresentativeProfile::with('user')
            ->orderBy('city')
            ->get()
            ->map(function (RepresentativeProfile $profile) use ($month) {
                $profile->current = $this->service->calculate($profile, $month);
                $profile->quarter = $this->service->quarterProgress($profile);
                return $profile;
            });

        return view('pages.super-admin.representatives.index', compact('profiles', 'month'));
    }

    public function show(RepresentativeProfile $representative): View
    {
        $representative->load('user', 'application');

        return view('pages.super-admin.representatives.show', [
            'profile' => $representative,
            'current' => $this->service->calculate($representative, now()->format('Y-m')),
            'quarter' => $this->service->quarterProgress($representative),
            'statements' => $representative->statements()->orderByDesc('month')->limit(24)->get(),
            'wallet' => \App\Models\Crm\Wallet::where('user_id', $representative->user_id)->first(),
        ]);
    }

    public function update(Request $request, RepresentativeProfile $representative): RedirectResponse
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:80'],
            'subscription_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'trial_ends_at' => ['nullable', 'date'],
            'quarterly_target' => ['required', 'integer', 'min:0', 'max:10000'],
            'exclusivity_active' => ['nullable', 'boolean'],
        ]);
        $data['exclusivity_active'] = $request->boolean('exclusivity_active');

        $this->service->updateSettings($representative, $data);

        return back()->with('success', 'Réglages du représentant enregistrés.');
    }

    /** Calcul (ou recalcul idempotent) d'un mois pour ce représentant. */
    public function compute(Request $request, RepresentativeProfile $representative): RedirectResponse
    {
        $data = $request->validate(['month' => ['required', 'date_format:Y-m', 'before_or_equal:' . now()->format('Y-m')]]);

        $result = $this->service->computeMonth($representative, $data['month']);
        $credited = number_format($result['credited_cents'] / 100, 0, ',', ' ');

        return back()->with('success', "Mois {$data['month']} calculé : part " . number_format($result['data']['total_share'], 0, ',', ' ') . " F, crédité ce calcul : {$credited} F.");
    }

    /** Export CSV des relevés (tous les représentants, ou un seul avec ?representative=). */
    public function export(Request $request): StreamedResponse
    {
        $query = RepresentativeStatement::with('profile.user')
            ->when($request->query('representative'), fn ($q, $id) => $q->where('representative_profile_id', $id))
            ->when($request->query('month'), fn ($q, $m) => $q->where('month', $m))
            ->orderByDesc('month');

        return self::statementsCsv($query, 'representants_releves_' . now()->format('Y-m-d') . '.csv');
    }

    /** Flux CSV (BOM UTF-8, « ; ») partagé avec l'espace représentant. */
    public static function statementsCsv($query, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Mois', 'Représentant', 'Ville', 'Restaurants attribués', 'Restaurants actifs',
                'Restaurants payants', 'Abonnements encaissés (F)', 'Taux abonnement (%)', 'Part abonnements (F)',
                'Commission MenuPro livraisons (F)', 'Taux livraisons (%)', 'Part livraisons (F)',
                'Part totale (F)', 'Versé au wallet (F)', 'Calculé le',
            ], ';');

            foreach ((clone $query)->lazy(500) as $s) {
                fputcsv($out, [
                    $s->month,
                    $s->profile?->user?->name,
                    $s->profile?->city,
                    $s->restaurants_attributed,
                    $s->restaurants_active,
                    $s->restaurants_paying,
                    $s->subscription_collected,
                    str_replace('.', ',', (string) $s->subscription_rate),
                    $s->subscription_share,
                    $s->delivery_commission,
                    str_replace('.', ',', (string) $s->delivery_rate),
                    $s->delivery_share,
                    $s->total_share,
                    intdiv((int) $s->credited_cents, 100),
                    $s->computed_at?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

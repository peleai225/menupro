<?php

namespace App\Console\Commands;

use App\Models\Crm\RepresentativeProfile;
use App\Services\Crm\RepresentativeService;
use Illuminate\Console\Command;

/**
 * Calcule la rémunération mensuelle des représentants de ville depuis le journal des
 * transactions et la crédite sur leur wallet CRM. Idempotent : relancer le même mois
 * ne crédite que l'éventuel complément (lignes arrivées depuis le dernier calcul).
 */
class ComputeRepresentativeShares extends Command
{
    protected $signature = 'representants:calculer-mois
                            {mois? : AAAA-MM, défaut = mois précédent}
                            {--dry-run : Simuler sans écrire ni créditer}';

    protected $description = 'Calcule et crédite la part mensuelle des représentants de ville (journal des transactions)';

    public function handle(RepresentativeService $service): int
    {
        $month = $this->argument('mois') ?: now()->subMonthNoOverflow()->format('Y-m');

        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('Mois invalide : utilisez le format AAAA-MM (ex. 2026-09).');
            return self::FAILURE;
        }

        if ($month > now()->format('Y-m')) {
            $this->error('Impossible de calculer un mois futur.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->info("Représentants — calcul de {$month}" . ($dryRun ? ' [DRY-RUN]' : ''));

        $rows = [];
        $totalCredited = 0;

        RepresentativeProfile::with('user')->orderBy('id')->each(function (RepresentativeProfile $profile) use ($service, $month, $dryRun, &$rows, &$totalCredited) {
            $result = $service->computeMonth($profile, $month, persist: !$dryRun);
            $d = $result['data'];
            $totalCredited += $result['credited_cents'];

            $rows[] = [
                $profile->user?->name ?? "#{$profile->id}",
                $profile->city,
                $d['restaurants_attributed'],
                number_format($d['subscription_collected'], 0, ',', ' '),
                number_format($d['delivery_commission'], 0, ',', ' '),
                number_format($d['total_share'], 0, ',', ' '),
                $dryRun ? '—' : number_format($result['credited_cents'] / 100, 0, ',', ' '),
            ];
        });

        if (!$rows) {
            $this->line('Aucun représentant.');
            return self::SUCCESS;
        }

        $this->table(
            ['Représentant', 'Ville', 'Restaurants', 'Abonnements (F)', 'Commission livraisons (F)', 'Part (F)', 'Crédité ce run (F)'],
            $rows
        );

        $this->info($dryRun
            ? 'Simulation terminée : aucune écriture.'
            : 'Total crédité ce run : ' . number_format($totalCredited / 100, 0, ',', ' ') . ' F');

        return self::SUCCESS;
    }
}

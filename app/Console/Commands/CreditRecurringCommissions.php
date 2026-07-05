<?php

namespace App\Console\Commands;

use App\Enums\Crm\CommissionType;
use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Commission;
use App\Models\Crm\Lead;
use App\Services\Crm\CommissionEngine;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreditRecurringCommissions extends Command
{
    protected $signature = 'crm:credit-recurring {--month= : YYYY-MM, défaut = mois précédent} {--dry-run : Simuler sans écrire}';
    protected $description = 'Crédite les commissions mensuelles récurrentes des commerciaux pour chaque restaurant actif';

    public function handle(CommissionEngine $engine): int
    {
        if (!config('crm.commissions.commercial_recurring_enabled')) {
            $this->info('Commissions récurrentes désactivées (CRM_COMMISSION_RECURRING_ENABLED=false).');
            return self::SUCCESS;
        }

        $month = $this->option('month')
            ? Carbon::parse($this->option('month') . '-01')
            : now()->subMonthNoOverflow()->startOfMonth();

        $dryRun = $this->option('dry-run');
        $label = $month->translatedFormat('F Y');

        $this->info("Calcul des récurrentes pour {$label}" . ($dryRun ? ' [DRY-RUN]' : ''));

        // Trouver tous les leads actifs (restaurants signés et opérationnels)
        $activeLeads = Lead::where('status', LeadStatus::ACTIF)
            ->whereNotNull('assigned_to')
            ->whereNotNull('converted_at')
            ->where('converted_at', '<', $month->copy()->endOfMonth())
            ->with('assignedUser')
            ->get();

        $credited = 0;
        $skipped = 0;

        foreach ($activeLeads as $lead) {
            $alreadyCredited = Commission::where('user_id', $lead->assigned_to)
                ->where('type', CommissionType::RECURRING)
                ->where('source_type', Lead::class)
                ->where('source_id', $lead->id)
                ->whereJsonContains('metadata->month', $month->format('Y-m'))
                ->exists();

            if ($alreadyCredited) {
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                DB::transaction(function () use ($engine, $lead, $month) {
                    $engine->creditRecurring($lead, $month->format('Y-m'));
                });
            }

            $credited++;
            $this->line("  ✓ {$lead->assignedUser->name} ← {$lead->restaurant_name}");
        }

        $amount = number_format(config('crm.commissions.commercial_recurring_cents') * $credited / 100, 0, ',', ' ');
        $this->info("Terminé : {$credited} créditées ({$amount} FCFA), {$skipped} déjà traitées.");

        return self::SUCCESS;
    }
}

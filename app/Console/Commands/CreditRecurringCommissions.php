<?php

namespace App\Console\Commands;

use App\Enums\Crm\AgentStatus;
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
    protected $signature = 'crm:credit-recurring
                            {--month= : YYYY-MM, défaut = mois précédent}
                            {--dry-run : Simuler sans écrire}';

    protected $description = 'Crédite les commissions récurrentes mensuelles pour chaque restaurant actif';

    public function handle(CommissionEngine $engine): int
    {
        if (!config('crm.commissions.commercial_recurring_enabled')) {
            $this->info('Commissions récurrentes désactivées.');
            return self::SUCCESS;
        }

        $month = $this->option('month')
            ? Carbon::parse($this->option('month') . '-01')
            : now()->subMonthNoOverflow()->startOfMonth();

        $dryRun = $this->option('dry-run');
        $label = $month->translatedFormat('F Y');
        $monthStr = $month->format('Y-m');

        $this->info("Calcul des récurrentes pour {$label}" . ($dryRun ? ' [DRY-RUN]' : ''));

        // Leads actifs dont la récurrente a démarré, assignés à un agent actif
        $activeLeads = Lead::where('status', LeadStatus::ACTIF)
            ->whereNotNull('assigned_to')
            ->whereNotNull('converted_at')
            ->where('converted_at', '<', $month->copy()->endOfMonth())
            // Récurrente démarre le 2ème mois après conversion
            ->where(function ($q) use ($monthStr) {
                $q->whereNull('recurring_starts_month')
                  ->orWhere('recurring_starts_month', '<=', $monthStr);
            })
            ->with(['assignedUser', 'assignedUser.agentStatus'])
            ->get();

        $credited = 0;
        $skipped = 0;
        $blocked = 0;
        $total = 0;

        foreach ($activeLeads as $lead) {
            // Vérifier que l'agent est encore actif
            $agent = $lead->assignedUser;
            if (!$agent) { $skipped++; continue; }

            $status = $agent->agent_status;
            if ($status && $status !== AgentStatus::ACTIF) {
                $blocked++;
                $this->line("  ⛔ {$agent->name} (statut: {$status->label()}) — {$lead->restaurant_name}");
                continue;
            }

            // Vérifier idempotence
            $alreadyCredited = Commission::where('user_id', $lead->assigned_to)
                ->where('type', CommissionType::RECURRING)
                ->where('source_type', Lead::class)
                ->where('source_id', $lead->id)
                ->whereJsonContains('metadata->month', $monthStr)
                ->exists();

            if ($alreadyCredited) {
                $skipped++;
                continue;
            }

            // Montant selon plan actuel
            $amountCents = $lead->subscription_plan
                ? $lead->subscription_plan->recurringCommissionCents()
                : config('crm.commissions.commercial_recurring_cents');

            $planLabel = $lead->subscription_plan?->shortLabel() ?? '?';

            if (!$dryRun) {
                DB::transaction(fn () => $engine->creditRecurring($lead, $monthStr));
            }

            $credited++;
            $total += $amountCents;
            $this->line("  ✓ {$agent->name} ← {$lead->restaurant_name} [{$planLabel}] +{$this->fcfa($amountCents)}");
        }

        $this->newLine();
        $this->info("Terminé : {$credited} créditées ({$this->fcfa($total)}), {$skipped} déjà traitées, {$blocked} bloquées (agent inactif).");

        return self::SUCCESS;
    }

    private function fcfa(int $cents): string
    {
        return number_format($cents / 100, 0, ',', ' ') . ' FCFA';
    }
}

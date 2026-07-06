<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Crm\Commission;
use App\Models\Crm\Lead;
use App\Models\Crm\PerformanceSnapshot;
use App\Models\Crm\Team;
use App\Services\Crm\CommissionEngine;
use App\Services\Crm\GradingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CrmMonthlyProcessing extends Command
{
    protected $signature = 'crm:monthly-processing {--month= : Mois à traiter (YYYY-MM, défaut: mois précédent)}';
    protected $description = 'Calcule les snapshots de performance et crédite les commissions récurrentes';

    public function handle(CommissionEngine $commissionEngine, GradingService $gradingService): int
    {
        $monthStr = $this->option('month') ?? Carbon::now()->subMonth()->format('Y-m');
        $periodStart = Carbon::parse($monthStr . '-01')->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        $this->info("Traitement CRM mensuel pour " . $periodStart->format('F Y'));

        // 1. Agents actifs (commercial + technician + team_leader)
        $agents = User::whereIn('role', ['commercial', 'technician', 'team_leader'])
            ->where('is_active', true)
            ->get();

        $this->info("{$agents->count()} agents à traiter");

        $bar = $this->output->createProgressBar($agents->count());

        foreach ($agents as $agent) {
            try {
                DB::transaction(function () use ($agent, $periodStart, $periodEnd, $commissionEngine) {
                    $this->processAgentSnapshot($agent, $periodStart, $periodEnd);
                    $this->processRecurringCommissions($agent, $periodStart, $periodEnd, $commissionEngine);
                });
            } catch (\Exception $e) {
                Log::error("CRM monthly processing failed for agent {$agent->id}", ['error' => $e->getMessage()]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // 2. Recalcul des grades
        $this->info("Recalcul des grades...");
        foreach ($agents as $agent) {
            try {
                $gradingService->recalculateForUser($agent);
            } catch (\Exception $e) {
                Log::warning("Grade recalc failed for agent {$agent->id}");
            }
        }

        // 3. Bonus top performer par équipe
        $this->info("Attribution des bonus top performer par équipe...");
        $this->processBonusPerformance($periodStart, $periodEnd, $monthStr, $commissionEngine);

        $this->info("Traitement mensuel terminé.");
        return 0;
    }

    private function processBonusPerformance(Carbon $start, Carbon $end, string $monthStr, CommissionEngine $commissionEngine): void
    {
        $teams = Team::active()->get();

        foreach ($teams as $team) {
            try {
                // Trouve le commercial de l'équipe avec le plus de leads passés à "actif" dans la période
                $topResult = Lead::where('team_id', $team->id)
                    ->where('status', 'actif')
                    ->whereBetween('updated_at', [$start, $end])
                    ->selectRaw('assigned_to, COUNT(*) as conversions')
                    ->groupBy('assigned_to')
                    ->orderByDesc('conversions')
                    ->first();

                if (!$topResult || $topResult->conversions < 1) {
                    continue;
                }

                $topAgent = User::find($topResult->assigned_to);
                if (!$topAgent) {
                    continue;
                }

                DB::transaction(function () use ($commissionEngine, $topAgent, $team, $monthStr) {
                    $commission = $commissionEngine->creditBonusPerformance($topAgent, $team->id, $monthStr);
                    if ($commission) {
                        Log::info("CRM bonus top performer: agent {$topAgent->id} ({$topAgent->name}) — équipe {$team->id} — mois {$monthStr} — {$commission->amount_cents} centimes");
                    }
                });
            } catch (\Exception $e) {
                Log::error("CRM bonus top performer failed for team {$team->id}", ['error' => $e->getMessage()]);
            }
        }
    }

    private function processAgentSnapshot(User $agent, Carbon $start, Carbon $end): void
    {
        $leadsCreated = Lead::where('assigned_to', $agent->id)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $leadsConverted = Lead::where('assigned_to', $agent->id)
            ->where('status', 'actif')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $commissionsEarned = Commission::where('user_id', $agent->id)
            ->where('status', 'validated')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount_cents');

        $totalLeads = max(1, $leadsCreated);
        $conversionRate = round(($leadsConverted / $totalLeads) * 100, 2);

        PerformanceSnapshot::updateOrCreate(
            [
                'user_id' => $agent->id,
                'period_type' => 'monthly',
                'period_start' => $start->toDateString(),
            ],
            [
                'period_end' => $end->toDateString(),
                'leads_created' => $leadsCreated,
                'leads_converted' => $leadsConverted,
                'commissions_earned_cents' => (int) $commissionsEarned,
                'conversion_rate' => $conversionRate,
            ]
        );
    }

    private function processRecurringCommissions(User $agent, Carbon $start, Carbon $end, CommissionEngine $commissionEngine): void
    {
        $monthStr = $start->format('Y-m');

        // Récupère les leads actifs de cet agent avec plan d'abonnement
        $activeLeads = Lead::where('assigned_to', $agent->id)
            ->where('status', 'actif')
            ->whereNotNull('subscription_plan')
            ->get();

        foreach ($activeLeads as $lead) {
            try {
                $commissionEngine->creditRecurring($lead, $monthStr);
            } catch (\Exception $e) {
                Log::warning("Recurring commission failed for lead {$lead->id}: " . $e->getMessage());
            }
        }
    }
}

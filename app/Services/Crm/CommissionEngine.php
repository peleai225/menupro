<?php

namespace App\Services\Crm;

use App\Enums\Crm\AgentStatus;
use App\Enums\Crm\CommissionStatus;
use App\Enums\Crm\CommissionType;
use App\Enums\Crm\SubscriptionPlan;
use App\Events\Crm\CommissionCredited;
use App\Models\Crm\Commission;
use App\Models\Crm\Installation;
use App\Models\Crm\Lead;
use App\Models\Crm\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionEngine
{
    /**
     * Commission signature ambassadeur — montant selon le plan souscrit.
     */
    public function creditForSignature(Lead $lead): ?Commission
    {
        $user = $lead->assignedUser;
        if (!$user || !$this->agentCanReceive($user)) return null;

        $alreadyCredited = Commission::where('user_id', $user->id)
            ->where('source_type', Lead::class)
            ->where('source_id', $lead->id)
            ->where('type', CommissionType::SIGNATURE)
            ->exists();

        if ($alreadyCredited) return null;

        $amountCents = $lead->subscription_plan
            ? $lead->subscription_plan->signatureCommissionCents()
            : config('crm.commissions.commercial_first_payment_cents');

        $planLabel = $lead->subscription_plan?->shortLabel() ?? 'Plan inconnu';

        return $this->credit(
            user: $user,
            type: CommissionType::SIGNATURE,
            amountCents: $amountCents,
            source: $lead,
            description: "Commission signature {$planLabel} — {$lead->restaurant_name}",
            metadata: ['plan' => $lead->subscription_plan?->value],
        );
    }

    /**
     * Commission installation technicien — paliers selon volume du mois calendaire.
     */
    public function creditForInstallation(Installation $installation): ?Commission
    {
        $technician = $installation->technician;
        if (!$technician || !$this->agentCanReceive($technician)) return null;

        $alreadyCredited = Commission::where('user_id', $technician->id)
            ->where('source_type', Installation::class)
            ->where('source_id', $installation->id)
            ->where('type', CommissionType::INSTALLATION)
            ->exists();

        if ($alreadyCredited) return null;

        $amountCents = $this->technicianInstallCommission($technician->id);

        return $this->credit(
            user: $technician,
            type: CommissionType::INSTALLATION,
            amountCents: $amountCents,
            source: $installation,
            description: "Commission installation — {$installation->lead->restaurant_name}",
        );
    }

    /**
     * Override Team Leader — 1 000 F par conversion dans son équipe.
     */
    public function creditLeaderOverride(Lead $lead): ?Commission
    {
        $team = $lead->team;
        if (!$team || !$team->leader_id) return null;

        $leader = $team->leader;
        if (!$leader || !$this->agentCanReceive($leader)) return null;

        $alreadyCredited = Commission::where('user_id', $leader->id)
            ->where('source_type', Lead::class)
            ->where('source_id', $lead->id)
            ->where('type', CommissionType::LEADER_OVERRIDE)
            ->exists();

        if ($alreadyCredited) return null;

        return $this->credit(
            user: $leader,
            type: CommissionType::LEADER_OVERRIDE,
            amountCents: config('crm.commissions.leader_per_conversion_cents'),
            source: $lead,
            description: "Override conversion — {$lead->restaurant_name}",
        );
    }

    /**
     * Bonus grade (one-shot).
     */
    public function creditGradeBonus(User $user, string $grade): ?Commission
    {
        $amountCents = match ($grade) {
            'commando' => config('crm.commissions.bonus_grade_commando_cents'),
            'elite'    => config('crm.commissions.bonus_grade_elite_cents'),
            default    => 0,
        };

        if ($amountCents <= 0) return null;

        $alreadyCredited = Commission::where('user_id', $user->id)
            ->where('type', CommissionType::BONUS_GRADE)
            ->whereJsonContains('metadata->grade', $grade)
            ->exists();

        if ($alreadyCredited) return null;

        return $this->credit(
            user: $user,
            type: CommissionType::BONUS_GRADE,
            amountCents: $amountCents,
            description: "Bonus atteinte grade {$grade}",
            metadata: ['grade' => $grade],
        );
    }

    /**
     * Récurrente mensuelle — montant selon le plan actuel du lead.
     * S'arrête si agent non actif ou si le lead n'est plus ACTIF.
     */
    public function creditRecurring(Lead $lead, string $month): ?Commission
    {
        $user = $lead->assignedUser;
        if (!$user || !$this->agentCanReceive($user)) return null;

        // Ne pas créditer si la récurrente n'a pas encore démarré
        if ($lead->recurring_starts_month && $month < $lead->recurring_starts_month) {
            return null;
        }

        $amountCents = $lead->subscription_plan
            ? $lead->subscription_plan->recurringCommissionCents()
            : config('crm.commissions.commercial_recurring_cents');

        if ($amountCents <= 0) return null;

        $planLabel = $lead->subscription_plan?->shortLabel() ?? 'Plan inconnu';

        return $this->credit(
            user: $user,
            type: CommissionType::RECURRING,
            amountCents: $amountCents,
            source: $lead,
            description: "Récurrente {$month} {$planLabel} — {$lead->restaurant_name}",
            metadata: ['month' => $month, 'plan' => $lead->subscription_plan?->value],
        );
    }

    /**
     * Bonus top performer mensuel — 1 seul par équipe par mois.
     * Idempotent : aucun double crédit si appelé plusieurs fois pour le même agent/équipe/mois.
     */
    public function creditBonusPerformance(User $user, int $teamId, string $month): ?Commission
    {
        if (!$this->agentCanReceive($user)) return null;

        $alreadyCredited = Commission::where('user_id', $user->id)
            ->where('type', CommissionType::BONUS_PERFORMANCE)
            ->whereJsonContains('metadata->month', $month)
            ->whereJsonContains('metadata->team_id', $teamId)
            ->exists();

        if ($alreadyCredited) return null;

        $amountCents = (int) config('crm.commissions.bonus_monthly_top_cents', 5_000_000);
        if ($amountCents <= 0) return null;

        return $this->credit(
            user: $user,
            type: CommissionType::BONUS_PERFORMANCE,
            amountCents: $amountCents,
            description: "Bonus top performer {$month} — équipe #{$teamId}",
            metadata: ['month' => $month, 'team_id' => $teamId],
        );
    }

    /**
     * Palier commission technicien selon nombre d'installations ce mois.
     */
    private function technicianInstallCommission(int $technicianId): int
    {
        $countThisMonth = Installation::where('technician_id', $technicianId)
            ->where('status', 'terminee')
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();

        $tier1Max = config('crm.commissions.technician_install_tier1_max');
        $tier2Max = config('crm.commissions.technician_install_tier2_max');

        // La nouvelle installation va s'ajouter à ce compte
        $next = $countThisMonth + 1;

        if ($next <= $tier1Max) {
            return config('crm.commissions.technician_install_tier1_cents');
        } elseif ($next <= $tier2Max) {
            return config('crm.commissions.technician_install_tier2_cents');
        } else {
            return config('crm.commissions.technician_install_tier3_cents');
        }
    }

    /**
     * L'agent peut recevoir une commission s'il est actif.
     */
    private function agentCanReceive(User $user): bool
    {
        // Manager reçoit toujours (super_admin)
        if ($user->isSuperAdmin()) return true;

        $status = $user->agent_status;
        if (!$status) return true; // pas encore migré → on laisse passer

        return $status === AgentStatus::ACTIF;
    }

    private function credit(
        User $user,
        CommissionType $type,
        int $amountCents,
        ?object $source = null,
        ?string $description = null,
        ?array $metadata = null,
    ): Commission {
        return DB::transaction(function () use ($user, $type, $amountCents, $source, $description, $metadata) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance_cents' => 0, 'total_earned_cents' => 0, 'total_withdrawn_cents' => 0]
            );

            $commission = Commission::create([
                'wallet_id'   => $wallet->id,
                'user_id'     => $user->id,
                'type'        => $type,
                'status'      => CommissionStatus::VALIDATED,
                'amount_cents' => $amountCents,
                'source_type' => $source ? get_class($source) : null,
                'source_id'   => $source?->id,
                'description' => $description,
                'metadata'    => $metadata,
                'validated_at' => now(),
            ]);

            $wallet->credit($amountCents);

            event(new CommissionCredited($commission));

            return $commission;
        });
    }
}

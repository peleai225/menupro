<?php

namespace App\Services\Crm;

use App\Enums\Crm\CommissionStatus;
use App\Enums\Crm\CommissionType;
use App\Events\Crm\CommissionCredited;
use App\Models\Crm\Commission;
use App\Models\Crm\Lead;
use App\Models\Crm\Installation;
use App\Models\Crm\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionEngine
{
    public function creditForSignature(Lead $lead): ?Commission
    {
        $user = $lead->assignedUser;
        if (!$user) return null;

        $alreadyCredited = Commission::where('user_id', $user->id)
            ->where('source_type', Lead::class)
            ->where('source_id', $lead->id)
            ->where('type', CommissionType::SIGNATURE)
            ->exists();

        if ($alreadyCredited) return null;

        return $this->credit(
            user: $user,
            type: CommissionType::SIGNATURE,
            amountCents: config('crm.commissions.commercial_first_payment_cents'),
            source: $lead,
            description: "Commission signature — {$lead->restaurant_name}",
        );
    }

    public function creditForInstallation(Installation $installation): ?Commission
    {
        $technician = $installation->technician;
        if (!$technician) return null;

        $alreadyCredited = Commission::where('user_id', $technician->id)
            ->where('source_type', Installation::class)
            ->where('source_id', $installation->id)
            ->where('type', CommissionType::INSTALLATION)
            ->exists();

        if ($alreadyCredited) return null;

        return $this->credit(
            user: $technician,
            type: CommissionType::INSTALLATION,
            amountCents: config('crm.commissions.technician_install_cents'),
            source: $installation,
            description: "Commission installation — {$installation->lead->restaurant_name}",
        );
    }

    public function creditLeaderOverride(Lead $lead): ?Commission
    {
        $team = $lead->team;
        if (!$team || !$team->leader_id) return null;

        $leader = $team->leader;
        if (!$leader) return null;

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

    public function creditGradeBonus(User $user, string $grade): ?Commission
    {
        $amountCents = match ($grade) {
            'commando' => config('crm.commissions.bonus_grade_commando_cents'),
            'elite' => config('crm.commissions.bonus_grade_elite_cents'),
            default => 0,
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

    public function creditRecurring(Lead $lead, string $month): ?Commission
    {
        $user = $lead->assignedUser;
        if (!$user) return null;

        $amountCents = config('crm.commissions.commercial_recurring_cents');
        if ($amountCents <= 0) return null;

        return $this->credit(
            user: $user,
            type: CommissionType::RECURRING,
            amountCents: $amountCents,
            source: $lead,
            description: "Commission récurrente {$month} — {$lead->restaurant_name}",
            metadata: ['month' => $month],
        );
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
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'type' => $type,
                'status' => CommissionStatus::VALIDATED,
                'amount_cents' => $amountCents,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source?->id,
                'description' => $description,
                'metadata' => $metadata,
                'validated_at' => now(),
            ]);

            $wallet->credit($amountCents);

            event(new CommissionCredited($commission));

            return $commission;
        });
    }
}

<?php

namespace App\Services\Crm;

use App\Enums\Crm\Grade;
use App\Events\Crm\GradeChanged;
use App\Models\Crm\Lead;
use App\Models\Crm\UserGrade;
use App\Models\User;

class GradingService
{
    public function __construct(
        private CommissionEngine $commissionEngine,
    ) {}

    public function recalculateForUser(User $user): void
    {
        $conversions = Lead::where('assigned_to', $user->id)
            ->where('status', 'actif')
            ->count();

        $grade = UserGrade::firstOrCreate(
            ['user_id' => $user->id],
            ['current_grade' => Grade::ROOKIE, 'total_conversions' => 0]
        );

        $newGrade = $grade->recalculate($conversions);

        if ($newGrade !== null) {
            $this->commissionEngine->creditGradeBonus($user, $newGrade->value);
            event(new GradeChanged($user, $newGrade));
        }
    }
}

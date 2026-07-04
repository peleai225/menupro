<?php

namespace App\Models\Crm;

use App\Enums\Crm\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGrade extends Model
{
    protected $table = 'crm_grades';

    protected $fillable = [
        'user_id',
        'current_grade',
        'total_conversions',
        'grade_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'current_grade' => Grade::class,
            'total_conversions' => 'integer',
            'grade_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recalculate(int $conversions): ?Grade
    {
        $newGrade = Grade::fromConversions($conversions);
        $oldGrade = $this->current_grade;

        $this->total_conversions = $conversions;

        if ($newGrade !== $oldGrade && $newGrade->minConversions() > $oldGrade->minConversions()) {
            $this->current_grade = $newGrade;
            $this->grade_updated_at = now();
            $this->save();
            return $newGrade;
        }

        $this->save();
        return null;
    }

    public function getProgressToNextAttribute(): int
    {
        $next = match ($this->current_grade) {
            Grade::ROOKIE => Grade::COMMANDO->minConversions(),
            Grade::COMMANDO => Grade::ELITE->minConversions(),
            Grade::ELITE => null,
        };

        if ($next === null) return 100;

        $current = $this->current_grade->minConversions();
        $range = $next - $current;
        $progress = $this->total_conversions - $current;

        return min(100, max(0, (int) round(($progress / $range) * 100)));
    }
}

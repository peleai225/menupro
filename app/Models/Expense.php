<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'supplier',
        'reference',
        'is_recurring',
        'recurrence_period',
        'notes',
    ];

    protected $casts = [
        'category' => ExpenseCategory::class,
        'amount' => 'integer',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForPeriod($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }

    public function scopeByCategory($query, ExpenseCategory $category)
    {
        return $query->where('category', $category);
    }
}

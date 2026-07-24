<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaiterDailyReport extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'restaurant_id',
        'waiter_id',
        'report_date',
        'orders_count',
        'revenue',
        'average_ticket',
        'first_order_at',
        'last_order_at',
    ];

    protected $casts = [
        'report_date'    => 'date',
        'orders_count'   => 'integer',
        'revenue'        => 'integer',
        'average_ticket' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function waiter(): BelongsTo
    {
        return $this->belongsTo(Waiter::class);
    }
}

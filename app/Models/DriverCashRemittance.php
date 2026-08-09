<?php
// app/Models/DriverCashRemittance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverCashRemittance extends Model
{
    protected $fillable = [
        'driver_id', 'restaurant_id', 'debt_id', 'amount_xof',
        'method', 'wave_reference', 'status', 'confirmed_by', 'confirmed_at', 'note',
    ];

    protected $casts = [
        'amount_xof'    => 'integer',
        'confirmed_at'  => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(DriverCashDebt::class, 'debt_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}

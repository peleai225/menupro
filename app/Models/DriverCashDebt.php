<?php
// app/Models/DriverCashDebt.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DriverCashDebt extends Model
{
    protected $fillable = [
        'driver_id', 'restaurant_id', 'order_id', 'delivery_id',
        'amount_xof', 'status', 'settled_at',
    ];

    protected $casts = [
        'amount_xof' => 'integer',
        'settled_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function remittance(): HasOne
    {
        return $this->hasOne(DriverCashRemittance::class, 'debt_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public static function pendingForDriver(int $driverId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('driver_id', $driverId)
            ->where('status', 'pending')
            ->with(['restaurant:id,name', 'order:id,reference'])
            ->get();
    }

    public static function totalPendingForDriver(int $driverId): int
    {
        return (int) static::where('driver_id', $driverId)
            ->where('status', 'pending')
            ->sum('amount_xof');
    }
}

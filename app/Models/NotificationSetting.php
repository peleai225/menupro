<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'email_new_order',
        'email_order_cancelled',
        'email_low_stock',
        'email_subscription_reminder',
        'sms_new_order',
        'sms_order_cancelled',
        'push_new_order',
        'push_order_status',
    ];

    protected $casts = [
        'email_new_order' => 'boolean',
        'email_order_cancelled' => 'boolean',
        'email_low_stock' => 'boolean',
        'email_subscription_reminder' => 'boolean',
        'sms_new_order' => 'boolean',
        'sms_order_cancelled' => 'boolean',
        'push_new_order' => 'boolean',
        'push_order_status' => 'boolean',
    ];

    /**
     * Get default settings
     */
    public static function defaults(): array
    {
        return [
            'email_new_order' => true,
            'email_order_cancelled' => true,
            'email_low_stock' => true,
            'email_subscription_reminder' => true,
            'sms_new_order' => false,
            'sms_order_cancelled' => false,
            'push_new_order' => true,
            'push_order_status' => true,
        ];
    }
}


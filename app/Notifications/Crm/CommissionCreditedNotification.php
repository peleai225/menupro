<?php

namespace App\Notifications\Crm;

use App\Models\Crm\Commission;
use Illuminate\Notifications\Notification;

class CommissionCreditedNotification extends Notification
{
    public function __construct(
        public Commission $commission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'commission_credited',
            'icon'             => 'currency',
            'title'            => 'Commission créditée',
            'body'             => $this->commission->description ?? 'Nouvelle commission',
            'amount_formatted' => $this->commission->amount_formatted,
            'commission_type'  => $this->commission->type->value,
        ];
    }
}

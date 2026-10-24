<?php

namespace App\Notifications\Crm;

use App\Notifications\Channels\PushChannel;
use App\Models\Crm\Commission;
use Illuminate\Notifications\Notification;

class CommissionCreditedNotification extends Notification
{
    public function __construct(
        public Commission $commission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => "💰 Commission créditée : {$this->commission->amount_formatted}",
            'body'  => $this->commission->description ?? 'Nouvelle commission sur votre portefeuille.',
            'data'  => [
                'type'          => 'commission_credited',
                'commission_id' => (string) $this->commission->id,
            ],
            'link' => route('crm.wallet'),
            'tag'  => 'commission-' . $this->commission->id,
        ];
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

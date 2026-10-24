<?php

namespace App\Notifications\Crm;

use App\Notifications\Channels\PushChannel;
use App\Models\Crm\Lead;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification
{
    public function __construct(
        public Lead $lead,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => '🎯 Nouveau lead assigné',
            'body'  => "« {$this->lead->restaurant_name} »" . ($this->lead->city ? " ({$this->lead->city})" : '') . ' vous a été confié.',
            'data'  => [
                'type'    => 'lead_assigned',
                'lead_id' => (string) $this->lead->id,
            ],
            'link' => route('crm.leads.index'),
            'tag'  => 'lead-' . $this->lead->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'lead_assigned',
            'icon'            => 'funnel',
            'title'           => 'Nouveau lead assigné',
            'body'            => "Le lead « {$this->lead->restaurant_name} » vous a été confié.",
            'lead_id'         => $this->lead->id,
            'restaurant_name' => $this->lead->restaurant_name,
            'city'            => $this->lead->city,
        ];
    }
}

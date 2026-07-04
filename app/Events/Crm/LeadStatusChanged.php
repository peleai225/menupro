<?php

namespace App\Events\Crm;

use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Lead $lead,
        public LeadStatus $oldStatus,
        public LeadStatus $newStatus,
    ) {}

    public function broadcastOn(): array
    {
        $channels = [new Channel('crm.leads')];

        if ($this->lead->team_id) {
            $channels[] = new Channel("crm.team.{$this->lead->team_id}");
        }

        if ($this->lead->assigned_to) {
            $channels[] = new Channel("crm.user.{$this->lead->assigned_to}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'lead.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->lead->id,
            'restaurant_name' => $this->lead->restaurant_name,
            'old_status' => $this->oldStatus->value,
            'new_status' => $this->newStatus->value,
            'assigned_to' => $this->lead->assigned_to,
        ];
    }
}

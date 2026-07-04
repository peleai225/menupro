<?php

namespace App\Events\Crm;

use App\Models\Crm\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Lead $lead,
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
        return 'lead.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->lead->id,
            'restaurant_name' => $this->lead->restaurant_name,
            'status' => $this->lead->status->value,
            'assigned_to' => $this->lead->assigned_to,
            'city' => $this->lead->city,
        ];
    }
}

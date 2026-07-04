<?php

namespace App\Events\Crm;

use App\Models\Crm\Commission;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommissionCredited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Commission $commission,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("crm.user.{$this->commission->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'commission.credited';
    }

    public function broadcastWith(): array
    {
        return [
            'amount_formatted' => $this->commission->amount_formatted,
            'type' => $this->commission->type->label(),
            'description' => $this->commission->description,
        ];
    }
}

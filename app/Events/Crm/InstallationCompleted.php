<?php

namespace App\Events\Crm;

use App\Models\Crm\Installation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstallationCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Installation $installation,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('crm.installations'),
            new Channel("crm.user.{$this->installation->technician_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'installation.completed';
    }
}

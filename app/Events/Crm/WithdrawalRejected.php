<?php

namespace App\Events\Crm;

use App\Models\Crm\Withdrawal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Withdrawal $withdrawal,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("crm.user.{$this->withdrawal->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'withdrawal.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'amount_formatted' => $this->withdrawal->amount_formatted,
            'rejection_reason' => $this->withdrawal->rejection_reason,
        ];
    }
}

<?php

namespace App\Events\Crm;

use App\Models\Crm\Withdrawal;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Withdrawal $withdrawal,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("crm.user.{$this->withdrawal->user_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'withdrawal.approved';
    }

    public function broadcastWith(): array
    {
        return [
            'withdrawal_id' => $this->withdrawal->id,
            'amount_formatted' => $this->withdrawal->amount_formatted,
            'payment_method' => $this->withdrawal->payment_method->label(),
        ];
    }
}

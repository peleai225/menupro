<?php
namespace App\Listeners\Crm;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\Crm\WithdrawalStatusNotification;

class SendWithdrawalNotification implements ShouldQueue
{
    public function handle(object $event): void
    {
        if (!isset($event->withdrawal)) return;

        $withdrawal = $event->withdrawal;
        $user = $withdrawal->user ?? $withdrawal->wallet?->user;

        if (!$user) return;

        $status = match (true) {
            str_contains(get_class($event), 'Approved') => 'approved',
            str_contains(get_class($event), 'Paid')     => 'paid',
            str_contains(get_class($event), 'Rejected') => 'rejected',
            default                                      => 'updated',
        };

        $user->notify(new WithdrawalStatusNotification($withdrawal, $status));
    }
}

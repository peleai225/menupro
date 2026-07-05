<?php

namespace App\Listeners\Crm;

use App\Events\Crm\CommissionCredited;
use App\Notifications\Crm\CommissionCreditedNotification;

class SendCommissionNotification
{
    public function handle(CommissionCredited $event): void
    {
        $event->commission->user->notify(new CommissionCreditedNotification($event->commission));
    }
}

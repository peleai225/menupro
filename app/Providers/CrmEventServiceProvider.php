<?php

namespace App\Providers;

use App\Events\Crm\CommissionCredited;
use App\Events\Crm\InstallationCompleted;
use App\Events\Crm\LeadCreated;
use App\Events\Crm\LeadStatusChanged;
use App\Listeners\Crm\HandleInstallationCompleted;
use App\Listeners\Crm\HandleLeadStatusChange;
use App\Listeners\Crm\SendCommissionNotification;
use App\Listeners\Crm\SendLeadAssignedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class CrmEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LeadStatusChanged::class => [
            HandleLeadStatusChange::class,
        ],
        InstallationCompleted::class => [
            HandleInstallationCompleted::class,
        ],
        CommissionCredited::class => [
            SendCommissionNotification::class,
        ],
        LeadCreated::class => [
            SendLeadAssignedNotification::class,
        ],
    ];
}

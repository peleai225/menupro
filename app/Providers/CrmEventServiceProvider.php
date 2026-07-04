<?php

namespace App\Providers;

use App\Events\Crm\InstallationCompleted;
use App\Events\Crm\LeadStatusChanged;
use App\Listeners\Crm\HandleInstallationCompleted;
use App\Listeners\Crm\HandleLeadStatusChange;
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
    ];
}

<?php

use App\Http\Controllers\Crm\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MenuPro CRM Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'crm.role:super_admin,commercial,technician,team_leader'])
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/leads', [DashboardController::class, 'leads'])->name('leads.index');
        Route::get('/installations', [DashboardController::class, 'installations'])->name('installations.index');
        Route::get('/teams', [DashboardController::class, 'teams'])->name('teams.index');
        Route::get('/wallet', [DashboardController::class, 'wallet'])->name('wallet');
        Route::get('/performance', [DashboardController::class, 'performance'])->name('performance');

        // Admin-only
        Route::middleware('crm.role:super_admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/agents', [DashboardController::class, 'adminAgents'])->name('agents');
            Route::get('/withdrawals', [DashboardController::class, 'adminWithdrawals'])->name('withdrawals');
        });

        // Verification publique (pas auth nécessaire)
    });

Route::get('/crm/verify/{uuid}', function (string $uuid) {
    return view('pages.crm.verify', ['uuid' => $uuid]);
})->middleware('throttle:10,1')->name('crm.verify');

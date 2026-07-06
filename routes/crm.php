<?php

use App\Http\Controllers\Crm\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| MenuPro CRM Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'crm.role:super_admin,commercial,technician,team_leader,commando_agent'])
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/leads', [DashboardController::class, 'leads'])->name('leads.index');
        Route::get('/installations', [DashboardController::class, 'installations'])->name('installations.index');
        Route::get('/teams', [DashboardController::class, 'teams'])->name('teams.index');
        Route::get('/wallet', [DashboardController::class, 'wallet'])->name('wallet');
        Route::get('/performance', [DashboardController::class, 'performance'])->name('performance');
        Route::get('/report', [DashboardController::class, 'dailyReport'])->name('report');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

        // Admin-only
        Route::middleware('crm.role:super_admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/agents', [DashboardController::class, 'adminAgents'])->name('agents');
            Route::get('/agents/{agent}', [\App\Http\Controllers\Crm\AgentController::class, 'show'])->name('agents.show');
            Route::get('/teams', [DashboardController::class, 'adminTeams'])->name('teams');
            Route::get('/withdrawals', [DashboardController::class, 'adminWithdrawals'])->name('withdrawals');
            Route::get('/reports', [DashboardController::class, 'adminReports'])->name('reports');
            Route::get('/export/leads', [\App\Http\Controllers\Crm\ExportController::class, 'leads'])->name('export.leads');
            Route::get('/export/commissions', [\App\Http\Controllers\Crm\ExportController::class, 'commissions'])->name('export.commissions');
            Route::get('/export/reports', [\App\Http\Controllers\Crm\ExportController::class, 'reports'])->name('export.reports');
        });

        // Verification publique (pas auth nécessaire)
    });

Route::get('/crm/verify/{uuid}', function (string $uuid) {
    return view('pages.crm.verify', ['uuid' => $uuid]);
})->middleware('throttle:10,1')->name('crm.verify');

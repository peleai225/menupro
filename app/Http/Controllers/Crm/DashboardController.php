<?php

namespace App\Http\Controllers\Crm;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;

        $view = match ($role) {
            UserRole::SUPER_ADMIN => 'pages.crm.admin-dashboard',
            UserRole::TEAM_LEADER => 'pages.crm.team-dashboard',
            UserRole::COMMERCIAL => 'pages.crm.commercial-dashboard',
            UserRole::TECHNICIAN => 'pages.crm.technician-dashboard',
            default => 'pages.crm.commercial-dashboard',
        };

        return view($view);
    }

    public function leads()
    {
        return view('pages.crm.leads');
    }

    public function installations()
    {
        return view('pages.crm.installations');
    }

    public function teams()
    {
        return view('pages.crm.teams');
    }

    public function wallet()
    {
        return view('pages.crm.wallet');
    }

    public function performance()
    {
        return view('pages.crm.performance');
    }

    public function adminAgents()
    {
        return view('pages.crm.admin-agents');
    }

    public function adminWithdrawals()
    {
        return view('pages.crm.admin-withdrawals');
    }

    public function dailyReport()
    {
        return view('pages.crm.daily-report');
    }

    public function adminReports()
    {
        return view('pages.crm.admin-reports');
    }
}

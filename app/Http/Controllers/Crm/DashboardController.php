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

        if ($role === UserRole::REPRESENTATIVE) {
            return $this->representativeDashboard($request);
        }

        $view = match ($role) {
            UserRole::SUPER_ADMIN => 'pages.crm.admin-dashboard',
            UserRole::TEAM_LEADER => 'pages.crm.team-dashboard',
            UserRole::COMMERCIAL => 'pages.crm.commercial-dashboard',
            UserRole::TECHNICIAN => 'pages.crm.technician-dashboard',
            default => 'pages.crm.commercial-dashboard',
        };

        return view($view);
    }

    /**
     * Espace du représentant de ville : restaurants attribués, encaissements du mois,
     * sa part, objectif trimestriel et historique des relevés.
     */
    private function representativeDashboard(Request $request)
    {
        $profile = $request->user()->representativeProfile;
        abort_unless($profile, 403, 'Profil représentant introuvable. Contactez MenuPro.');

        $service = app(\App\Services\Crm\RepresentativeService::class);
        $month = now()->format('Y-m');

        return view('pages.crm.representative-dashboard', [
            'profile' => $profile,
            'current' => $service->calculate($profile, $month),
            'quarter' => $service->quarterProgress($profile),
            'statements' => $profile->statements()->orderByDesc('month')->limit(12)->get(),
            'wallet' => \App\Models\Crm\Wallet::firstOrCreate(
                ['user_id' => $request->user()->id],
                ['balance_cents' => 0, 'total_earned_cents' => 0, 'total_withdrawn_cents' => 0]
            ),
        ]);
    }

    public function representativeExport(Request $request)
    {
        $profile = $request->user()->representativeProfile;
        abort_unless($profile, 403);

        return \App\Http\Controllers\SuperAdmin\RepresentativeController::statementsCsv(
            $profile->statements()->with('profile.user')->orderByDesc('month'),
            'mes-releves-menupro_' . now()->format('Y-m-d') . '.csv'
        );
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

    public function adminTeams()
    {
        return view('pages.crm.admin-teams');
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

    public function profile()
    {
        return view('pages.crm.profile');
    }
}

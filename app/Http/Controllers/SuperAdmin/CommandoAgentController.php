<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AgentVerificationStatus;
use App\Enums\CommissionTransactionStatus;
use App\Enums\CommissionTransactionType;
use App\Http\Controllers\Controller;
use App\Models\CommandoAgent;
use App\Models\CommandoCommissionTransaction;
use App\Models\User;
use App\Notifications\CommandoAgentApprovedNotification;
use App\Notifications\CommandoAgentRejectedNotification;
use App\Notifications\CommandoWithdrawalPaidNotification;
use App\Notifications\CommandoWithdrawalRejectedNotification;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CommandoAgentController extends Controller
{
    public function index(Request $request): View
    {
        $query = CommandoAgent::query()->with('user');

        if ($request->filled('status')) {
            $query->where('status_verification', $request->status);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $agents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $counts = [
            'pending' => CommandoAgent::pendingReview()->count(),
            'valide' => CommandoAgent::valide()->count(),
            'banni' => CommandoAgent::banni()->count(),
        ];

        return view('pages.super-admin.commando-agents.index', compact('agents', 'counts'));
    }

    public function show(CommandoAgent $agent): View
    {
        $agent->load([
            'user',
            'verifyScans' => fn ($q) => $q->latest()->limit(10),
            'commissionTransactions' => fn ($q) => $q->orderByDesc('created_at')->limit(50),
            'deployments' => fn ($q) => $q->orderByDesc('created_at')->limit(30),
            'referredRestaurants' => fn ($q) => $q->latest()->limit(20),
        ]);
        $pendingWithdrawals = $agent->commissionTransactions()
            ->where('type', CommissionTransactionType::WITHDRAWAL_REQUEST)
            ->where('status', CommissionTransactionStatus::PENDING)
            ->orderByDesc('created_at')
            ->get();
        return view('pages.super-admin.commando-agents.show', compact('agent', 'pendingWithdrawals'));
    }

    public function addCommission(Request $request, CommandoAgent $agent): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:100000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $amountCents = (int) round($request->input('amount') * 100);
        CommandoCommissionTransaction::create([
            'commando_agent_id' => $agent->id,
            'type' => CommissionTransactionType::COMMISSION,
            'status' => CommissionTransactionStatus::VALIDATED,
            'amount_cents' => $amountCents,
            'description' => $request->input('description') ?: 'Commission ajoutée par l\'admin',
            'processed_at' => now(),
        ]);
        $agent->increment('balance_cents', $amountCents);
        return back()->with('success', 'Commission ajoutée.');
    }

    public function withdrawalPay(Request $request, CommandoAgent $agent, CommandoCommissionTransaction $transaction): RedirectResponse
    {
        if ($transaction->commando_agent_id !== $agent->id
            || $transaction->type !== CommissionTransactionType::WITHDRAWAL_REQUEST
            || $transaction->status !== CommissionTransactionStatus::PENDING) {
            return back()->with('error', 'Demande invalide.');
        }
        $transaction->update(['status' => CommissionTransactionStatus::WITHDRAWN, 'processed_at' => now()]);
        CommandoCommissionTransaction::create([
            'commando_agent_id' => $agent->id,
            'type' => CommissionTransactionType::WITHDRAWAL_PAID,
            'status' => CommissionTransactionStatus::VALIDATED,
            'amount_cents' => -$transaction->amount_cents,
            'description' => 'Retrait effectué',
            'meta' => ['withdrawal_request_id' => $transaction->id],
            'processed_at' => now(),
        ]);
        $agent->decrement('balance_cents', $transaction->amount_cents);

        if ($agent->user) {
            $agent->user->notify(new CommandoWithdrawalPaidNotification($agent, $transaction->amount_cents));
        }

        return back()->with('success', 'Retrait marqué comme payé.');
    }

    public function withdrawalReject(Request $request, CommandoAgent $agent, CommandoCommissionTransaction $transaction): RedirectResponse
    {
        if ($transaction->commando_agent_id !== $agent->id
            || $transaction->type !== CommissionTransactionType::WITHDRAWAL_REQUEST
            || $transaction->status !== CommissionTransactionStatus::PENDING) {
            return back()->with('error', 'Demande invalide.');
        }
        $amountCents = $transaction->amount_cents;
        $transaction->update(['status' => CommissionTransactionStatus::REJECTED, 'processed_at' => now()]);

        if ($agent->user) {
            $agent->user->notify(new CommandoWithdrawalRejectedNotification(
                $agent,
                $amountCents,
                $request->input('reason')
            ));
        }

        return back()->with('success', 'Demande de retrait rejetée.');
    }

    public function approve(Request $request, CommandoAgent $agent): RedirectResponse
    {
        if ($agent->status_verification !== AgentVerificationStatus::PENDING_REVIEW) {
            return back()->with('error', 'Cet agent n\'est pas en attente de validation.');
        }

        $agent->update([
            'status_verification' => AgentVerificationStatus::VALIDE,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $user = $agent->user;
        $welcomeToken = null;
        if (!$agent->user_id) {
            $welcomeToken = Str::random(64);
            $user = User::create([
                'name' => $agent->full_name,
                'email' => 'agent-' . $agent->uuid . '@commando.menupro.local',
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(32)),
                'role' => \App\Enums\UserRole::COMMANDO_AGENT,
                'phone' => $agent->whatsapp,
                'is_active' => true,
                'welcome_token' => $welcomeToken,
            ]);
            $agent->update(['user_id' => $user->id]);
        } elseif ($agent->user && !$agent->user->welcome_token) {
            $welcomeToken = Str::random(64);
            $agent->user->update(['welcome_token' => $welcomeToken]);
        } else {
            $welcomeToken = $agent->user?->welcome_token;
        }

        $welcomeUrl = $welcomeToken
            ? route('commando.welcome', ['token' => $welcomeToken])
            : null;

        if ($user) {
            $user->notify(new CommandoAgentApprovedNotification($agent));
        }

        if ($welcomeUrl && $user) {
            $sent = app(WhatsAppService::class)->sendAgentWelcome($agent, $welcomeUrl, $user->email);
            return redirect()->route('super-admin.commando.agents.show', $agent)
                ->with('success', 'Agent validé avec succès.')
                ->with('welcome_url', $welcomeUrl)
                ->with('whatsapp_sent', $sent);
        }

        return redirect()->route('super-admin.commando.agents.show', $agent)
            ->with('success', 'Agent validé avec succès.');
    }

    public function reject(Request $request, CommandoAgent $agent): RedirectResponse
    {
        $request->validate(['reason' => 'nullable', 'string', 'max:500']);

        if ($agent->status_verification !== AgentVerificationStatus::PENDING_REVIEW) {
            return back()->with('error', 'Cet agent n\'est pas en attente de validation.');
        }

        $agent->update([
            'status_verification' => AgentVerificationStatus::REJETE,
            'rejection_reason' => $request->input('reason'),
        ]);

        return back()->with('success', 'Agent rejeté.');
    }

    public function ban(Request $request, CommandoAgent $agent): RedirectResponse
    {
        $agent->update([
            'status_verification' => AgentVerificationStatus::BANNI,
            'banned_at' => now(),
        ]);

        return back()->with('success', 'Agent révoqué. Le QR affichera « Agent invalide ».');
    }

    /**
     * Supprimer définitivement un agent.
     * Le compte utilisateur lié (s'il existe) est aussi supprimé pour éviter les comptes orphelins.
     */
    public function destroy(CommandoAgent $agent): RedirectResponse
    {
        $userId = $agent->user_id;
        $agent->delete();

        if ($userId) {
            try {
                User::where('id', $userId)->where('role', \App\Enums\UserRole::COMMANDO_AGENT)->delete();
            } catch (\Throwable) {
                // Ignorer si contraintes FK (ex. activity_log) ; l'agent est bien supprimé
            }
        }

        return redirect()->route('super-admin.commando.agents.index')
            ->with('success', 'Agent supprimé.');
    }
}

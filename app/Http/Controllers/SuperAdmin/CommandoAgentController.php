<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\AgentVerificationStatus;
use App\Enums\CommissionTransactionStatus;
use App\Enums\CommissionTransactionType;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\CommandoAgent;
use App\Models\CommandoCommissionTransaction;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use App\Notifications\CommandoAgentApprovedNotification;
use App\Notifications\CommandoAgentRejectedNotification;
use App\Notifications\CommandoWithdrawalPaidNotification;
use App\Notifications\CommandoWithdrawalRejectedNotification;
use App\Notifications\Commando\CommandoAgentBannedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $stats = [
            'total' => CommandoAgent::count(),
            'pending' => $counts['pending'],
            'validated' => $counts['valide'],
            'banned' => $counts['banni'],
            'total_commissions_fcfa' => CommandoCommissionTransaction::where('type', CommissionTransactionType::COMMISSION)
                ->where('status', CommissionTransactionStatus::VALIDATED)
                ->sum('amount_cents') / 100,
            'total_withdrawn_fcfa' => CommandoCommissionTransaction::where('type', CommissionTransactionType::WITHDRAWAL_PAID)
                ->sum(DB::raw('ABS(amount_cents)')) / 100,
            'total_pending_withdrawal_fcfa' => CommandoCommissionTransaction::where('type', CommissionTransactionType::WITHDRAWAL_REQUEST)
                ->where('status', CommissionTransactionStatus::PENDING)
                ->sum('amount_cents') / 100,
            'restaurants_referred_active' => Restaurant::whereNotNull('referred_by_agent_id')
                ->where('status', RestaurantStatus::ACTIVE)
                ->count(),
            'top_agents' => CommandoAgent::with('user')
                ->where('status_verification', AgentVerificationStatus::VALIDE)
                ->withCount('referredRestaurants')
                ->orderByDesc('referred_restaurants_count')
                ->limit(5)
                ->get(),
            'new_agents_this_month' => CommandoAgent::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('pages.super-admin.commando-agents.index', compact('agents', 'counts', 'stats'));
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

        // Total commissions reçues (validées)
        $totalCommissionsCents = $agent->commissionTransactions()
            ->where('type', CommissionTransactionType::COMMISSION)
            ->where('status', CommissionTransactionStatus::VALIDATED)
            ->sum('amount_cents');

        // Restaurants parrainés actifs vs total
        $restaurantsActive = $agent->referredRestaurants()
            ->where('status', RestaurantStatus::ACTIVE)
            ->count();
        $restaurantsTotal = $agent->referredRestaurants()->count();

        // Dernière activité
        $lastScan = $agent->verifyScans()->latest()->first();
        $lastCommission = $agent->commissionTransactions()
            ->where('type', CommissionTransactionType::COMMISSION)
            ->latest()
            ->first();

        $agentStats = [
            'total_commissions_fcfa' => $totalCommissionsCents / 100,
            'restaurants_active' => $restaurantsActive,
            'restaurants_total' => $restaurantsTotal,
            'last_scan_at' => $lastScan?->created_at,
            'last_commission_at' => $lastCommission?->created_at,
        ];

        return view('pages.super-admin.commando-agents.show', compact('agent', 'pendingWithdrawals', 'agentStats'));
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
        if ((int) $transaction->commando_agent_id !== (int) $agent->id
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
        if ((int) $transaction->commando_agent_id !== (int) $agent->id
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

        $welcomeToken = Str::random(64);

        $user = $agent->user;
        if (!$agent->user_id) {
            $user = User::create([
                'name' => $agent->full_name,
                'email' => 'agent-' . $agent->uuid . '@commando.menupro.local',
                'email_verified_at' => now(),
                'password' => $agent->password,
                'role' => \App\Enums\UserRole::COMMERCIAL,
                'phone' => $agent->whatsapp,
                'is_active' => true,
                'welcome_token' => $welcomeToken,
                'welcome_token_expires_at' => now()->addHours(72),
            ]);
            $agent->update(['user_id' => $user->id]);
        } elseif ($user && !$user->welcome_token) {
            $user->update([
                'welcome_token' => $welcomeToken,
                'welcome_token_expires_at' => now()->addHours(72),
            ]);
        } else {
            $welcomeToken = $user?->welcome_token;
        }

        if ($user) {
            $user->notify(new CommandoAgentApprovedNotification($agent));
        }

        $welcomeUrl = $welcomeToken
            ? route('commando.welcome', ['token' => $welcomeToken])
            : null;

        return redirect()->route('super-admin.commando.agents.show', $agent)
            ->with('success', 'Agent validé. Lien de bienvenue généré (72h).')
            ->with('welcome_url', $welcomeUrl);
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

        if ($agent->user) {
            $agent->user->notify(new CommandoAgentRejectedNotification($agent, $request->input('reason')));
        }

        return back()->with('success', 'Agent rejeté. Il a été notifié par email.');
    }

    public function ban(Request $request, CommandoAgent $agent): RedirectResponse
    {
        $request->validate(['ban_reason' => 'nullable|string|max:500']);

        $agent->update([
            'status_verification' => AgentVerificationStatus::BANNI,
            'banned_at' => now(),
        ]);

        // Désactiver le User associé
        if ($agent->user) {
            $agent->user->update(['is_active' => false]);
        }

        // Notifier l'agent avec la raison
        if ($agent->user) {
            $agent->user->notify(new CommandoAgentBannedNotification($agent, $request->ban_reason));
        }

        ActivityLog::log(
            'commando_ban',
            auth()->user(),
            "Agent {$agent->full_name} banni" . ($request->ban_reason ? ' – Motif : ' . $request->ban_reason : ''),
            ['agent_id' => $agent->id, 'ban_reason' => $request->ban_reason]
        );

        return back()->with('success', 'Agent révoqué. Le QR affichera « Agent invalide ».');
    }

    public function unban(CommandoAgent $agent): RedirectResponse
    {
        if ($agent->status_verification !== AgentVerificationStatus::BANNI) {
            return back()->with('error', 'Cet agent n\'est pas banni.');
        }

        $agent->update([
            'status_verification' => AgentVerificationStatus::VALIDE,
            'banned_at' => null,
        ]);

        // Réactiver le User associé
        if ($agent->user) {
            $agent->user->update(['is_active' => true]);
        }

        // Notifier l'agent
        if ($agent->user) {
            $agent->user->notify(new CommandoAgentApprovedNotification($agent));
        }

        ActivityLog::log(
            'commando_unban',
            auth()->user(),
            "Agent {$agent->full_name} débanni",
            ['agent_id' => $agent->id]
        );

        return back()->with('success', 'Agent débanni avec succès.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = CommandoAgent::with('user')
            ->when($request->status, fn($q, $s) => $q->where('status_verification', $s))
            ->when($request->city, fn($q, $c) => $q->where('city', 'like', "%{$c}%"))
            ->orderByDesc('created_at');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="agents-commando-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($handle, ['ID', 'Badge ID', 'Nom', 'Prénom', 'WhatsApp', 'Ville', 'Statut', 'Grade', 'Restaurants parrainés', 'Solde (FCFA)', 'Date inscription', 'Date approbation']);

            $query->chunk(500, function ($agents) use ($handle) {
                foreach ($agents as $agent) {
                    fputcsv($handle, [
                        $agent->id,
                        $agent->badge_id,
                        $agent->last_name,
                        $agent->first_name,
                        $agent->whatsapp,
                        $agent->city,
                        $agent->status_verification->value ?? $agent->status_verification,
                        $agent->grade->value ?? 'rookie',
                        $agent->referredRestaurants()->count(),
                        number_format(($agent->balance_cents ?? 0) / 100, 2),
                        $agent->created_at->format('d/m/Y'),
                        $agent->approved_at?->format('d/m/Y') ?? '',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }

    public function exportCommissions(Request $request): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="commissions-commando-' . now()->format('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Agent', 'Type', 'Montant (FCFA)', 'Description', 'Statut', 'Date']);

            CommandoCommissionTransaction::with('commandoAgent')
                ->when($request->agent_id, fn($q, $id) => $q->where('commando_agent_id', $id))
                ->when($request->from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
                ->when($request->to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
                ->orderByDesc('created_at')
                ->chunk(500, function ($transactions) use ($handle) {
                    foreach ($transactions as $tx) {
                        $meta = is_array($tx->meta) ? $tx->meta : [];
                        fputcsv($handle, [
                            $tx->id,
                            $tx->commandoAgent?->full_name ?? 'N/A',
                            $tx->type->value ?? $tx->type,
                            number_format(($tx->amount_cents ?? 0) / 100, 2),
                            $meta['restaurant_name'] ?? $meta['restaurant_id'] ?? ($tx->description ?? ''),
                            $tx->status->value ?? $tx->status,
                            $tx->created_at->format('d/m/Y H:i'),
                        ]);
                    }
                });

            fclose($handle);
        }, 200, $headers);
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

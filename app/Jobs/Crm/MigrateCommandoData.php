<?php

namespace App\Jobs\Crm;

use App\Enums\Crm\CommissionStatus;
use App\Enums\Crm\CommissionType;
use App\Enums\Crm\Grade;
use App\Enums\Crm\LeadStatus;
use App\Enums\UserRole;
use App\Models\CommandoAgent;
use App\Models\CommandoCommissionTransaction;
use App\Models\CommandoDeployment;
use App\Models\Crm\CommercialProfile;
use App\Models\Crm\Commission;
use App\Models\Crm\Lead;
use App\Models\Crm\UserGrade;
use App\Models\Crm\Wallet;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateCommandoData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('[CRM Migration] Démarrage de la migration Commando → CRM');

        $this->migrateAgents();
        $this->migrateDeployments();
        $this->migrateCommissions();
        $this->migrateRestaurantReferrals();
        $this->calculateGrades();

        Log::info('[CRM Migration] Migration terminée');
    }

    private function migrateAgents(): void
    {
        $agents = CommandoAgent::with('user')->whereNotNull('user_id')->get();

        foreach ($agents as $agent) {
            $user = $agent->user;
            if (!$user) continue;

            // Update user role to COMMERCIAL
            $user->update(['role' => UserRole::COMMERCIAL]);

            // Create commercial profile
            CommercialProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'uuid' => $agent->uuid,
                    'badge_id' => $agent->badge_id,
                    'city' => $agent->city,
                    'statut_metier' => $agent->statut_metier,
                    'id_document_path' => $agent->id_document_path,
                    'verification_status' => $this->mapVerificationStatus($agent->status_verification),
                    'approved_at' => $agent->approved_at,
                    'banned_at' => $agent->banned_at,
                    'rejection_reason' => $agent->rejection_reason,
                ]
            );

            // Create wallet
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance_cents' => $agent->balance_cents ?? 0,
                    'total_earned_cents' => $agent->balance_cents ?? 0,
                    'total_withdrawn_cents' => 0,
                ]
            );
        }

        Log::info("[CRM Migration] {$agents->count()} agents migrés");
    }

    private function migrateDeployments(): void
    {
        $deployments = CommandoDeployment::with('commandoAgent.user')->get();
        $count = 0;

        foreach ($deployments as $deployment) {
            $agent = $deployment->commandoAgent;
            if (!$agent || !$agent->user_id) continue;

            Lead::firstOrCreate(
                ['restaurant_name' => $deployment->restaurant_name, 'assigned_to' => $agent->user_id],
                [
                    'manager_name' => $deployment->manager_name,
                    'phone' => $deployment->phone,
                    'latitude' => $deployment->latitude,
                    'longitude' => $deployment->longitude,
                    'status' => $this->mapDeploymentStatus($deployment->status),
                    'source' => 'terrain',
                    'restaurant_id' => $deployment->restaurant_id,
                    'converted_at' => $deployment->status === 'actif' ? $deployment->updated_at : null,
                ]
            );
            $count++;
        }

        Log::info("[CRM Migration] {$count} deployments migrés en leads");
    }

    private function migrateCommissions(): void
    {
        $transactions = CommandoCommissionTransaction::with('commandoAgent.user')->get();
        $count = 0;

        foreach ($transactions as $tx) {
            $agent = $tx->commandoAgent;
            if (!$agent || !$agent->user_id) continue;

            $wallet = Wallet::where('user_id', $agent->user_id)->first();
            if (!$wallet) continue;

            Commission::firstOrCreate(
                ['wallet_id' => $wallet->id, 'created_at' => $tx->created_at, 'amount_cents' => $tx->amount_cents],
                [
                    'user_id' => $agent->user_id,
                    'type' => $this->mapCommissionType($tx->type),
                    'status' => CommissionStatus::VALIDATED,
                    'description' => $tx->description,
                    'metadata' => $tx->meta,
                    'validated_at' => $tx->processed_at ?? $tx->created_at,
                ]
            );
            $count++;
        }

        Log::info("[CRM Migration] {$count} transactions migrées");
    }

    private function migrateRestaurantReferrals(): void
    {
        $restaurants = Restaurant::whereNotNull('referred_by_agent_id')->get();
        $count = 0;

        foreach ($restaurants as $restaurant) {
            $agent = CommandoAgent::find($restaurant->referred_by_agent_id);
            if (!$agent || !$agent->user_id) continue;

            $restaurant->update(['referred_by_user_id' => $agent->user_id]);
            $count++;
        }

        Log::info("[CRM Migration] {$count} restaurants reliés aux users");
    }

    private function calculateGrades(): void
    {
        $commercials = User::where('role', UserRole::COMMERCIAL)->get();

        foreach ($commercials as $user) {
            $conversions = Lead::where('assigned_to', $user->id)
                ->where('status', LeadStatus::ACTIF)
                ->count();

            UserGrade::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'current_grade' => Grade::fromConversions($conversions),
                    'total_conversions' => $conversions,
                ]
            );
        }
    }

    private function mapVerificationStatus(string $status): string
    {
        return match ($status) {
            'shadow' => 'shadow',
            'pending_review' => 'pending_review',
            'valide' => 'valide',
            'rejete' => 'rejete',
            'banni' => 'banni',
            default => 'pending_review',
        };
    }

    private function mapDeploymentStatus(string $status): LeadStatus
    {
        return match ($status) {
            'en_negociation' => LeadStatus::CONTACTE,
            'en_attente_paiement' => LeadStatus::SIGNATURE,
            'actif' => LeadStatus::ACTIF,
            default => LeadStatus::NOUVEAU,
        };
    }

    private function mapCommissionType(string $type): CommissionType
    {
        return match ($type) {
            'commission' => CommissionType::SIGNATURE,
            'withdrawal_request', 'withdrawal_paid' => CommissionType::SIGNATURE,
            default => CommissionType::SIGNATURE,
        };
    }
}

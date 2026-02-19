<?php

namespace App\Services;

use App\Enums\CommissionTransactionStatus;
use App\Enums\CommissionTransactionType;
use App\Models\CommandoAgent;
use App\Models\CommandoCommissionTransaction;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class CommandoCommissionService
{
    /**
     * Crédite l'agent parrain si le restaurant paie son abonnement (premier paiement uniquement si config).
     */
    public function creditAgentForRestaurantSubscription(Restaurant $restaurant, Subscription $subscription): bool
    {
        if (!$restaurant->referred_by_agent_id) {
            return false;
        }

        $agent = CommandoAgent::find($restaurant->referred_by_agent_id);
        if (!$agent || $agent->isBanni()) {
            return false;
        }

        $onlyFirst = (bool) (SystemSetting::has('commando_commission_only_first_payment')
            ? SystemSetting::get('commando_commission_only_first_payment', true)
            : config('commando.commission_only_first_payment', true));
        if ($onlyFirst && $this->agentAlreadyCreditedForRestaurant($agent, $restaurant)) {
            Log::channel('payments')->info('Commando: agent already credited for restaurant', [
                'agent_id' => $agent->id,
                'restaurant_id' => $restaurant->id,
            ]);
            return false;
        }

        $cents = (int) (SystemSetting::has('commando_commission_cents_first_payment')
            ? SystemSetting::get('commando_commission_cents_first_payment', 500000)
            : config('commando.commission_cents_first_payment', 500000));
        if ($cents <= 0) {
            return false;
        }

        CommandoCommissionTransaction::create([
            'commando_agent_id' => $agent->id,
            'type' => CommissionTransactionType::COMMISSION,
            'status' => CommissionTransactionStatus::VALIDATED,
            'amount_cents' => $cents,
            'description' => 'Commission parrainage – ' . $restaurant->name,
            'meta' => [
                'restaurant_id' => $restaurant->id,
                'subscription_id' => $subscription->id,
            ],
            'processed_at' => now(),
        ]);

        $agent->increment('balance_cents', $cents);

        Log::channel('payments')->info('Commando: commission credited', [
            'agent_id' => $agent->id,
            'restaurant_id' => $restaurant->id,
            'amount_cents' => $cents,
        ]);

        return true;
    }

    protected function agentAlreadyCreditedForRestaurant(CommandoAgent $agent, Restaurant $restaurant): bool
    {
        return CommandoCommissionTransaction::query()
            ->where('commando_agent_id', $agent->id)
            ->where('type', CommissionTransactionType::COMMISSION)
            ->whereNotNull('meta')
            ->where('meta->restaurant_id', $restaurant->id)
            ->exists();
    }
}

<?php

namespace App\Jobs;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\PushToken;
use App\Notifications\NewOrderNotification;
use App\Services\FcmService;
use App\Services\WhatsAppService;
use App\Support\PushBadge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Prévient le restaurant (ou le stand) d'une nouvelle commande à traiter, sur tous les canaux :
 * cloche du tableau de bord, push sur tous ses appareils (même onglet fermé) et WhatsApp.
 */
class AlertRestaurantNewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    // File par défaut : l'alerte la plus critique ne doit pas dépendre d'un worker dédié
    public function __construct(public int $orderId) {}

    public function handle(FcmService $fcm, WhatsAppService $whatsApp): void
    {
        $order = Order::withoutGlobalScope('restaurant')->with('restaurant')->find($this->orderId);
        if (!$order || !$order->restaurant) {
            return;
        }

        $staff = $order->restaurant->staffToAlert();

        // Une seule fois par commande, même si le job est rejoué après un échec partiel
        $alreadyNotified = $staff->isNotEmpty() && $staff->first()->notifications()
            ->where('type', NewOrderNotification::class)
            ->where('data->order_id', $order->id)
            ->exists();

        if (!$alreadyNotified) {
            $staff->each(fn ($user) => $user->notify(new NewOrderNotification($order)));

            $this->sendPush($fcm, $order, $staff->pluck('id')->all());
            $this->sendWhatsApp($whatsApp, $order, $staff);
        }
    }

    private function sendPush(FcmService $fcm, Order $order, array $userIds): void
    {
        if (!$userIds || !$fcm->isConfigured()) {
            return;
        }

        $tokens = PushToken::whereIn('user_id', $userIds)->pluck('token')->all();
        if (!$tokens) {
            return;
        }

        $total = number_format($order->total, 0, ',', ' ');

        $fcm->sendToMultiple(
            $tokens,
            '🔔 Nouvelle commande #' . $order->reference,
            "{$order->customer_name} · {$order->items_count} article(s) · {$total} F CFA",
            [
                'type'     => 'new_order',
                'order_id' => (string) $order->id,
                'badge'    => PushBadge::forRestaurant($order->restaurant_id),
            ],
            [
                'link'                => route('restaurant.orders'),
                'tag'                 => 'order-' . $order->id,
                'require_interaction' => true,
                'icon'                => '/icon-192.png',
                'ttl'                 => 1800,
            ],
        );
    }

    private function sendWhatsApp(WhatsAppService $whatsApp, Order $order, $staff): void
    {
        $phone = $order->restaurant->phone
            ?: $staff->firstWhere('role', UserRole::RESTAURANT_ADMIN)?->phone;

        if (!$phone) {
            return;
        }

        try {
            $whatsApp->sendNewOrderToRestaurant($order, $phone);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp nouvelle commande restaurant échoué', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}

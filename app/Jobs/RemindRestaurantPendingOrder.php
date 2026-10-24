<?php

namespace App\Jobs;

use App\Contracts\SmsSender;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use App\Notifications\Admin\RestaurantUnresponsiveNotification;
use App\Notifications\OrderAwaitingAcceptanceNotification;
use App\Services\CustomerPushService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Relances d'une commande que le restaurant n'a ni acceptée ni refusée (délais : config/orders.php,
 * comptés depuis la réception de la commande) :
 *
 *   1. T+2 min : push « ⏰ Commande #X en attente depuis 2 min » au personnel + WhatsApp ;
 *   2. T+3 min : SMS au téléphone du restaurant (App\Contracts\SmsSender) ;
 *   3. T+5 min : alerte super-admins (cloche + push, lien back-office) et message rassurant au client.
 *
 * Programmé à la réception (Order::announceToRestaurant), chaque étape programme la suivante.
 * S'arrête dès que la commande n'est plus « à accepter » (acceptée, refusée, annulée). Idempotent :
 * une étape n'est envoyée qu'une fois (orders.acceptance_reminder_level), même si le job est rejoué.
 * Pas d'annulation automatique : l'alerte admin reste visible jusqu'à traitement.
 */
class RemindRestaurantPendingOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const STEP_PUSH = 1;
    public const STEP_SMS = 2;
    public const STEP_ADMIN = 3;

    private const STEP_KEYS = [
        self::STEP_PUSH  => 'push',
        self::STEP_SMS   => 'sms',
        self::STEP_ADMIN => 'admin',
    ];

    // Un envoi partiellement réussi ne doit pas être répété
    public int $tries = 1;

    public function __construct(public int $orderId, public int $step = self::STEP_PUSH) {}

    /**
     * Programme la première relance d'une commande qui vient d'arriver.
     */
    public static function scheduleFirst(Order $order): void
    {
        self::dispatch($order->id, self::STEP_PUSH)
            ->delay(self::dueAt($order, self::STEP_PUSH))
            ->afterCommit();
    }

    public static function dueAt(Order $order, int $step): \Carbon\Carbon
    {
        $minutes = (int) config('orders.acceptance.reminders.' . self::STEP_KEYS[$step], 0);

        return $order->acceptanceRequestedAt()->addMinutes($minutes);
    }

    public function handle(WhatsAppService $whatsApp, SmsSender $sms, CustomerPushService $customerPush): void
    {
        if (!isset(self::STEP_KEYS[$this->step])) {
            return;
        }

        $order = Order::withoutGlobalScope('restaurant')->with('restaurant')->find($this->orderId);
        if (!$order || !$order->restaurant || !$order->isAwaitingAcceptance()) {
            return;
        }

        // File synchrone (délais ignorés) : on ne relance pas avant l'heure
        if (now()->lt(self::dueAt($order, $this->step))) {
            return;
        }

        // Réserve l'étape : un seul envoi même si deux jobs identiques tournent en même temps
        $claimed = Order::withoutGlobalScope('restaurant')
            ->whereKey($order->id)
            ->where('status', OrderStatus::PAID->value)
            ->where('acceptance_reminder_level', '<', $this->step)
            ->update(['acceptance_reminder_level' => $this->step]);

        if (!$claimed) {
            return;
        }

        // Étape suivante programmée avant l'envoi : une panne d'un canal n'arrête pas les relances
        if ($this->step < self::STEP_ADMIN) {
            self::dispatch($order->id, $this->step + 1)->delay(self::dueAt($order, $this->step + 1));
        }

        $minutes = (int) floor($order->acceptanceRequestedAt()->diffInMinutes(now(), true));

        match ($this->step) {
            self::STEP_PUSH  => $this->remindStaff($order, $whatsApp, $minutes),
            self::STEP_SMS   => $this->sendSms($order, $sms, $minutes),
            self::STEP_ADMIN => $this->alertAdmins($order, $whatsApp, $customerPush, $minutes),
        };
    }

    private function remindStaff(Order $order, WhatsAppService $whatsApp, int $minutes): void
    {
        $staff = $order->restaurant->staffToAlert();
        if ($staff->isNotEmpty()) {
            Notification::send($staff, new OrderAwaitingAcceptanceNotification($order, $minutes));
        }

        $phone = $this->restaurantPhone($order, $staff);
        if (!$phone) {
            return;
        }

        try {
            $whatsApp->sendOrderAwaitingReminder($order, $phone, $minutes);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp relance commande en attente échoué', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function sendSms(Order $order, SmsSender $sms, int $minutes): void
    {
        $phone = $this->restaurantPhone($order, $order->restaurant->staffToAlert());
        if (!$phone) {
            return;
        }

        $message = "MenuPro : commande #{$order->reference} en attente depuis {$minutes} min. "
            . 'Acceptez-la ou refusez-la vite : ' . route('restaurant.orders');

        try {
            $sms->send($phone, $message);
        } catch (\Throwable $e) {
            Log::warning('SMS relance commande en attente échoué', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    private function alertAdmins(Order $order, WhatsAppService $whatsApp, CustomerPushService $customerPush, int $minutes): void
    {
        $admins = User::superAdmins()->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new RestaurantUnresponsiveNotification($order, $minutes));
        }

        // Le client ne doit pas croire sa commande perdue
        try {
            $customerPush->notifyOrderDelayed($order);
        } catch (\Throwable $e) {
            Log::warning('Push client commande retardée échoué', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        if ($order->customer_phone) {
            try {
                $whatsApp->sendOrderDelayedToCustomer($order);
            } catch (\Throwable $e) {
                Log::warning('WhatsApp client commande retardée échoué', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }
    }

    private function restaurantPhone(Order $order, $staff): ?string
    {
        return $order->restaurant->phone
            ?: $staff->firstWhere('role', UserRole::RESTAURANT_ADMIN)?->phone;
    }
}

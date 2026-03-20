<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuProHubService
{
    protected float $commissionRate = 0.5;

    /**
     * Parse SMS text to extract amount (FCFA) and sender phone.
     * Supports common formats from Wave, Orange Money, MTN.
     */
    public function parsePaymentSms(string $smsText, ?string $senderPhone = null): ?array
    {
        $smsText = trim($smsText);
        $senderPhone = $this->normalizePhone($senderPhone ?? '');

        // Extract amount - patterns: "5000 FCFA", "5 000 F", "5000F", "5.000 FCFA", "Vous avez reçu 5000"
        $amount = null;
        if (preg_match('/(\d[\d\s.]*)\s*(?:FCFA|F\b|FCFA)/ui', $smsText, $m)) {
            $amount = (int) preg_replace('/\s|\./', '', $m[1]);
        } elseif (preg_match('/re[çc]u\s+(\d[\d\s.]*)/ui', $smsText, $m)) {
            $amount = (int) preg_replace('/\s|\./', '', $m[1]);
        } elseif (preg_match('/(\d{4,})\s*(?:de|from)/ui', $smsText, $m)) {
            $amount = (int) $m[1];
        }

        if (!$amount || $amount < 100) {
            return null;
        }

        return [
            'amount' => $amount,
            'sender_phone' => $senderPhone,
        ];
    }

    /**
     * Find order matching payment (amount + optional sender, within time window).
     */
    public function findMatchingOrder(int $amount, ?string $senderPhone, int $restaurantId = null): ?Order
    {
        $query = Order::query()
            ->where('payment_status', PaymentStatus::PENDING_VERIFICATION)
            ->where('status', OrderStatus::PENDING_PAYMENT)
            ->where('total', $amount)
            ->where('created_at', '>=', now()->subHours(2)); // 2h window

        if ($restaurantId) {
            $query->where('restaurant_id', $restaurantId);
        }

        if ($senderPhone) {
            $query->where(function ($q) use ($senderPhone) {
                $q->where('customer_phone', 'like', "%{$senderPhone}%")
                    ->orWhereRaw("REPLACE(REPLACE(REPLACE(customer_phone, ' ', ''), '-', ''), '+', '') LIKE ?", ['%' . preg_replace('/\D/', '', $senderPhone) . '%']);
            });
        }

        $orders = $query->orderByDesc('created_at')->get();

        if ($orders->count() === 1) {
            return $orders->first();
        }

        if ($orders->count() > 1 && $senderPhone) {
            // Prefer exact phone match
            $exact = $orders->first(fn ($o) => $this->phonesMatch($o->customer_phone, $senderPhone));
            return $exact ?? $orders->first();
        }

        return $orders->first();
    }

    /**
     * Verify payment and mark order as paid.
     */
    public function verifyAndMarkPaid(Order $order, string $paymentMethod, ?string $paymentRef = null): bool
    {
        if ($order->payment_status === PaymentStatus::COMPLETED) {
            return true;
        }

        $order->markAsPaid([
            'method' => $paymentMethod,
            'reference' => $paymentRef,
            'metadata' => ['verified_via' => 'menupo_hub_webhook', 'verified_at' => now()->toIso8601String()],
        ]);

        Log::info('MenuPro Hub: Order marked as paid', [
            'order_id' => $order->id,
            'reference' => $order->reference,
            'method' => $paymentMethod,
        ]);

        return true;
    }

    /**
     * Deduct commission when order is validated (PAID → CONFIRMED or similar).
     * Only for MenuPro Hub payment methods (wave, orange, mtn).
     */
    public function deductCommission(Order $order): bool
    {
        $hubMethods = ['wave', 'orange', 'mtn', 'moov'];
        if (!in_array($order->payment_method, $hubMethods)) {
            return true;
        }

        if ($order->commissionLog()->exists()) {
            return true; // Already deducted
        }

        $commissionAmount = (float) round($order->total * ($this->commissionRate / 100), 2);

        return DB::transaction(function () use ($order, $commissionAmount) {
            CommissionLog::create([
                'restaurant_id' => $order->restaurant_id,
                'order_id' => $order->id,
                'amount' => $commissionAmount,
                'order_total' => $order->total,
                'commission_rate' => $this->commissionRate,
            ]);

            $restaurant = $order->restaurant;
            $restaurant->decrement('commission_wallet_balance', $commissionAmount);

            Log::info('MenuPro Hub: Commission deducted', [
                'order_id' => $order->id,
                'amount' => $commissionAmount,
                'restaurant_id' => $restaurant->id,
            ]);

            return true;
        });
    }

    /**
     * Check if restaurant can use MenuPro Hub (has balance and at least one method).
     */
    public function canUseHub(Restaurant $restaurant): bool
    {
        if (!$restaurant->menupo_hub_enabled) {
            return false;
        }

        if (($restaurant->commission_wallet_balance ?? 0) <= 0) {
            return false;
        }

        return !empty($restaurant->wave_merchant_id)
            || !empty($restaurant->orange_money_number)
            || !empty($restaurant->mtn_money_number)
            || !empty($restaurant->moov_money_number);
    }

    /**
     * Get available payment methods for a restaurant.
     */
    public function getAvailableMethods(Restaurant $restaurant): array
    {
        $methods = [];

        if (!empty($restaurant->wave_merchant_id)) {
            $methods[] = 'wave';
        }
        if (!empty($restaurant->orange_money_number)) {
            $methods[] = 'orange';
        }
        if (!empty($restaurant->mtn_money_number)) {
            $methods[] = 'mtn';
        }
        if (!empty($restaurant->moov_money_number)) {
            $methods[] = 'moov';
        }

        return $methods;
    }

    /**
     * Generate Wave deep link for payment.
     */
    public function getWaveDeepLink(Restaurant $restaurant, Order $order): string
    {
        $merchantId = $restaurant->wave_merchant_id;
        $amount = $order->total;
        $reference = $order->reference;

        // Wave deep link format (to be verified with Wave docs)
        return "wave://pay?merchant_id={$merchantId}&amount={$amount}&reference=" . urlencode($reference);
    }

    /**
     * Get USSD code for Orange Money.
     */
    public function getOrangeUssdCode(Restaurant $restaurant, Order $order): string
    {
        $number = preg_replace('/\D/', '', $restaurant->orange_money_number);
        $amount = $order->total;

        return "#144*5*{$number}*{$amount}#";
    }

    /**
     * Get USSD code for MTN MoMo.
     */
    public function getMtnUssdCode(Restaurant $restaurant, Order $order): string
    {
        $number = preg_replace('/\D/', '', $restaurant->mtn_money_number);
        $amount = $order->total;

        return "#111*1*{$number}*{$amount}#";
    }

    /**
     * Get USSD code for Moov Money (Côte d'Ivoire : *155*1*1# puis numéro + montant).
     * Format court affiché : *155*1*1# (l'utilisateur entre numéro et montant quand demandé).
     */
    public function getMoovUssdCode(Restaurant $restaurant, Order $order): string
    {
        return '*155*1*1#';
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }

    private function phonesMatch(string $phone1, string $phone2): bool
    {
        $n1 = $this->normalizePhone($phone1);
        $n2 = $this->normalizePhone($phone2);

        return $n1 === $n2 || str_ends_with($n1, $n2) || str_ends_with($n2, $n1);
    }
}

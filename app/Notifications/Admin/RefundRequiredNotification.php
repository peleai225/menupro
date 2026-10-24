<?php

namespace App\Notifications\Admin;

use App\Models\Order;
use App\Notifications\Channels\PushChannel;
use Illuminate\Notifications\Notification;

/**
 * Commande payée en ligne refusée par le restaurant : le client doit être remboursé
 * (manuellement, pas de remboursement automatique). orders.refund_status = 'pending'.
 */
class RefundRequiredNotification extends Notification
{
    public function __construct(
        protected Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => "💸 Remboursement à effectuer : {$this->amount()} F",
            'body'  => "Commande #{$this->order->reference} refusée par {$this->restaurantName()} ({$this->order->cancellation_reason}).",
            'data'  => [
                'type'     => 'refund_required',
                'order_id' => (string) $this->order->id,
            ],
            'link'                => route('super-admin.orders.show', $this->order->id),
            'tag'                 => 'refund-' . $this->order->id,
            'require_interaction' => true,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'refund_required',
            'order_id'        => $this->order->id,
            'order_reference' => $this->order->reference,
            'restaurant_id'   => $this->order->restaurant_id,
            'restaurant_name' => $this->restaurantName(),
            'amount'          => $this->order->total,
            'payment_method'  => $this->order->payment_method,
            'customer_phone'  => $this->order->customer_phone,
            'reason'          => $this->order->cancellation_reason,
            'message'         => "Remboursement à effectuer : {$this->amount()} F (commande #{$this->order->reference} refusée par {$this->restaurantName()})",
            'url'             => route('super-admin.orders.show', $this->order->id),
        ];
    }

    private function amount(): string
    {
        return number_format($this->order->total, 0, ',', ' ');
    }

    private function restaurantName(): string
    {
        return $this->order->restaurant?->name ?? 'le restaurant';
    }
}

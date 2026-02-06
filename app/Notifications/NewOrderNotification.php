<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        return (new MailMessage)
            ->subject("🍽️ Nouvelle commande #{$order->reference}")
            ->greeting("Nouvelle commande !")
            ->line("Vous avez reçu une nouvelle commande de **{$order->customer_name}**.")
            ->line("**Référence :** {$order->reference}")
            ->line("**Montant :** {$order->formatted_total}")
            ->line("**Type :** {$order->type->label()}")
            ->line("**Articles :** {$order->items_count}")
            ->action('Voir la commande', route('restaurant.orders.show', $order))
            ->salutation('MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'customer_name' => $this->order->customer_name,
            'total' => $this->order->total,
            'items_count' => $this->order->items_count,
            'order_type' => $this->order->type->value,
            'message' => "Nouvelle commande #{$this->order->reference} de {$this->order->customer_name}.",
        ];
    }
}


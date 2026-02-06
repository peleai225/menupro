<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderModifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected string $message
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        return (new MailMessage)
            ->subject("✏️ Commande modifiée #{$order->reference}")
            ->greeting("Commande modifiée")
            ->line($this->message)
            ->line("**Référence :** {$order->reference}")
            ->line("**Client :** {$order->customer_name}")
            ->line("**Nouveau total :** {$order->formatted_total}")
            ->action('Voir la commande', route('restaurant.orders.show', $order))
            ->line('Veuillez vérifier les modifications et ajuster la préparation si nécessaire.')
            ->salutation('MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_modified',
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'customer_name' => $this->order->customer_name,
            'message' => $this->message,
            'total' => $this->order->total,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Ingredient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RealTimeLowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Ingredient $ingredient,
        protected bool $sendEmail = false
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendEmail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠️ Stock bas : {$this->ingredient->name}")
            ->line("L'ingrédient **{$this->ingredient->name}** vient de passer sous le seuil minimum.")
            ->line("Quantité actuelle : {$this->ingredient->current_quantity} {$this->ingredient->unit?->value}")
            ->line("Seuil minimum : {$this->ingredient->min_quantity} {$this->ingredient->unit?->value}")
            ->action('Voir le stock', url('/dashboard/stock'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'             => 'real_time_low_stock',
            'ingredient_id'    => $this->ingredient->id,
            'ingredient_name'  => $this->ingredient->name,
            'current_quantity' => (float) $this->ingredient->current_quantity,
            'min_quantity'     => (float) $this->ingredient->min_quantity,
            'unit'             => $this->ingredient->unit?->value,
            'restaurant_id'    => $this->ingredient->restaurant_id,
            'message'          => "Stock bas : {$this->ingredient->name} ({$this->ingredient->current_quantity} {$this->ingredient->unit?->value} restant)",
        ];
    }
}

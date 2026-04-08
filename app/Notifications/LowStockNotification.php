<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Restaurant $restaurant,
        protected Collection $ingredients
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->ingredients->count();
        $plural = $count > 1 ? 's' : '';

        return (new MailMessage)
            ->subject("⚠️ {$count} ingrédient{$plural} en stock bas")
            ->view('emails.low-stock', [
                'restaurant' => $this->restaurant,
                'ingredients' => $this->ingredients,
                'notifiable' => $notifiable,
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'restaurant_id' => $this->restaurant->id,
            'ingredients_count' => $this->ingredients->count(),
            'ingredients' => $this->ingredients->take(5)->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'quantity' => $i->current_quantity,
                'unit' => $i->unit->value,
            ])->toArray(),
            'message' => "{$this->ingredients->count()} ingrédient(s) en stock bas.",
        ];
    }
}


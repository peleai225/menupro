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

        $message = (new MailMessage)
            ->subject("⚠️ {$count} ingrédient{$plural} en stock bas")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Attention ! **{$count} ingrédient{$plural}** de votre restaurant **{$this->restaurant->name}** nécessite{$plural} un réapprovisionnement :");

        // List first 5 ingredients
        $ingredientsList = $this->ingredients->take(5)->map(function ($ingredient) {
            return "- **{$ingredient->name}** : {$ingredient->formatted_quantity} (seuil : {$ingredient->min_quantity} {$ingredient->unit->shortLabel()})";
        })->implode("\n");

        $message->line($ingredientsList);

        if ($count > 5) {
            $message->line("... et " . ($count - 5) . " autre(s).");
        }

        return $message
            ->action('Voir le stock', route('restaurant.stock'))
            ->line('N\'oubliez pas de réapprovisionner pour éviter les ruptures !')
            ->salutation('L\'équipe MenuPro');
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


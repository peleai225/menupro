<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Review $review
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $review = $this->review;
        $restaurant = $review->restaurant;

        $stars = str_repeat('⭐', $review->rating) . str_repeat('☆', 5 - $review->rating);

        return (new MailMessage)
            ->subject("⭐ Nouvel avis client - {$review->rating}/5 étoiles")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Vous avez reçu un nouvel avis pour **{$restaurant->name}**.")
            ->line("**Client :** {$review->customer_name}")
            ->line("**Note :** {$stars} ({$review->rating}/5)")
            ->when($review->comment, function ($mail) use ($review) {
                return $mail->line("**Commentaire :**")
                    ->line($review->comment);
            })
            ->line('Cet avis est en attente de modération.')
            ->action('Modérer les avis', route('restaurant.reviews'))
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_review',
            'review_id' => $this->review->id,
            'restaurant_id' => $this->review->restaurant_id,
            'customer_name' => $this->review->customer_name,
            'rating' => $this->review->rating,
            'has_comment' => !empty($this->review->comment),
            'message' => "Nouvel avis de {$this->review->customer_name} ({$this->review->rating}/5 étoiles).",
        ];
    }
}


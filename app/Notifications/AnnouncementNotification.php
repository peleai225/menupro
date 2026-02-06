<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnnouncementNotification extends Notification
{
    public function __construct(
        public Announcement $announcement
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $typeEmoji = match($this->announcement->type) {
            'warning' => '⚠️',
            'success' => '✅',
            'danger' => '🚨',
            default => 'ℹ️',
        };

        return (new MailMessage)
            ->subject($typeEmoji . ' ' . $this->announcement->title . ' - MenuPro')
            ->greeting('Bonjour ' . $notifiable->name . ' !')
            ->line($this->announcement->content)
            ->action('Accéder à mon dashboard', route('restaurant.dashboard'))
            ->line('Merci de votre confiance.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'announcement_id' => $this->announcement->id,
            'title' => $this->announcement->title,
            'content' => $this->announcement->content,
            'type' => $this->announcement->type,
        ];
    }
}

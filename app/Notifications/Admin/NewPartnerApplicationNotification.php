<?php

namespace App\Notifications\Admin;

use App\Models\PartnerApplication;
use App\Notifications\Channels\PushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Nouvelle candidature « Représentant de ville » : cloche du back-office + push.
 */
class NewPartnerApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected PartnerApplication $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => '🤝 Nouvelle candidature représentant',
            'body'  => "{$this->application->full_name} ({$this->application->city}) souhaite devenir représentant MenuPro.",
            'data'  => [
                'type'           => 'partner_application',
                'application_id' => (string) $this->application->id,
            ],
            'link' => route('super-admin.partner-applications.show', $this->application),
            'tag'  => 'partner-application-' . $this->application->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'partner_application',
            'application_id' => $this->application->id,
            'message' => "Candidature représentant : {$this->application->full_name} ({$this->application->city})",
            'url' => route('super-admin.partner-applications.show', $this->application),
        ];
    }
}

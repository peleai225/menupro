<?php

namespace App\Jobs;

use App\Notifications\Channels\PushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Envoi d'un push (PushChannel) en arrière-plan, pour les notifications qui ne sont pas
 * elles-mêmes en file : l'appel à Firebase ne doit pas ralentir la requête de l'utilisateur.
 */
class SendPushMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Pas de nouvel essai : un envoi partiellement réussi ne doit pas sonner deux fois
    public int $tries = 1;

    public bool $deleteWhenMissingModels = true;

    public function __construct(public Model $notifiable, public array $message) {}

    public function handle(PushChannel $channel): void
    {
        $channel->deliver($this->notifiable, $this->message);
    }
}

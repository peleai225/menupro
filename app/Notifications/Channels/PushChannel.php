<?php

namespace App\Notifications\Channels;

use App\Jobs\SendPushMessage;
use App\Models\Customer;
use App\Models\DeliveryDriver;
use App\Models\PushToken;
use App\Models\User;
use App\Services\FcmService;
use App\Support\PushBadge;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Canal de notification « push » : envoie la notification sur TOUS les appareils du destinataire.
 *
 *   public function via($notifiable): array { return ['database', PushChannel::class]; }
 *
 *   public function toPush($notifiable): ?array
 *   {
 *       return [
 *           'title'               => '…',
 *           'body'                => '…',
 *           'data'                => ['type' => '…'],     // optionnel
 *           'link'                => route('…'),          // optionnel, https uniquement
 *           'tag'                 => 'reservation-12',    // optionnel : remplace la notif précédente
 *           'require_interaction' => true,                // optionnel : reste affichée
 *           'ttl'                 => 3600,                // optionnel
 *       ];
 *   }
 *
 * Appareils : User → push_tokens ; DeliveryDriver → fcm_token ; Customer → fcm_token + push_tokens
 * de son compte. data.badge est ajouté automatiquement (voir PushBadge).
 * Silencieux si Firebase n'est pas configuré ou si le destinataire n'a aucun appareil.
 */
class PushChannel
{
    public function __construct(private FcmService $fcm) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toPush')) {
            return;
        }

        $message = $notification->toPush($notifiable);
        if (!is_array($message) || empty($message['title'])) {
            return;
        }

        if (!$this->fcm->isConfigured() || !self::tokensFor($notifiable)) {
            return;
        }

        // Notification envoyée en direct (requête HTTP, webhook…) : l'appel à Firebase part en file
        // pour ne pas ralentir la requête. Déjà en file : on envoie tout de suite.
        if (!$notification instanceof ShouldQueue && $notifiable instanceof Model) {
            SendPushMessage::dispatch($notifiable, $message)->afterCommit();
            return;
        }

        $this->deliver($notifiable, $message);
    }

    /**
     * @return array{success: int, failure: int}
     */
    public function deliver(object $notifiable, array $message): array
    {
        $tokens = self::tokensFor($notifiable);
        if (!$tokens || !$this->fcm->isConfigured()) {
            return ['success' => 0, 'failure' => 0];
        }

        $data = $message['data'] ?? [];
        $data['badge'] ??= PushBadge::for($notifiable);

        try {
            return $this->fcm->sendToMultiple(
                $tokens,
                (string) $message['title'],
                (string) ($message['body'] ?? ''),
                $data,
                array_filter([
                    'link'                => $message['link'] ?? null,
                    'tag'                 => $message['tag'] ?? null,
                    'require_interaction' => $message['require_interaction'] ?? false,
                    'icon'                => $message['icon'] ?? self::defaultIcon($notifiable),
                    'ttl'                 => $message['ttl'] ?? null,
                ], fn ($v) => $v !== null && $v !== false),
            );
        } catch (\Throwable $e) {
            Log::warning('[Push] envoi impossible', [
                'notifiable' => $notifiable::class . '#' . ($notifiable->id ?? '?'),
                'error'      => $e->getMessage(),
            ]);
            return ['success' => 0, 'failure' => count($tokens)];
        }
    }

    /**
     * Tous les tokens FCM connus pour ce destinataire.
     */
    public static function tokensFor(object $notifiable): array
    {
        $tokens = match (true) {
            $notifiable instanceof User => PushToken::where('user_id', $notifiable->id)->pluck('token')->all(),

            $notifiable instanceof DeliveryDriver => [$notifiable->fcm_token],

            $notifiable instanceof Customer => [
                $notifiable->fcm_token,
                ...($notifiable->user_id
                    ? PushToken::where('user_id', $notifiable->user_id)->pluck('token')->all()
                    : []),
            ],

            default => [],
        };

        return array_values(array_unique(array_filter($tokens)));
    }

    private static function defaultIcon(object $notifiable): string
    {
        // L'app livreur (driver.menupro.ci) n'a pas d'icon-192.png
        return $notifiable instanceof DeliveryDriver ? '/logo.png' : '/icon-192.png';
    }
}

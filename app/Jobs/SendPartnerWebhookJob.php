<?php

namespace App\Jobs;

use App\Models\DeliveryPartner;
use App\Models\DeliveryPartnerWebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Webhook sortant vers un partenaire : POST JSON signé.
 *
 *   X-MenuPro-Signature : HMAC-SHA256 (hex) du corps brut avec le secret du partenaire
 *   X-MenuPro-Event     : nom de l'événement (parcel.status_changed, ping…)
 *
 * Chaque tentative est journalisée (delivery_partner_webhook_logs). En cas d'échec
 * (réponse non 2xx ou erreur réseau), la tentative suivante est replanifiée avec un
 * délai croissant (BACKOFF) jusqu'à MAX_ATTEMPTS. Le job ne lève jamais d'exception :
 * un partenaire injoignable ne doit pas bloquer la course.
 */
class SendPartnerWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public const MAX_ATTEMPTS = 6;

    /** Délai (secondes) avant la tentative n+1. */
    public const BACKOFF = [30, 120, 600, 1800, 3600];

    public int $tries = 1;

    public function __construct(
        public int $partnerId,
        public ?int $parcelId,
        public string $event,
        public array $payload,
        public int $attempt = 1,
    ) {}

    public function handle(): void
    {
        $partner = DeliveryPartner::find($this->partnerId);

        if (!$partner || !$partner->webhook_url) {
            return;
        }

        $body = json_encode($this->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $log = [
            'delivery_partner_id' => $partner->id,
            'parcel_id' => $this->parcelId,
            'event' => $this->event,
            'url' => $partner->webhook_url,
            'payload' => $this->payload,
            'attempt' => $this->attempt,
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-MenuPro-Signature' => $partner->sign($body),
                'X-MenuPro-Event' => $this->event,
                'X-MenuPro-Attempt' => (string) $this->attempt,
                'User-Agent' => 'MenuPro-Webhooks/1.0',
            ])
                ->timeout(10)
                ->withBody($body, 'application/json')
                ->post($partner->webhook_url);

            $ok = $response->successful();

            DeliveryPartnerWebhookLog::create($log + [
                'success' => $ok,
                'status_code' => $response->status(),
                'response_excerpt' => mb_substr((string) $response->body(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            $ok = false;
            DeliveryPartnerWebhookLog::create($log + [
                'success' => false,
                'error' => mb_substr($e->getMessage(), 0, 500),
            ]);
        }

        if ($ok) {
            return;
        }

        if ($this->attempt >= self::MAX_ATTEMPTS) {
            Log::warning('Webhook partenaire abandonné après ' . self::MAX_ATTEMPTS . ' tentatives', [
                'partner_id' => $partner->id,
                'parcel_id' => $this->parcelId,
                'event' => $this->event,
            ]);

            return;
        }

        $delay = self::BACKOFF[min($this->attempt - 1, count(self::BACKOFF) - 1)];

        self::dispatch($this->partnerId, $this->parcelId, $this->event, $this->payload, $this->attempt + 1)
            ->delay(now()->addSeconds($delay));
    }
}

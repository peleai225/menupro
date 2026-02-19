<?php

namespace App\Services;

use App\Models\CommandoAgent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Envoyer le message de bienvenue à un agent avec le lien pour définir son mot de passe.
     * Si configuré, envoie via l'API WhatsApp ; sinon log et retourne false.
     */
    public function sendAgentWelcome(CommandoAgent $agent, string $welcomeUrl, string $loginEmail): bool
    {
        if (!config('services.whatsapp.enabled') || !config('services.whatsapp.api_url')) {
            Log::channel('stack')->info('WhatsApp non configuré : message bienvenue agent non envoyé', [
                'agent_id' => $agent->id,
                'whatsapp' => $agent->whatsapp,
            ]);
            return false;
        }

        $phone = $this->normalizePhone($agent->whatsapp);
        $message = "Bienvenue sur MenuPro Commando ! Votre compte a été validé. "
            . "Définissez votre mot de passe ici : {$welcomeUrl} "
            . "Puis connectez-vous avec l'email : {$loginEmail}";

        try {
            $response = Http::withToken(config('services.whatsapp.api_key'))
                ->post(config('services.whatsapp.api_url'), [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'text',
                    'text' => ['body' => $message],
                ]);

            if (!$response->successful()) {
                Log::channel('stack')->warning('WhatsApp send failed', [
                    'agent_id' => $agent->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::channel('stack')->error('WhatsApp exception: ' . $e->getMessage(), [
                'agent_id' => $agent->id,
            ]);
            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '225')) {
            return $phone;
        }
        if (str_starts_with($phone, '0')) {
            return '225' . substr($phone, 1);
        }
        return '225' . $phone;
    }
}

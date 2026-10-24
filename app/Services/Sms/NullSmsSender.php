<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Log;

/**
 * Pas de fournisseur SMS configuré : le message est seulement journalisé.
 */
class NullSmsSender implements SmsSender
{
    public function send(string $phone, string $message): bool
    {
        Log::info('[SMS] aucun fournisseur configuré — SMS non envoyé', [
            'phone'   => $phone,
            'preview' => mb_substr($message, 0, 120),
        ]);

        return false;
    }
}

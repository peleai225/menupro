<?php

namespace App\Services;

use App\Models\CommandoAgent;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service WhatsApp — Notifications clients et agents via WhatsApp Business API.
 *
 * Canaux couverts :
 *  - Confirmation de commande client
 *  - Mise à jour statut commande client
 *  - Commande prête (client)
 *  - Nouvelle commande (restaurateur)
 *  - Bienvenue agent Commando
 */
class WhatsAppService
{
    /* ─── Commando Agent ──────────────────────────────────────────────────── */

    /**
     * Message de bienvenue à un agent avec son lien de création de mot de passe.
     */
    public function sendAgentWelcome(CommandoAgent $agent, string $welcomeUrl, string $loginEmail): bool
    {
        $message = "🎉 *Bienvenue sur MenuPro Commando !*\n\n"
            . "Votre compte agent a été validé ✅\n\n"
            . "📱 Définissez votre mot de passe ici :\n{$welcomeUrl}\n\n"
            . "✉️ Connectez-vous ensuite avec :\n{$loginEmail}\n\n"
            . "_L'équipe MenuPro vous souhaite la bienvenue !_ 🚀";

        return $this->send($agent->whatsapp, $message);
    }

    /* ─── Client — Cycle de vie commande ─────────────────────────────────── */

    /**
     * Envoyer la confirmation de commande au client dès le paiement.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        if (empty($order->customer_phone)) {
            return false;
        }

        $trackingUrl = route('r.order.status', [
            $order->restaurant->slug,
            $order->tracking_token,
        ]);

        $typeLabel = match ($order->type->value) {
            'dine_in'  => "🍽️ Sur place (Table {$order->table_number})",
            'takeaway' => '🛍️ À emporter',
            'delivery' => '🚚 Livraison',
            default    => ucfirst($order->type->value),
        };

        $items = $order->items->map(fn ($i) => "  • {$i->dish_name} x{$i->quantity}")->implode("\n");

        $message = "✅ *Commande confirmée !*\n\n"
            . "Merci *{$order->customer_name}* 🙏\n\n"
            . "📋 *Réf :* #{$order->reference}\n"
            . "📦 *Type :* {$typeLabel}\n"
            . "⏱️ *Préparation :* ~{$order->estimated_prep_time} min\n\n"
            . "*Votre commande :*\n{$items}\n\n"
            . "💰 *Total :* " . number_format($order->total, 0, ',', ' ') . " F CFA\n\n"
            . "🔗 Suivez votre commande en temps réel :\n{$trackingUrl}";

        return $this->send($order->customer_phone, $message);
    }

    /**
     * Notifier le client d'un changement de statut de commande.
     * Appelé depuis les controllers / jobs au moment de la transition.
     */
    public function sendOrderStatusUpdate(Order $order): bool
    {
        if (empty($order->customer_phone)) {
            return false;
        }

        $message = match ($order->status->value) {
            'confirmed' => "✅ *Commande #{$order->reference} acceptée !*\n\n"
                . "Votre commande a été validée par *{$order->restaurant->name}*.\n"
                . "Préparation en cours... ⏳",

            'preparing' => "👨‍🍳 *En préparation !*\n\n"
                . "Commande #{$order->reference} — *~{$order->estimated_prep_time} minutes* de cuisson.",

            'ready' => "🔔 *Votre commande est prête !*\n\n"
                . "#{$order->reference} vous attend. Bon appétit ! 🍴",

            'delivering' => "🚚 *Votre commande est en chemin !*\n\n"
                . "#{$order->reference} est en cours de livraison. Restez proche 📍",

            'completed' => "⭐ *Merci pour votre confiance !*\n\n"
                . "Nous espérons que vous avez apprécié votre repas 😊\n"
                . "Laissez-nous un avis sur *{$order->restaurant->name}* !",

            'cancelled' => "❌ *Commande #{$order->reference} annulée*\n\n"
                . "Contactez le restaurant directement si vous avez des questions.",

            default => null,
        };

        if (!$message) {
            return false;
        }

        return $this->send($order->customer_phone, $message);
    }

    /**
     * Notification dédiée "commande prête" (raccourci pratique).
     */
    public function sendOrderReady(Order $order): bool
    {
        if (empty($order->customer_phone)) {
            return false;
        }

        $context = match ($order->type->value) {
            'dine_in'  => 'servie à votre table',
            'takeaway' => 'prête au comptoir — venez la récupérer',
            'delivery' => 'en route vers vous',
            default    => 'prête',
        };

        $message = "🔔 *Votre commande est {$context} !*\n\n"
            . "#{$order->reference} · {$order->restaurant->name}\n\n"
            . "Bon appétit ! 🍴";

        return $this->send($order->customer_phone, $message);
    }

    /* ─── Restaurateur — Alertes opérationnelles ──────────────────────────── */

    /**
     * Alerter le restaurateur d'une nouvelle commande entrant.
     * (Complément au canal mail/database déjà existant)
     */
    public function sendNewOrderToRestaurant(Order $order, string $restaurantPhone): bool
    {
        $typeLabel = match ($order->type->value) {
            'dine_in'  => "Sur place — Table {$order->table_number}",
            'takeaway' => 'À emporter',
            'delivery' => "Livraison → {$order->delivery_city}",
            default    => ucfirst($order->type->value),
        };

        $message = "🍽️ *Nouvelle commande !*\n\n"
            . "📋 *Réf :* #{$order->reference}\n"
            . "👤 *Client :* {$order->customer_name}\n"
            . "📞 *Tél :* {$order->customer_phone}\n"
            . "📦 *Type :* {$typeLabel}\n"
            . "🔢 *Articles :* {$order->items_count}\n"
            . "💰 *Total :* " . number_format($order->total, 0, ',', ' ') . " F CFA\n\n"
            . "⚡ Ouvrez votre dashboard MenuPro pour traiter cette commande.";

        return $this->send($restaurantPhone, $message);
    }

    /**
     * Alerter le restaurateur d'un stock critique.
     */
    public function sendLowStockAlert(string $restaurantPhone, string $ingredientName, int $quantity, string $unit): bool
    {
        $message = "⚠️ *Alerte stock faible — MenuPro*\n\n"
            . "L'ingrédient *{$ingredientName}* est presque épuisé.\n"
            . "Quantité restante : *{$quantity} {$unit}*\n\n"
            . "Pensez à réapprovisionner avant d'ouvrir !";

        return $this->send($restaurantPhone, $message);
    }

    /* ─── OTP ────────────────────────────────────────────────────────────── */

    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "🔐 *MenuPro* — Code de vérification : *{$otp}*\n\nCe code expire dans 10 minutes. Ne le partagez à personne.";
        return $this->send($phone, $message);
    }

    /* ─── Core : envoi HTTP ───────────────────────────────────────────────── */

    /**
     * Envoyer un message WhatsApp à un numéro donné.
     * Utilise Twilio si configuré, sinon Meta Graph API.
     */
    public function send(string $phone, string $message): bool
    {
        $normalizedPhone = $this->normalizePhone($phone);

        // Twilio en priorité si configuré (DB > .env)
        $twilioSid   = SystemSetting::get('twilio_sid', config('twilio.sid', ''));
        $twilioToken = SystemSetting::get('twilio_auth_token', config('twilio.token', ''));
        $twilioFrom  = SystemSetting::get('twilio_whatsapp_from', config('twilio.whatsapp_from', 'whatsapp:+14155238886'));

        if ($twilioSid && $twilioToken) {
            return $this->sendViaTwilio($normalizedPhone, $message, $twilioSid, $twilioToken, $twilioFrom);
        }

        // Fallback : Meta Graph API
        $enabled = SystemSetting::get('whatsapp_enabled', config('services.whatsapp.enabled', false));
        $phoneId = SystemSetting::get('whatsapp_phone_id', config('services.whatsapp.phone_id', ''));
        $apiKey  = SystemSetting::get('whatsapp_api_key', config('services.whatsapp.api_key', ''));

        if (!$enabled || !$phoneId || !$apiKey) {
            Log::channel('stack')->info('WhatsApp non configuré — message non envoyé', [
                'phone'   => $phone,
                'preview' => mb_substr($message, 0, 80),
            ]);
            return false;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->post("https://graph.facebook.com/v21.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $normalizedPhone,
                    'type'              => 'text',
                    'text'              => ['body' => $message, 'preview_url' => false],
                ]);

            if (!$response->successful()) {
                Log::channel('stack')->warning('WhatsApp Meta — envoi échoué', [
                    'phone'  => $normalizedPhone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::channel('stack')->error('WhatsApp Meta — exception : ' . $e->getMessage());
            return false;
        }
    }

    private function sendViaTwilio(string $phone, string $message, string $sid, string $token, string $from): bool
    {
        try {
            $response = Http::withBasicAuth($sid, $token)
                ->timeout(15)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To'   => 'whatsapp:+' . $phone,
                    'From' => $from,
                    'Body' => $message,
                ]);

            if (!$response->successful()) {
                Log::channel('stack')->warning('WhatsApp Twilio — envoi échoué', [
                    'phone'  => $phone,
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            Log::channel('stack')->info('WhatsApp Twilio — message envoyé', ['phone' => $phone]);
            return true;
        } catch (\Throwable $e) {
            Log::channel('stack')->error('WhatsApp Twilio — exception : ' . $e->getMessage());
            return false;
        }
    }

    /* ─── Helpers ─────────────────────────────────────────────────────────── */

    /**
     * Normalise un numéro en format E.164 sans le "+" (requis par WhatsApp Business API).
     *
     * Formats acceptés :
     *  - +2250501862640  → 2250501862640
     *  - 2250501862640   → 2250501862640
     *  - 00225XXXXXXXX   → 225XXXXXXXX
     *  - 05XXXXXXXX      → 225XXXXXXXX  (numéro ivoirien local)
     *  - 5XXXXXXXX       → 2255XXXXXXXX
     */
    private function normalizePhone(string $phone): string
    {
        // Supprimer tout ce qui n'est pas un chiffre
        $digits = preg_replace('/\D/', '', $phone);

        // Format 00225... → retirer les deux 0 de tête
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Déjà au format international (225...)
        if (str_starts_with($digits, '225') && strlen($digits) >= 11) {
            return $digits;
        }

        // Numéro ivoirien local débutant par 0 (ex: 05XXXXXXXX)
        if (str_starts_with($digits, '0') && strlen($digits) >= 9) {
            return '225' . substr($digits, 1);
        }

        // Numéro court sans indicatif
        return '225' . $digits;
    }
}

<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Subscription;

interface PaymentGatewayInterface
{
    /**
     * Vérifie si le gateway est configuré (API keys présentes).
     */
    public function isConfigured(): bool;

    /**
     * Configure le gateway en mode plateforme (paiements vers MenuPro).
     */
    public function forPlatform(): static;

    /**
     * Crée une demande de paiement (Pay-in).
     *
     * @param Order|Subscription $entity L'entité à payer
     * @param string $successUrl URL de retour succès
     * @param string $errorUrl URL de retour erreur
     * @return array ['success' => bool, 'payment_id' => string, 'payment_url' => string, 'error' => string]
     */
    public function createPayment(Order|Subscription $entity, string $successUrl, string $errorUrl): array;

    /**
     * Récupère le statut d'un paiement.
     *
     * @param string $paymentId ID du paiement côté gateway
     * @return array ['success' => bool, 'status' => string, 'data' => array, 'error' => string]
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Effectue un payout (Pay-out) vers un bénéficiaire.
     *
     * @param string $recipient Numéro mobile ou identifiant bénéficiaire
     * @param int $amount Montant en centimes
     * @param string $recipientName Nom du bénéficiaire (optionnel)
     * @param string $reason Raison du paiement (optionnel)
     * @param string $reference Référence unique de la transaction (optionnel)
     * @return array ['success' => bool, 'transfer_id' => string, 'status' => string, 'error' => string]
     */
    public function payout(string $recipient, int $amount, string $recipientName = '', string $reason = '', string $reference = ''): array;

    /**
     * Vérifie la signature HMAC d'un webhook.
     *
     * @param string $rawPayload Payload brut du webhook
     * @param string $signatureHeader Header de signature
     * @return bool True si signature valide
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool;
}

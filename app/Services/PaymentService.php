<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Enums\PaymentGateway;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Retourne une instance de gateway selon le nom.
     *
     * @throws InvalidArgumentException Si le gateway est inconnu
     */
    public static function gateway(string $gatewayName): PaymentGatewayInterface
    {
        $gateway = PaymentGateway::tryFrom($gatewayName);

        if (!$gateway) {
            throw new InvalidArgumentException("Gateway inconnu: {$gatewayName}");
        }

        return match($gateway) {
            PaymentGateway::WAVE => throw new InvalidArgumentException('WaveGateway pas encore adapté à l\'interface. Utilisez JekoGateway.'),
            PaymentGateway::JEKO => throw new InvalidArgumentException('Jeko pas encore implémenté'),
            PaymentGateway::CINETPAY => throw new InvalidArgumentException('CinetPay pas encore implémenté'),
            PaymentGateway::CASH => throw new InvalidArgumentException('Cash ne supporte pas les paiements en ligne'),
        };
    }

    /**
     * Vérifie si un gateway est disponible et configuré.
     */
    public static function isGatewayAvailable(string $gatewayName): bool
    {
        try {
            $gateway = self::gateway($gatewayName);
            return $gateway->isConfigured();
        } catch (\Exception $e) {
            return false;
        }
    }
}

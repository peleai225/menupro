<?php

namespace App\Enums;

enum ParcelStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case SEARCHING_DRIVER = 'searching_driver';
    case ASSIGNED = 'assigned';
    case PICKED_UP = 'picked_up';
    case DELIVERING = 'delivering';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'En attente de paiement',
            self::SEARCHING_DRIVER => 'Recherche d\'un livreur',
            self::ASSIGNED => 'Livreur en route vers le ramassage',
            self::PICKED_UP => 'Colis ramassé',
            self::DELIVERING => 'En cours de livraison',
            self::DELIVERED => 'Livré',
            self::CANCELLED => 'Annulé',
            self::FAILED => 'Échec de livraison',
        };
    }

    /**
     * Transitions autorisées (machine d'états).
     *  - assigned → searching_driver : le livreur se désiste avant le ramassage.
     *  - failed / delivered / cancelled : terminaux.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::PENDING_PAYMENT => [self::SEARCHING_DRIVER, self::CANCELLED],
            self::SEARCHING_DRIVER => [self::ASSIGNED, self::CANCELLED],
            self::ASSIGNED => [self::PICKED_UP, self::SEARCHING_DRIVER, self::CANCELLED],
            self::PICKED_UP => [self::DELIVERING, self::FAILED, self::CANCELLED],
            self::DELIVERING => [self::DELIVERED, self::FAILED, self::CANCELLED],
            self::DELIVERED, self::CANCELLED, self::FAILED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED, self::FAILED], true);
    }

    /** Course en cours pour un livreur (occupe le livreur). */
    public function isActiveForDriver(): bool
    {
        return in_array($this, [self::ASSIGNED, self::PICKED_UP, self::DELIVERING], true);
    }

    /** L'expéditeur peut encore annuler (avant le ramassage). */
    public function isCancellableBySender(): bool
    {
        return in_array($this, [self::PENDING_PAYMENT, self::SEARCHING_DRIVER, self::ASSIGNED], true);
    }

    /** @return array<int, string> */
    public static function driverActiveValues(): array
    {
        return [self::ASSIGNED->value, self::PICKED_UP->value, self::DELIVERING->value];
    }
}

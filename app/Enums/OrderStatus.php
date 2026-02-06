<?php

namespace App\Enums;

enum OrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case CONFIRMED = 'confirmed';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case DELIVERING = 'delivering';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING_PAYMENT => 'En attente de paiement',
            self::PAID => 'Payée',
            self::CONFIRMED => 'Confirmée',
            self::PREPARING => 'En préparation',
            self::READY => 'Prête',
            self::DELIVERING => 'En livraison',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
            self::REFUNDED => 'Remboursée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'neutral',
            self::PENDING_PAYMENT => 'warning',
            self::PAID => 'info',
            self::CONFIRMED => 'primary',
            self::PREPARING => 'primary',
            self::READY => 'success',
            self::DELIVERING => 'info',
            self::COMPLETED => 'success',
            self::CANCELLED => 'error',
            self::REFUNDED => 'neutral',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'pencil',
            self::PENDING_PAYMENT => 'clock',
            self::PAID => 'credit-card',
            self::CONFIRMED => 'check',
            self::PREPARING => 'fire',
            self::READY => 'check-circle',
            self::DELIVERING => 'truck',
            self::COMPLETED => 'flag',
            self::CANCELLED => 'x-circle',
            self::REFUNDED => 'arrow-uturn-left',
        };
    }

    public function canBeEdited(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING_PAYMENT]);
    }

    /**
     * Check if order can be modified by manager
     * Managers can modify orders until PREPARING status
     */
    public function canBeModifiedByManager(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::PENDING_PAYMENT,
            self::PAID,
            self::CONFIRMED,
            self::PREPARING,
        ]);
    }

    /**
     * Check if order can be modified by customer
     * Customers can modify orders until 5 minutes after payment OR before confirmation
     */
    public function canBeModifiedByCustomer(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::PENDING_PAYMENT,
            self::PAID, // Limited by time (5 minutes)
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::DRAFT, self::PENDING_PAYMENT, self::PAID, self::CONFIRMED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::PAID, self::CONFIRMED, self::PREPARING, self::READY, self::DELIVERING]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED, self::REFUNDED]);
    }

    /**
     * Get allowed next statuses
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::PENDING_PAYMENT, self::CANCELLED],
            self::PENDING_PAYMENT => [self::PAID, self::CANCELLED],
            self::PAID => [self::CONFIRMED, self::CANCELLED, self::REFUNDED],
            self::CONFIRMED => [self::PREPARING, self::CANCELLED],
            self::PREPARING => [self::READY],
            self::READY => [self::DELIVERING, self::COMPLETED],
            self::DELIVERING => [self::COMPLETED],
            self::COMPLETED => [self::REFUNDED],
            self::CANCELLED, self::REFUNDED => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}


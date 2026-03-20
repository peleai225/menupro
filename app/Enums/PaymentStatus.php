<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PENDING_VERIFICATION = 'pending_verification';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PENDING_VERIFICATION => 'En attente de vérification',
            self::PROCESSING => 'En cours',
            self::COMPLETED => 'Complété',
            self::FAILED => 'Échoué',
            self::REFUNDED => 'Remboursé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PENDING_VERIFICATION => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'error',
            self::REFUNDED => 'neutral',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isPendingVerification(): bool
    {
        return $this === self::PENDING_VERIFICATION;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}


<?php

namespace App\Services\Parcels;

use Illuminate\Http\JsonResponse;

/**
 * Refus métier lisible par l'utilisateur (message en français), avec un code d'erreur
 * stable pour les applications (voir docs/api-colis.md).
 */
class ParcelException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'parcel_error',
        public readonly int $status = 422,
        public readonly array $extra = [],
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
        ], $this->extra), $this->status);
    }
}

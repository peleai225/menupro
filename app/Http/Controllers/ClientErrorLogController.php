<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Journal léger des erreurs côté navigateur des pages de paiement (JS, fetch, envoi de
 * formulaire bloqué) : la prochaine « erreur sur mobile » sera visible dans
 * storage/logs/payments-*.log au lieu de rester muette. Sans CSRF (une page restée
 * ouverte des heures a un jeton périmé), limité en débit et en taille.
 */
class ClientErrorLogController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:500'],
            'line' => ['nullable', 'integer'],
            'page' => ['nullable', 'string', 'max:500'],
            'context' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        Log::channel('payments')->warning('Erreur côté navigateur (page de paiement)', [
            'type' => $data['type'],
            'message' => Str::limit((string) ($data['message'] ?? ''), 1000),
            'source' => $data['source'] ?? null,
            'line' => $data['line'] ?? null,
            'page' => $data['page'] ?? null,
            'context' => Str::limit(json_encode($data['context'] ?? []), 1000),
            'user_id' => $user?->id,
            'restaurant_id' => $user?->restaurant_id,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 200),
        ]);

        return response()->json(['ok' => true]);
    }
}

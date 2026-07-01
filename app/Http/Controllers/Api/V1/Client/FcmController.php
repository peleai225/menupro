<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FcmController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate(['fcm_token' => ['required', 'string', 'max:512']]);

        $customer = $request->user()->customer;

        if (! $customer) {
            return response()->json(['message' => 'Profil client introuvable.'], 404);
        }

        $customer->update(['fcm_token' => $request->fcm_token]);

        return response()->json(['message' => 'Token push enregistré.']);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->customer?->update(['fcm_token' => null]);

        return response()->json(['message' => 'Token supprimé.']);
    }
}

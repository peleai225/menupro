<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'               => 'required|string|max:100',
            'phone'              => 'required|string|max:20|unique:delivery_drivers,phone',
            'email'              => 'nullable|email|max:150|unique:users,email',
            'password'           => ['required', Password::min(6)],
            'city'               => 'required|string|max:100',
            'zone'               => 'nullable|string|max:100',
            'vehicle_type'       => 'required|in:moto,velo,voiture',
            'vehicle_plate'      => 'nullable|string|max:20',
            'cni_number'         => 'required|string|max:30',
            'cni_photo'          => 'required|file|image|max:5120',
            'license_photo'      => 'required|file|image|max:5120',
            'vehicle_photo'      => 'required|file|image|max:5120',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
        // 'role' est dans $guarded — assigner après create()
        $user->role = UserRole::DELIVERY_DRIVER;
        $user->save();

        $cniPath     = $request->file('cni_photo')->store('drivers/cni', 'public');
        $licensePath = $request->file('license_photo')->store('drivers/license', 'public');
        $vehiclePath = $request->file('vehicle_photo')->store('drivers/vehicle', 'public');

        DeliveryDriver::create([
            'user_id'             => $user->id,
            'name'                => $data['name'],
            'phone'               => $data['phone'],
            'email'               => $data['email'] ?? null,
            'city'                => $data['city'],
            'zone'                => $data['zone'] ?? null,
            'vehicle_type'        => $data['vehicle_type'],
            'vehicle_plate'       => $data['vehicle_plate'] ?? null,
            'token'               => Str::random(64),
            'cni_number'          => $data['cni_number'],
            'cni_photo_path'      => $cniPath,
            'license_photo_path'  => $licensePath,
            'vehicle_photo_path'  => $vehiclePath,
            'verification_status' => 'pending',
            'is_active'           => false,
            'is_available'        => false,
        ]);

        return response()->json([
            'message' => 'Inscription reçue. Votre dossier est en cours de vérification (24-48h).',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $driver = DeliveryDriver::where('phone', $data['phone'])->first();

        if (!$driver) {
            return response()->json(['message' => 'Compte introuvable.'], 404);
        }

        $user = $driver->user;

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 401);
        }

        if ($driver->verification_status === 'rejected') {
            return response()->json(['message' => 'Votre dossier a été refusé. Contactez le support.'], 403);
        }

        if ($driver->verification_status === 'suspended') {
            return response()->json(['message' => 'Votre compte est suspendu. Contactez le support.'], 403);
        }

        $user->tokens()->where('name', 'driver-app')->delete();
        $token = $user->createToken('driver-app')->plainTextToken;

        return response()->json([
            'token'  => $token,
            'driver' => $this->formatDriver($driver),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->formatDriver($request->user()->deliveryDriver));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        $data = $request->validate(['fcm_token' => 'required|string|max:255']);
        $request->user()->deliveryDriver->update(['fcm_token' => $data['fcm_token']]);
        return response()->json(['message' => 'Token FCM mis à jour.']);
    }

    private function formatDriver(DeliveryDriver $driver): array
    {
        return [
            'id'                  => $driver->id,
            'name'                => $driver->name,
            'phone'               => $driver->phone,
            'city'                => $driver->city,
            'zone'                => $driver->zone,
            'vehicle_type'        => $driver->vehicle_type,
            'vehicle_plate'       => $driver->vehicle_plate,
            'verification_status' => $driver->verification_status,
            'is_active'           => $driver->is_active,
            'is_available'        => $driver->is_available,
            'rating'              => $driver->rating !== null ? (float) $driver->rating : null,
            'total_deliveries'    => (int) ($driver->total_deliveries ?? 0),
            'total_earnings_xof'  => (int) ($driver->total_earnings_xof ?? 0),
        ];
    }
}

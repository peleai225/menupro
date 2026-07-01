<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'required|string|max:20|unique:customers,phone',
            'email'    => 'nullable|email|max:150|unique:users,email',
            'password' => ['required', Password::min(6)],
            'city'     => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'] ?? null,
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
            'role'     => UserRole::CUSTOMER->value,
        ]);

        $customer = Customer::create([
            'user_id' => $user->id,
            'phone'   => $data['phone'],
            'city'    => $data['city'] ?? null,
        ]);

        $token = $user->createToken('customer-app')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => $this->formatCustomer($user, $customer),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $customer = Customer::where('phone', $data['phone'])->first();

        if (!$customer) {
            return response()->json(['message' => 'Compte introuvable.'], 404);
        }

        $user = $customer->user;

        if (!Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Mot de passe incorrect.'], 401);
        }

        if (!$customer->is_active) {
            return response()->json(['message' => 'Compte suspendu. Contactez le support.'], 403);
        }

        $user->tokens()->where('name', 'customer-app')->delete();
        $token = $user->createToken('customer-app')->plainTextToken;

        return response()->json([
            'token'    => $token,
            'customer' => $this->formatCustomer($user, $customer),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user     = $request->user();
        $customer = $user->customer;

        return response()->json($this->formatCustomer($user, $customer));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'  => 'sometimes|string|max:100',
            'city'  => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:150|unique:users,email,' . $request->user()->id,
        ]);

        $user     = $request->user();
        $customer = $user->customer;

        if (isset($data['name']))  $user->update(['name' => $data['name']]);
        if (isset($data['email'])) $user->update(['email' => $data['email']]);
        if (isset($data['city']))  $customer->update(['city' => $data['city']]);

        return response()->json($this->formatCustomer($user->fresh(), $customer->fresh()));
    }

    private function formatCustomer(User $user, ?Customer $customer): array
    {
        return [
            'id'    => $customer?->id,
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $customer?->phone,
            'city'  => $customer?->city,
            'total_orders' => $customer?->total_orders ?? 0,
        ];
    }
}

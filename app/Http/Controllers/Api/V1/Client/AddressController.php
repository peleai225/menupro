<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->customer->addresses()->latest()->get();

        return response()->json(['data' => $addresses]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label'        => 'required|string|max:50',
            'address'      => 'required|string|max:300',
            'city'         => 'required|string|max:100',
            'zone'         => 'nullable|string|max:100',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'instructions' => 'nullable|string|max:300',
            'is_default'   => 'nullable|boolean',
        ]);

        $customer = $request->user()->customer;

        $address = DB::transaction(function () use ($customer, $data) {
            if (!empty($data['is_default'])) {
                $customer->addresses()->update(['is_default' => false]);
            }

            return $customer->addresses()->create($data);
        });

        return response()->json($address, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = $request->user()->customer;
        $address  = $customer->addresses()->findOrFail($id);

        $data = $request->validate([
            'label'        => 'sometimes|string|max:50',
            'address'      => 'sometimes|string|max:300',
            'city'         => 'sometimes|string|max:100',
            'zone'         => 'nullable|string|max:100',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'instructions' => 'nullable|string|max:300',
            'is_default'   => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($customer, $address, $data) {
            if (!empty($data['is_default'])) {
                $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            }
            $address->update($data);
        });

        return response()->json($address->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $customer = $request->user()->customer;
        $address  = $customer->addresses()->findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Adresse supprimée.']);
    }
}

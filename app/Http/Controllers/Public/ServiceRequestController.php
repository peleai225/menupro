<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function store(Request $request, string $slug): JsonResponse
    {
        $query = Restaurant::where('slug', $slug);
        if (app()->environment('production')) {
            $query->where('status', 'active');
        }
        $restaurant = $query->firstOrFail();

        $data = $request->validate([
            'table_number' => 'required|string|max:50',
            'type'         => 'required|in:cleaning,assistance,checkout,other',
            'notes'        => 'nullable|string|max:200',
        ]);

        ServiceRequest::create([
            'restaurant_id' => $restaurant->id,
            'table_number'  => $data['table_number'],
            'type'          => $data['type'],
            'notes'         => $data['notes'] ?? null,
            'status'        => 'pending',
        ]);

        return response()->json(['success' => true]);
    }
}

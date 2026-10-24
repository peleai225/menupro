<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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

        $serviceRequest = ServiceRequest::create([
            'restaurant_id' => $restaurant->id,
            'table_number'  => $data['table_number'],
            'type'          => $data['type'],
            'notes'         => $data['notes'] ?? null,
            'status'        => 'pending',
        ]);

        // Cloche + push sur les appareils du personnel (en file : la réponse au client reste immédiate)
        try {
            Notification::send($restaurant->staffToAlert(), new ServiceRequestNotification($serviceRequest));
        } catch (\Throwable $e) {
            // La demande est enregistrée : le tableau de bord la verra au prochain rafraîchissement
            \Illuminate\Support\Facades\Log::warning('Notification appel du personnel échouée', [
                'service_request_id' => $serviceRequest->id,
                'error'              => $e->getMessage(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KitchenController extends Controller
{
    /**
     * Generate or regenerate kitchen token (from settings).
     */
    public function generateToken(Request $request): JsonResponse
    {
        $restaurant = auth()->user()->restaurant;
        $restaurant->kitchen_token = Str::random(32);
        $restaurant->save();

        return response()->json([
            'token' => $restaurant->kitchen_token,
            'url' => route('kitchen.display', $restaurant->kitchen_token),
        ]);
    }

    /**
     * Kitchen display screen (no auth required, token-secured).
     */
    public function display(string $token): View
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        $orders = Order::withoutGlobalScope('restaurant')
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
            ])
            ->where('created_at', '>=', now()->subHours(24))
            ->with('items.dish.category')
            ->oldest()
            ->get();

        $ordersJson = $orders->map(fn($order) => $this->serializeOrder($order))->values();

        return view('pages.kitchen.display', compact('restaurant', 'ordersJson', 'token'));
    }

    /**
     * Proxy ElevenLabs TTS — la clé API ne sort jamais dans le HTML.
     */
    public function tts(string $token, Request $request): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
    {
        Restaurant::where('kitchen_token', $token)->firstOrFail();

        $apiKey  = \App\Models\SystemSetting::get('elevenlabs_api_key', '');
        $voiceId = \App\Models\SystemSetting::get('elevenlabs_voice_id', 'pNInz6obpgDQGcFmaJgB');

        if (empty($apiKey)) {
            return response('ElevenLabs non configuré', 503);
        }

        $text = $request->validate(['text' => ['required', 'string', 'max:500']])['text'];

        // ElevenLabs utilise xi-api-key, pas Authorization: Bearer
        $response = \Illuminate\Support\Facades\Http::withHeaders(['xi-api-key' => $apiKey])
            ->timeout(15)
            ->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => 'eleven_multilingual_v2',
                'voice_settings' => ['stability' => 0.5, 'similarity_boost' => 0.75],
            ]);

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::error('ElevenLabs TTS error', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'voice'  => $voiceId,
            ]);
            return response('TTS error: ' . $response->status(), 502);
        }

        return response($response->body(), 200, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Get orders data (AJAX polling from kitchen display).
     */
    public function data(string $token): JsonResponse
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        $orders = Order::withoutGlobalScope('restaurant')
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
            ])
            ->where('created_at', '>=', now()->subHours(24))
            ->with('items.dish.category')
            ->oldest()
            ->get()
            ->map(fn($order) => $this->serializeOrder($order));

        $serviceRequests = ServiceRequest::where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subHours(12))
            ->oldest()
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'table_number' => $r->table_number,
                'type'         => $r->type,
                'type_label'   => $r->typeLabel(),
                'type_icon'    => $r->typeIcon(),
                'notes'        => $r->notes,
                'minutes_ago'  => $r->created_at->diffInMinutes(now()),
            ]);

        return response()->json([
            'orders' => $orders,
            'counts' => [
                'new'           => $orders->whereIn('status', ['paid', 'confirmed'])->count(),
                'preparing'     => $orders->where('status', 'preparing')->count(),
                'ready'         => $orders->where('status', 'ready')->count(),
                'service'       => $serviceRequests->count(),
            ],
            'service_requests' => $serviceRequests,
        ]);
    }

    /**
     * Mark a service request as done.
     */
    public function serviceRequestDone(string $token, ServiceRequest $serviceRequest): JsonResponse
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        if ((int) $serviceRequest->restaurant_id !== (int) $restaurant->id) {
            abort(403);
        }

        $serviceRequest->update(['status' => 'done', 'done_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Update order status from kitchen display.
     */
    public function updateStatus(string $token, Order $order, Request $request): JsonResponse
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        // Recharge sans scope pour éviter le filtre BelongsToRestaurant (pas de session ici)
        $order = Order::withoutGlobalScope('restaurant')->findOrFail($order->id);

        if ((int) $order->restaurant_id !== (int) $restaurant->id) {
            abort(403);
        }

        $action = $request->input('action');

        $newStatus = match ($action) {
            'confirm' => OrderStatus::CONFIRMED,
            'prepare' => OrderStatus::PREPARING,
            'ready'   => OrderStatus::READY,
            default   => null,
        };

        if (!$newStatus) {
            return response()->json(['error' => 'Action invalide'], 400);
        }

        if (!$order->status->canTransitionTo($newStatus)) {
            return response()->json(['error' => 'Transition impossible'], 422);
        }

        // Déduire le stock lors de la confirmation (PAID → CONFIRMED), comme les autres contrôleurs
        if ($newStatus === OrderStatus::CONFIRMED
            && $order->status === OrderStatus::PAID
            && $restaurant->hasFeature('stock')
        ) {
            app(\App\Services\StockManager::class)
                ->forRestaurant($restaurant)
                ->deductForOrder($order);
        }

        $order->transitionTo($newStatus);

        return response()->json(['success' => true, 'new_status' => $newStatus->value]);
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'id'             => $order->id,
            'reference'      => $order->reference,
            'status'         => $order->status->value,
            'status_label'   => $order->status->label(),
            'customer_name'  => $order->customer_name,
            'customer_notes' => $order->customer_notes,
            'type'           => $order->type?->label() ?? '',
            'table_number'   => $order->table_number,
            'created_at'     => $order->created_at->format('H:i'),
            'minutes_ago'    => $order->created_at->diffInMinutes(now()),
            'ready_at'       => $order->ready_at?->format('H:i'),
            'items'          => $order->items->map(fn($item) => [
                'quantity'     => $item->quantity,
                'name'         => $item->dish?->name ?? $item->dish_name ?? 'Plat',
                'category'     => $item->dish?->category?->name ?? '',
                'photo'        => $item->dish?->image_path
                                    ? \Illuminate\Support\Facades\Storage::url($item->dish->image_path)
                                    : null,
                'options'      => $item->selected_options ?? [],
                'instructions' => $item->special_instructions,
            ])->values()->all(),
        ];
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderStatusController extends Controller
{
    /**
     * Display order status page (secured with tracking token).
     */
    public function show(string $slug, string $token): View|RedirectResponse
    {
        $slug = trim($slug);
        $token = trim($token);

        // 1) Slug exact (insensible à la casse)
        $restaurant = Restaurant::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->first();

        // 2) Fallback : slug sans tirets (pelefood <-> pele-food)
        if (!$restaurant) {
            $normalized = str_replace('-', '', strtolower($slug));
            if ($normalized !== '') {
                $restaurant = Restaurant::whereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [$normalized])->first();
                if ($restaurant) {
                    return redirect()->route('r.order.status', [$restaurant->slug, $token], 301);
                }
            }
        }

        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé. Vérifiez l’adresse du lien.');
        }

        // Try to find order by tracking token first
        $order = Order::where('tracking_token', $token)->first();

        // If not found, check if token is actually an ID (for backward compatibility)
        // and redirect to the proper token URL
        if (!$order && is_numeric($token)) {
            $orderById = Order::where('id', $token)
                ->where('restaurant_id', $restaurant->id)
                ->first();
            
            if ($orderById && $orderById->tracking_token) {
                return redirect()->route('r.order.status', [
                    $restaurant->slug,
                    $orderById->tracking_token
                ]);
            }
        }

        if (!$order) {
            abort(404, 'Commande introuvable ou lien expiré. Vérifiez le lien reçu par email ou SMS.');
        }

        // Verify order belongs to restaurant (cast to int to avoid type mismatch 2 !== "2")
        if ((int) $order->restaurant_id !== (int) $restaurant->id) {
            abort(404, 'Cette commande n’appartient pas à ce restaurant.');
        }

        $order->load('items.dish');

        // Calculate progress
        $progress = $this->calculateProgress($order);

        // Get available dishes for modification (if order can be modified)
        $availableDishes = collect();
        if ($order->canBeModifiedByCustomer()) {
            $availableDishes = \App\Models\Dish::where('restaurant_id', $order->restaurant_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('pages.restaurant-public.order-status', compact(
            'restaurant',
            'order',
            'progress',
            'availableDishes'
        ));
    }

    /**
     * Get order status (AJAX for polling) - secured with tracking token.
     */
    public function status(string $slug, string $token)
    {
        $slug = trim($slug);
        $token = trim($token);

        // Même résolution du restaurant que show() (slug exact + fallback sans tirets, insensible à la casse)
        $restaurant = Restaurant::whereRaw('LOWER(slug) = ?', [strtolower($slug)])->first();
        if (!$restaurant) {
            $normalized = str_replace('-', '', strtolower($slug));
            if ($normalized !== '') {
                $restaurant = Restaurant::whereRaw("LOWER(REPLACE(slug, '-', '')) = ?", [$normalized])->first();
            }
        }
        if (!$restaurant) {
            abort(404);
        }

        $order = Order::where('tracking_token', $token)->first();
        if (!$order || (int) $order->restaurant_id !== (int) $restaurant->id) {
            abort(404);
        }

        // Refresh order from database to get latest status
        $order->refresh();

        return response()->json([
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'status_color' => $order->status->color(),
            'payment_status' => $order->payment_status->value,
            'estimated_ready_at' => $order->estimated_ready_at?->toISOString(),
            'progress' => $this->calculateProgress($order),
            'is_final' => $order->is_final,
            'can_be_modified' => $order->canBeModifiedByCustomer(),
            'remaining_modification_time' => $order->remaining_modification_time,
        ]);
    }

    /**
     * Calculate order progress steps.
     */
    protected function calculateProgress(Order $order): array
    {
        $steps = [
            [
                'key' => 'placed',
                'label' => 'Commande passée',
                'completed' => true,
                'current' => false,
                'time' => $order->created_at,
            ],
            [
                'key' => 'paid',
                'label' => 'Paiement confirmé',
                'completed' => $order->is_paid,
                'current' => !$order->is_paid && !$order->is_final,
                'time' => $order->paid_at,
            ],
            [
                'key' => 'confirmed',
                'label' => 'Commande confirmée',
                'completed' => (bool) $order->confirmed_at,
                'current' => $order->is_paid && !$order->confirmed_at && !$order->is_final,
                'time' => $order->confirmed_at,
            ],
            [
                'key' => 'preparing',
                'label' => 'En préparation',
                'completed' => (bool) $order->preparing_at,
                'current' => (bool) $order->confirmed_at && !$order->preparing_at && !$order->is_final,
                'time' => $order->preparing_at,
            ],
            [
                'key' => 'ready',
                'label' => $order->type->requiresAddress() ? 'En livraison' : 'Prête',
                'completed' => (bool) $order->ready_at,
                'current' => (bool) $order->preparing_at && !$order->ready_at && !$order->is_final,
                'time' => $order->ready_at,
            ],
            [
                'key' => 'completed',
                'label' => $order->type->requiresAddress() ? 'Livrée' : 'Récupérée',
                'completed' => (bool) $order->completed_at,
                'current' => (bool) $order->ready_at && !$order->completed_at && !$order->is_final,
                'time' => $order->completed_at,
            ],
        ];

        // Handle cancelled/refunded
        if ($order->cancelled_at) {
            $steps[] = [
                'key' => 'cancelled',
                'label' => 'Annulée',
                'completed' => true,
                'current' => false,
                'time' => $order->cancelled_at,
                'error' => true,
            ];
        }

        return $steps;
    }
}


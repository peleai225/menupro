<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WaiterController extends Controller
{
    public function generateToken(): \Illuminate\Http\RedirectResponse
    {
        $restaurant = auth()->user()->restaurant;
        $restaurant->waiter_token = Str::random(32);
        $restaurant->save();

        return redirect()->route('restaurant.waiters')
            ->with('success', 'Lien interface serveur généré.');
    }

    /**
     * Waiter display screen (no auth required, token-secured).
     */
    public function display(string $token): View
    {
        $restaurant = Restaurant::where('waiter_token', $token)->firstOrFail();
        $spaces     = $restaurant->spaces()->active()->get(['id', 'name', 'color']);

        return view('pages.waiter.display', compact('restaurant', 'token', 'spaces'));
    }

    /**
     * Authenticate a waiter by PIN (AJAX, no auth required).
     */
    public function authenticatePin(Request $request, string $token): JsonResponse
    {
        $restaurant = Restaurant::where('waiter_token', $token)->firstOrFail();
        $request->validate(['pin' => 'required|digits:4']);

        $waiters = Waiter::where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->get();

        foreach ($waiters as $waiter) {
            if ($waiter->isLocked()) {
                continue;
            }
            if ($waiter->checkPin($request->pin)) {
                $waiter->resetAttempts();

                return response()->json([
                    'success'     => true,
                    'waiter_id'   => $waiter->id,
                    'waiter_name' => $waiter->name,
                    'space_id'    => $waiter->space_id,
                    'space_name'  => $waiter->space?->name,
                ]);
            }
        }

        // PIN incorrect — rate-limit by IP + restaurant via cache
        $key      = 'waiter_pin_fail_' . $request->ip() . '_' . $restaurant->id;
        $attempts = cache()->increment($key);
        cache()->put($key, $attempts, now()->addMinutes(5));

        if ($attempts >= 10) {
            $waiters->each->recordFailedAttempt();
        }

        return response()->json(['success' => false, 'message' => 'PIN incorrect'], 401);
    }

    /**
     * Get active orders for a waiter (AJAX polling, no auth required).
     */
    public function data(string $token): JsonResponse
    {
        $restaurant = Restaurant::where('waiter_token', $token)->firstOrFail();
        $waiterId   = request()->query('waiter_id');

        $orders = Order::withoutGlobalScope('restaurant')
            ->where('restaurant_id', $restaurant->id)
            ->when($waiterId, fn ($q) => $q->where('waiter_id', (int) $waiterId))
            ->whereIn('status', ['confirmed', 'preparing', 'ready'])
            ->with('items.dish')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($o) => [
                'id'           => $o->id,
                'reference'    => $o->reference,
                'table'        => $o->table_number,
                'status'       => $o->status->value,
                'status_label' => $o->status->label(),
                'items'        => $o->items->map(fn ($i) => ($i->dish?->name ?? 'Plat') . ' x' . $i->quantity)->join(', '),
                'total'        => number_format($o->total, 0, '.', ' ') . ' F',
                'created_at'   => $o->created_at->format('H:i'),
            ]);

        return response()->json(['orders' => $orders]);
    }
}

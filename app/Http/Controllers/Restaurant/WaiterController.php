<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

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
}

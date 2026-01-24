<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QRCodeController extends Controller
{
    /**
     * Display the QR code page for the restaurant
     */
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;
        
        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé');
        }
        
        // Generate the public URL for the restaurant
        $publicUrl = route('r.menu', ['slug' => $restaurant->slug]);
        
        return view('pages.restaurant.qrcode', [
            'restaurant' => $restaurant,
            'publicUrl' => $publicUrl,
        ]);
    }
}

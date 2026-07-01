<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\Customer;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PushNotificationController extends Controller
{
    public function __construct(private FcmService $fcm) {}

    public function index(): View
    {
        $stats = [
            'drivers_with_token'   => DeliveryDriver::whereNotNull('fcm_token')->where('verification_status', 'approved')->count(),
            'drivers_online'       => DeliveryDriver::where('is_active', true)->where('is_available', true)->where('verification_status', 'approved')->count(),
            'customers_with_token' => Customer::whereNotNull('fcm_token')->count(),
            'fcm_configured'       => $this->fcm->isConfigured(),
        ];

        return view('pages.super-admin.push-notifications.index', compact('stats'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'title'    => 'required|string|max:100',
            'body'     => 'required|string|max:500',
            'audience' => 'required|in:all_drivers,online_drivers,all_customers,all',
            'data_key' => 'nullable|string|max:50',
            'data_val' => 'nullable|string|max:255',
        ]);

        if (!$this->fcm->isConfigured()) {
            return back()->with('error', 'Firebase non configuré. Ajoutez la clé serveur dans Paramètres → Livraison & Cartes.');
        }

        $tokens = match ($request->audience) {
            'all_drivers'    => DeliveryDriver::whereNotNull('fcm_token')->where('verification_status', 'approved')->pluck('fcm_token')->toArray(),
            'online_drivers' => DeliveryDriver::whereNotNull('fcm_token')->where('is_active', true)->where('is_available', true)->where('verification_status', 'approved')->pluck('fcm_token')->toArray(),
            'all_customers'  => Customer::whereNotNull('fcm_token')->pluck('fcm_token')->toArray(),
            'all'            => array_merge(
                DeliveryDriver::whereNotNull('fcm_token')->pluck('fcm_token')->toArray(),
                Customer::whereNotNull('fcm_token')->pluck('fcm_token')->toArray(),
            ),
        };

        if (empty($tokens)) {
            return back()->with('error', 'Aucun token push disponible pour cette audience.');
        }

        $data = [];
        if ($request->filled('data_key') && $request->filled('data_val')) {
            $data[$request->data_key] = $request->data_val;
        }

        $result = $this->fcm->sendToMultiple($tokens, $request->title, $request->body, $data);

        return back()->with('success', "Envoyé à {$result['success']} appareil(s). Échecs : {$result['failure']}.");
    }
}

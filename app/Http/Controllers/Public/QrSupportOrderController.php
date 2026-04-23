<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class QrSupportOrderController extends Controller
{
    /**
     * Prix unitaires des supports QR (FCFA).
     */
    private const PRICES = [
        'support' => 1500, // Support rigide pose sur les tables
        'sticker' => 300,  // Autocollant plastifie
    ];

    /**
     * Handle a QR support order request (rigid stand or sticker).
     * Non-auth public endpoint : rate limited + validated + email to admin.
     */
    public function store(Request $request)
    {
        // Rate limiting : 3 commandes max par heure par IP
        $key = 'qr-support-order:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = max(1, (int) ceil($seconds / 60));
            return back()
                ->withInput()
                ->with('qr_error', "Trop de demandes. Reessayez dans {$minutes} minute(s).");
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'required|string|max:30',
            'email'    => 'nullable|email|max:255',
            'city'     => 'required|string|max:100',
            'address'  => 'nullable|string|max:500',
            'format'   => 'required|in:support,sticker',
            'quantity' => 'required|integer|min:1|max:999',
            'note'     => 'nullable|string|max:1000',
        ], [
            'name.required'     => 'Votre nom est requis.',
            'phone.required'    => 'Votre numero de telephone est requis.',
            'city.required'     => 'Votre ville est requise.',
            'format.required'   => 'Veuillez choisir un format (support rigide ou autocollant).',
            'format.in'         => 'Format de support invalide.',
            'quantity.required' => 'Veuillez indiquer une quantite.',
            'quantity.min'      => 'La quantite minimale est 1.',
            'quantity.max'      => 'La quantite maximale est 999.',
            'email.email'       => 'Email invalide.',
        ]);

        RateLimiter::hit($key, 3600); // 1h

        // Recalcul serveur des prix (jamais confiance au client)
        $unitPrice = self::PRICES[$validated['format']];
        $subtotal  = $unitPrice * $validated['quantity'];
        // Livraison : offerte des 20 unites, sinon 2000 FCFA Abidjan
        $delivery  = $validated['quantity'] >= 20 ? 0 : 2000;
        $total     = $subtotal + $delivery;

        $orderData = array_merge($validated, [
            'unit_price' => $unitPrice,
            'subtotal'   => $subtotal,
            'delivery'   => $delivery,
            'total'      => $total,
            'ref'        => strtoupper('QRS-' . substr(bin2hex(random_bytes(4)), 0, 8)),
            'date'       => now()->format('d/m/Y H:i'),
        ]);

        // Destinataire admin (fallback mail.from si pas configure)
        $contactEmail = SystemSetting::get(
            'contact_email',
            config('mail.from.address', 'contact@menupro.com')
        );

        try {
            Mail::send('emails.qr-support-order', ['order' => $orderData], function ($message) use ($orderData, $contactEmail, $validated) {
                $message->to($contactEmail)
                    ->subject('[MenuPro] Nouvelle commande supports QR - ' . $orderData['ref'] . ' (' . $orderData['quantity'] . ' x ' . ($validated['format'] === 'support' ? 'Support rigide' : 'Autocollant') . ')');

                if (!empty($validated['email'])) {
                    $message->replyTo($validated['email'], $validated['name']);
                }
            });

            return back()
                ->with('qr_success', "Merci {$validated['name']} ! Votre demande a ete recue (reference {$orderData['ref']}). Notre equipe vous contacte au {$validated['phone']} sous 24h ouvrees pour confirmer et organiser la livraison.");
        } catch (\Exception $e) {
            \Log::error('QR support order error: ' . $e->getMessage(), [
                'exception' => $e,
                'data'      => $validated,
            ]);

            return back()
                ->withInput()
                ->with('qr_error', "Une erreur est survenue lors de l'envoi de votre demande. Veuillez reessayer ou nous contacter directement.");
        }
    }
}

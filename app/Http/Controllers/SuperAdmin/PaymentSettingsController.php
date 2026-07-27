<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemPaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    public function index(): View
    {
        $settings = SystemPaymentSetting::orderBy('gateway')->get();

        return view('admin.payment-settings.index', compact('settings'));
    }

    public function update(Request $request, SystemPaymentSetting $paymentSetting): RedirectResponse
    {
        $validated = $request->validate([
            'is_active'      => ['boolean'],
            'mode'           => ['required', 'in:sandbox,production'],
            'api_key'        => ['nullable', 'string', 'max:1000'],
            'webhook_secret' => ['nullable', 'string', 'max:1000'],
            'merchant_id'    => ['nullable', 'string', 'max:255'],
            'store_id'       => ['nullable', 'string', 'max:255'],
        ]);

        $paymentSetting->is_active   = $request->boolean('is_active');
        $paymentSetting->mode        = $validated['mode'];
        $paymentSetting->merchant_id = $validated['merchant_id'] ?? null;

        // store_id stocké dans le champ config JSON
        $config = $paymentSetting->config ?? [];
        if (isset($validated['store_id'])) {
            $config['store_id'] = $validated['store_id'] ?: null;
        }
        $paymentSetting->config = $config;

        if (!empty($validated['api_key'])) {
            $paymentSetting->setEncryptedApiKey($validated['api_key']);
        }

        if (!empty($validated['webhook_secret'])) {
            $paymentSetting->setEncryptedWebhookSecret($validated['webhook_secret']);
        }

        $paymentSetting->save();

        Log::channel('payments')->info('Payment setting updated', [
            'gateway'    => $paymentSetting->gateway,
            'is_active'  => $paymentSetting->is_active,
            'mode'       => $paymentSetting->mode,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', "Paramètres {$paymentSetting->gateway} mis à jour.");
    }
}

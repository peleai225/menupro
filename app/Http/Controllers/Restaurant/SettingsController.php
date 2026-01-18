<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\UpdateSettingsRequest;
use App\Models\NotificationSetting;
use App\Services\MediaUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        protected MediaUploader $mediaUploader
    ) {}

    /**
     * Display settings page.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        $notificationSettings = $restaurant->notificationSettings ?? new NotificationSetting(NotificationSetting::defaults());

        return view('pages.restaurant.settings', compact('restaurant', 'notificationSettings'));
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(UpdateSettingsRequest $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;
        $data = $request->validated();

        // Handle logo
        if ($request->hasFile('logo')) {
            $this->mediaUploader->delete($restaurant->logo_path);
            $data['logo_path'] = $this->mediaUploader->uploadLogo(
                $request->file('logo'),
                $restaurant->id
            );
        } elseif ($request->boolean('remove_logo')) {
            $this->mediaUploader->delete($restaurant->logo_path);
            $data['logo_path'] = null;
        }

        // Handle banner
        if ($request->hasFile('banner')) {
            $this->mediaUploader->delete($restaurant->banner_path);
            $data['banner_path'] = $this->mediaUploader->uploadBanner(
                $request->file('banner'),
                $restaurant->id
            );
        } elseif ($request->boolean('remove_banner')) {
            $this->mediaUploader->delete($restaurant->banner_path);
            $data['banner_path'] = null;
        }

        // Filter out non-fillable fields
        unset($data['remove_logo'], $data['remove_banner']);

        $restaurant->update($data);

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Update payment settings (Lygos).
     */
    public function updatePayment(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;
        
        $this->authorize('managePayments', $restaurant);

        $request->validate([
            'lygos_api_key' => ['nullable', 'string', 'max:255'],
            'lygos_api_secret' => ['nullable', 'string', 'max:255'],
            'lygos_enabled' => ['boolean'],
        ]);

        // Only update if new values provided
        if ($request->filled('lygos_api_key')) {
            $restaurant->lygos_api_key = $request->lygos_api_key;
        }

        if ($request->filled('lygos_api_secret')) {
            $restaurant->lygos_api_secret = $request->lygos_api_secret;
        }

        $restaurant->lygos_enabled = $request->boolean('lygos_enabled');
        $restaurant->save();

        return back()->with('success', 'Paramètres de paiement mis à jour.');
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        $request->validate([
            'email_new_order' => ['boolean'],
            'email_order_cancelled' => ['boolean'],
            'email_low_stock' => ['boolean'],
            'email_subscription_reminder' => ['boolean'],
            'sms_new_order' => ['boolean'],
            'sms_order_cancelled' => ['boolean'],
            'push_new_order' => ['boolean'],
            'push_order_status' => ['boolean'],
        ]);

        NotificationSetting::updateOrCreate(
            ['restaurant_id' => $restaurant->id],
            $request->only([
                'email_new_order',
                'email_order_cancelled',
                'email_low_stock',
                'email_subscription_reminder',
                'sms_new_order',
                'sms_order_cancelled',
                'push_new_order',
                'push_order_status',
            ])
        );

        return back()->with('success', 'Paramètres de notification mis à jour.');
    }

    /**
     * Update opening hours.
     */
    public function updateHours(Request $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurant;

        $request->validate([
            'opening_hours' => ['required', 'array'],
            'opening_hours.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.close' => ['nullable', 'date_format:H:i'],
            'opening_hours.*.closed' => ['boolean'],
        ]);

        $restaurant->update([
            'opening_hours' => $request->opening_hours,
        ]);

        return back()->with('success', 'Horaires mis à jour.');
    }
}


<?php

namespace App\Livewire\Restaurant;

use App\Models\Restaurant;
use App\Models\RestaurantWallet;
use App\Services\MediaUploader;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public ?Restaurant $restaurant = null;

    // General Info
    #[Rule('required|string|max:100')]
    public string $name = '';

    #[Rule('nullable|string|max:500')]
    public ?string $description = null;

    #[Rule('nullable|string|max:200')]
    public ?string $tagline = null;

    #[Rule('nullable|string|max:20')]
    public ?string $phone = null;

    #[Rule('nullable|email|max:255')]
    public ?string $email = null;

    #[Rule('nullable|url|max:255')]
    public ?string $website = null;

    // Address
    #[Rule('nullable|string|max:255')]
    public ?string $address = null;

    #[Rule('nullable|string|max:100')]
    public ?string $city = null;

    #[Rule('nullable|string|max:20')]
    public ?string $postal_code = null;

    // Images
    public $logo = null;
    public $banner = null;
    public ?string $existingLogo = null;
    public ?string $existingBanner = null;

    // Delivery
    public bool $delivery_enabled = false;
    public int $delivery_fee = 0;
    public int $min_order_amount = 0;
    public int $estimated_prep_time = 30;
    public ?string $delivery_zones = null;

    // Payment
    public bool $cash_on_delivery = true;

    // Jeko
    public bool $jeko_enabled = false;
    public ?string $jeko_api_key = null;
    public ?string $jeko_api_key_id = null;
    public ?string $jeko_webhook_secret = null;
    public ?string $jeko_store_id = null;

    // Wave Business
    public bool $wave_business_enabled = false;
    public ?string $wave_api_key = null;
    public ?string $wave_webhook_secret = null;

    // Appearance / Colors
    #[Rule('nullable|string|regex:/^#[0-9A-Fa-f]{6}$/')]
    public ?string $primary_color = null;

    #[Rule('nullable|string|regex:/^#[0-9A-Fa-f]{6}$/')]
    public ?string $secondary_color = null;

    // Opening Hours
    public array $opening_hours = [];

    // Verification / RCCM
    public ?string $company_name = null;
    public ?string $rccm = null;
    public $rccm_document = null;
    public ?string $existingRccmDocument = null;

    public string $activeTab = 'general';

    public string $payoutAmount = '';

    // Auto-payout
    public bool $auto_payout_enabled = false;
    public int $min_payout_amount = 1000;
    public ?string $wallet_phone = null;
    public ?string $wallet_recipient_name = null;

    public function mount(): void
    {
        $restaurant = auth()->user()->restaurant;
        
        // Redirect if user has no restaurant
        if (!$restaurant) {
            session()->flash('error', 'Vous n\'avez pas de restaurant associé à votre compte.');
            $this->redirect(route('home'));
            return;
        }
        
        $this->restaurant = $restaurant;
        
        // Ensure restaurant has a slug
        if (!$this->restaurant->slug) {
            $this->restaurant->slug = \Illuminate\Support\Str::slug($this->restaurant->name);
            $this->restaurant->save();
        }

        $this->name = $this->restaurant->name;
        $this->description = $this->restaurant->description;
        $this->tagline = $this->restaurant->tagline;
        $this->phone = $this->restaurant->phone;
        $this->email = $this->restaurant->email;
        $this->website = $this->restaurant->website;
        $this->address = $this->restaurant->address;
        $this->city = $this->restaurant->city;
        $this->postal_code = $this->restaurant->postal_code;
        $this->existingLogo = $this->restaurant->logo_path;
        $this->existingBanner = $this->restaurant->banner_path;
        $this->delivery_enabled = $this->restaurant->delivery_enabled ?? false;
        $this->delivery_fee = $this->restaurant->delivery_fee ?? 0;
        $this->min_order_amount = $this->restaurant->min_order_amount ?? 0;
        $this->estimated_prep_time = $this->restaurant->estimated_prep_time ?? 30;
        $this->delivery_zones = $this->restaurant->delivery_zones;
        $this->cash_on_delivery = $this->restaurant->cash_on_delivery ?? true;
        $this->jeko_enabled = $this->restaurant->jeko_enabled ?? false;
        $this->jeko_api_key = $this->restaurant->getJekoApiKey();
        $this->jeko_api_key_id = $this->restaurant->getJekoApiKeyId();
        $this->jeko_webhook_secret = $this->restaurant->getJekoWebhookSecret();
        $this->jeko_store_id = $this->restaurant->jeko_store_id;
        $this->wave_business_enabled = $this->restaurant->wave_business_enabled ?? false;
        $this->wave_api_key = $this->restaurant->getWaveApiKey();
        $this->wave_webhook_secret = $this->restaurant->getWaveWebhookSecret();
        $this->primary_color = $this->restaurant->primary_color ?? '#f97316';
        $this->secondary_color = $this->restaurant->secondary_color ?? '#1c1917';
        $this->opening_hours = $this->restaurant->opening_hours ?? $this->getDefaultOpeningHours();
        
        // Auto-payout (wallet settings)
        $wallet = $this->restaurant->wallet;
        if ($wallet) {
            $this->auto_payout_enabled = $wallet->auto_payout_enabled ?? false;
            $this->min_payout_amount = $wallet->min_payout_amount ?? 1000;
            $this->wallet_phone = $wallet->phone;
            $this->wallet_recipient_name = $wallet->recipient_name;
        }

        // Verification fields
        $this->company_name = $this->restaurant->company_name;
        $this->rccm = $this->restaurant->rccm;
        $this->existingRccmDocument = $this->restaurant->rccm_document_path;
    }

    #[Computed]
    public function walletBalance(): float
    {
        $wallet = $this->restaurant?->wallet;
        return $wallet ? (float) $wallet->balance : 0;
    }

    #[Computed]
    public function hasWallet(): bool
    {
        return $this->restaurant && RestaurantWallet::where('restaurant_id', $this->restaurant->id)->exists();
    }

    #[Computed]
    public function payoutAvailable(): bool
    {
        return false;
    }

    #[Computed]
    public function jekoPaymentAvailable(): bool
    {
        try {
            $jeko = app(\App\Services\JekoGateway::class)->forPlatform();
            return $jeko->isConfigured() && !empty($jeko->getPlatformStoreId());
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function requestPayout(): void
    {
        session()->flash('error', 'Le service de retrait n\'est pas encore disponible. Contactez le support.');
    }

    public function saveWallet(): void
    {
        $this->validate([
            'wallet_phone' => ['nullable', 'string', 'max:20'],
            'wallet_recipient_name' => ['nullable', 'string', 'max:255'],
            'min_payout_amount' => ['required', 'integer', 'min:500'],
        ]);

        $wallet = RestaurantWallet::firstOrCreate(
            ['restaurant_id' => $this->restaurant->id],
            ['balance' => 0, 'total_collected' => 0, 'total_withdrawn' => 0]
        );

        $wallet->update([
            'auto_payout_enabled' => $this->auto_payout_enabled,
            'min_payout_amount' => $this->min_payout_amount,
            'phone' => $this->wallet_phone,
            'recipient_name' => $this->wallet_recipient_name,
        ]);

        unset($this->walletBalance, $this->hasWallet);
        session()->flash('success', 'Paramètres du wallet mis à jour.');
    }

    protected function getDefaultOpeningHours(): array
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $hours = [];

        foreach ($days as $day) {
            $hours[$day] = [
                'is_open' => true,
                'open' => '08:00',
                'close' => '22:00',
            ];
        }

        return $hours;
    }

    public function saveGeneral(): void
    {
        try {
            $this->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'tagline' => 'nullable|string|max:200',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'website' => 'nullable|url|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'logo' => 'nullable|image|max:2048',
                'banner' => 'nullable|image|max:5120',
            ]);

            $data = [
            'name' => $this->name,
            'description' => $this->description,
            'tagline' => $this->tagline,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
        ];

        // Handle logo upload
        if ($this->logo) {
            try {
                $uploader = app(MediaUploader::class);
                $data['logo_path'] = $uploader->upload($this->logo, "restaurants/{$this->restaurant->id}/logo");

                if ($this->existingLogo) {
                    Storage::disk('public')->delete($this->existingLogo);
                }
                $this->existingLogo = $data['logo_path'];
                $this->logo = null;
            } catch (\Exception $e) {
                session()->flash('error', 'Erreur lors de l\'upload du logo : ' . $e->getMessage());
                return;
            }
        }

        // Handle banner upload
        if ($this->banner) {
            try {
                $uploader = app(MediaUploader::class);
                $data['banner_path'] = $uploader->upload($this->banner, "restaurants/{$this->restaurant->id}/banner");

                if ($this->existingBanner) {
                    Storage::disk('public')->delete($this->existingBanner);
                }
                $this->existingBanner = $data['banner_path'];
                $this->banner = null;
            } catch (\Exception $e) {
                session()->flash('error', 'Erreur lors de l\'upload de la bannière : ' . $e->getMessage());
                return;
            }
        }

            $this->restaurant->update($data);

            session()->flash('success', 'Informations mises à jour avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Les erreurs de validation sont gérées automatiquement par Livewire
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function saveDelivery(): void
    {
        $this->validate([
            'delivery_enabled' => 'boolean',
            'delivery_fee' => 'integer|min:0',
            'min_order_amount' => 'integer|min:0',
            'estimated_prep_time' => 'integer|min:1',
            'delivery_zones' => 'nullable|string|max:1000',
        ]);

        $this->restaurant->update([
            'delivery_enabled' => $this->delivery_enabled,
            'delivery_fee' => $this->delivery_fee,
            'min_order_amount' => $this->min_order_amount,
            'estimated_prep_time' => $this->estimated_prep_time,
            'delivery_zones' => $this->delivery_zones,
        ]);

        session()->flash('success', 'Paramètres de livraison mis à jour.');
    }

    public function savePayment(): void
    {
        $this->validate([
            'cash_on_delivery' => 'boolean',
            'jeko_enabled' => 'boolean',
            'jeko_api_key' => 'nullable|string|max:255',
            'jeko_api_key_id' => 'nullable|string|max:255',
            'jeko_webhook_secret' => 'nullable|string|max:255',
            'jeko_store_id' => 'nullable|string|max:100',
            'wave_business_enabled' => 'boolean',
            'wave_api_key' => 'nullable|string|max:500',
            'wave_webhook_secret' => 'nullable|string|max:500',
        ]);

        $data = [
            'cash_on_delivery' => $this->cash_on_delivery,
            'jeko_enabled' => $this->jeko_enabled,
            'jeko_store_id' => $this->jeko_store_id,
            'wave_business_enabled' => $this->wave_business_enabled,
        ];

        if ($this->jeko_api_key !== null) {
            $data['jeko_api_key'] = $this->jeko_api_key;
        }
        if ($this->jeko_api_key_id !== null) {
            $data['jeko_api_key_id'] = $this->jeko_api_key_id;
        }
        if ($this->jeko_webhook_secret !== null) {
            $data['jeko_webhook_secret'] = $this->jeko_webhook_secret;
        }
        if ($this->wave_api_key !== null) {
            $data['wave_api_key'] = $this->wave_api_key;
        }
        if ($this->wave_webhook_secret !== null) {
            $data['wave_webhook_secret'] = $this->wave_webhook_secret;
        }

        $this->restaurant->update($data);

        session()->flash('success', 'Paramètres de paiement mis à jour.');
    }

    public function saveHours(): void
    {
        try {
            // Validation des horaires
            $rules = [];
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            
            foreach ($days as $day) {
                $rules["opening_hours.{$day}.is_open"] = 'boolean';
                $rules["opening_hours.{$day}.open"] = 'nullable|date_format:H:i';
                $rules["opening_hours.{$day}.close"] = 'nullable|date_format:H:i';
            }
            
            $this->validate($rules);

            // S'assurer que tous les jours sont présents
            $completeHours = [];
            foreach ($days as $day) {
                $completeHours[$day] = [
                    'is_open' => $this->opening_hours[$day]['is_open'] ?? false,
                    'open' => $this->opening_hours[$day]['open'] ?? '08:00',
                    'close' => $this->opening_hours[$day]['close'] ?? '22:00',
                ];
            }

            $this->restaurant->update([
                'opening_hours' => $completeHours,
            ]);

            // Mettre à jour la propriété locale
            $this->opening_hours = $completeHours;

            session()->flash('success', 'Horaires mis à jour avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Erreur de validation : ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue lors de la sauvegarde : ' . $e->getMessage());
        }
    }

    public function saveAppearance(): void
    {
        try {
            $this->validate([
                'primary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ]);

            $this->restaurant->update([
                'primary_color' => $this->primary_color ?? '#f97316',
                'secondary_color' => $this->secondary_color ?? '#1c1917',
            ]);

            session()->flash('success', 'Apparence mise à jour.');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function saveVerification(): void
    {
        try {
            $this->validate([
                'company_name' => 'nullable|string|max:255',
                'rccm' => 'nullable|string|max:50|unique:restaurants,rccm,' . $this->restaurant->id,
                'rccm_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
            ]);

            $data = [
                'company_name' => $this->company_name,
                'rccm' => $this->rccm,
            ];

            // Handle RCCM document upload
            if ($this->rccm_document) {
                $uploader = app(MediaUploader::class);
                $data['rccm_document_path'] = $uploader->upload(
                    $this->rccm_document, 
                    "restaurants/{$this->restaurant->id}/documents"
                );

                // Delete old document if exists
                if ($this->existingRccmDocument) {
                    Storage::disk('public')->delete($this->existingRccmDocument);
                }
                
                $this->existingRccmDocument = $data['rccm_document_path'];
                $this->rccm_document = null;
            }

            // If RCCM or document removed, reset verification
            if (empty($this->rccm) || empty($data['rccm_document_path'] ?? $this->existingRccmDocument)) {
                $data['verified_at'] = null;
                $data['verified_by'] = null;
            }

            $this->restaurant->update($data);
            $this->restaurant->refresh();

            session()->flash('success', 'Informations de vérification mises à jour.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    public function deleteMedia(string $type): void
    {
        if ($type === 'logo' && $this->existingLogo) {
            Storage::disk('public')->delete($this->existingLogo);
            $this->restaurant->update(['logo_path' => null]);
            $this->existingLogo = null;
        }

        if ($type === 'banner' && $this->existingBanner) {
            Storage::disk('public')->delete($this->existingBanner);
            $this->restaurant->update(['banner_path' => null]);
            $this->existingBanner = null;
        }

        session()->flash('success', 'Image supprimée.');
    }

    public function render()
    {
        // If no restaurant, return empty view (redirect will happen from mount)
        if (!$this->restaurant) {
            return view('livewire.restaurant.settings')
                ->layout('components.layouts.admin-restaurant', [
                    'title' => 'Paramètres',
                    'restaurant' => null,
                    'subscription' => null,
                ]);
        }
        
        // Refresh restaurant to get latest data (e.g., after admin verification)
        $this->restaurant->refresh();
        
        $subscription = $this->restaurant->activeSubscription ?? null;
        
        return view('livewire.restaurant.settings')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Paramètres',
                'restaurant' => $this->restaurant,
                'subscription' => $subscription,
            ]);
    }
}


<?php

namespace App\Livewire\Restaurant;

use App\Models\Parcel;
use App\Models\Restaurant;
use App\Services\GeocodingService;
use App\Services\Parcels\ParcelException;
use App\Services\Parcels\ParcelPricingService;
use App\Services\Parcels\ParcelService;
use App\Support\Parcels\ParcelRules;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * « Envoyer un colis » : le commerçant commande une course (ramassage à son commerce par
 * défaut) et suit ses colis. Paiement Jeko (redirection) ou espèces au livreur.
 */
class Parcels extends Component
{
    use WithPagination;

    public string $pickup_address = '';
    public ?float $pickup_lat = null;
    public ?float $pickup_lng = null;
    public string $pickup_coords = '';

    public string $dropoff_search = '';
    public string $dropoff_address = '';
    public ?float $dropoff_lat = null;
    public ?float $dropoff_lng = null;
    public string $dropoff_coords = '';
    public array $dropoff_results = [];

    public string $sender_phone = '';
    public string $recipient_name = '';
    public string $recipient_phone = '';
    public string $size = 'small';
    public string $description = '';
    public string $note = '';
    public string $payment_method = 'cash';
    public string $jeko_operator = 'wave';

    public ?array $quote = null;
    public ?string $quoteError = null;
    public ?string $createdReference = null;

    public function mount(): void
    {
        $r = $this->restaurant();
        $this->pickup_address = (string) ($r->address ?? '');
        $this->pickup_lat = $r->latitude ? (float) $r->latitude : null;
        $this->pickup_lng = $r->longitude ? (float) $r->longitude : null;
        $this->sender_phone = (string) ($r->phone ?? '');
    }

    private function restaurant(): Restaurant
    {
        return auth()->user()->restaurant;
    }

    /** Coordonnées collées (« 6.8770, -6.4502 », lien partagé par WhatsApp…). */
    private static function parseCoords(string $value): ?array
    {
        if (preg_match('/(-?\d{1,2}\.\d+)\s*[,; ]\s*(-?\d{1,3}\.\d+)/', $value, $m)) {
            return [(float) $m[1], (float) $m[2]];
        }

        return null;
    }

    public function updatedPickupCoords(): void
    {
        if ($c = self::parseCoords($this->pickup_coords)) {
            [$this->pickup_lat, $this->pickup_lng] = $c;
            $this->refreshQuote();
        }
    }

    public function updatedDropoffCoords(): void
    {
        if ($c = self::parseCoords($this->dropoff_coords)) {
            [$this->dropoff_lat, $this->dropoff_lng] = $c;
            if ($this->dropoff_address === '') {
                $this->dropoff_address = 'Position GPS ' . $c[0] . ', ' . $c[1];
            }
            $this->refreshQuote();
        }
    }

    public function searchDropoff(GeocodingService $geo): void
    {
        $this->dropoff_results = $geo->searchAddress($this->dropoff_search, (string) ($this->restaurant()->city ?: 'Daloa'));
        if (!$this->dropoff_results) {
            $this->addError('dropoff_search', 'Aucune adresse trouvée. Collez plutôt la position GPS du destinataire.');
        }
    }

    public function chooseDropoff(int $index): void
    {
        $r = $this->dropoff_results[$index] ?? null;
        if (!$r) {
            return;
        }

        $this->dropoff_address = mb_substr(trim($r['display_name']), 0, 300);
        $this->dropoff_lat = (float) $r['lat'];
        $this->dropoff_lng = (float) $r['lng'];
        $this->dropoff_results = [];
        $this->refreshQuote();
    }

    public function updatedSize(): void
    {
        $this->refreshQuote();
    }

    public function refreshQuote(): void
    {
        $this->quote = null;
        $this->quoteError = null;

        if (!$this->pickup_lat || !$this->pickup_lng || !$this->dropoff_lat || !$this->dropoff_lng) {
            return;
        }

        try {
            $q = app(ParcelPricingService::class)->quote($this->pickup_lat, $this->pickup_lng, $this->dropoff_lat, $this->dropoff_lng, $this->size);
            $this->quote = ['price' => $q['price'], 'distance_km' => $q['distance_km'], 'city' => $q['city']->name];
        } catch (ParcelException $e) {
            $this->quoteError = $e->getMessage();
        }
    }

    public function send(ParcelService $service)
    {
        $data = [
            'pickup_address' => $this->pickup_address,
            'pickup_lat' => $this->pickup_lat,
            'pickup_lng' => $this->pickup_lng,
            'dropoff_address' => $this->dropoff_address,
            'dropoff_lat' => $this->dropoff_lat,
            'dropoff_lng' => $this->dropoff_lng,
            'sender_name' => $this->restaurant()->name,
            'sender_phone' => $this->sender_phone,
            'recipient_name' => $this->recipient_name,
            'recipient_phone' => $this->recipient_phone,
            'size' => $this->size,
            'description' => $this->description,
            'note' => $this->note ?: null,
            'payment_method' => $this->payment_method,
            'jeko_operator' => $this->jeko_operator,
        ];

        $rules = ParcelRules::create();
        $rules['sender_phone'] = ['required', 'string', ParcelRules::PHONE];
        $validated = validator($data, $rules, ParcelRules::messages() + [
            'pickup_lat.required' => 'Position de ramassage inconnue : collez les coordonnées GPS du commerce.',
            'dropoff_lat.required' => 'Choisissez l\'adresse du destinataire dans la liste ou collez sa position GPS.',
        ], ParcelRules::attributes())->validate();

        try {
            [$parcel] = $service->create($validated, Parcel::SENDER_RESTAURANT, restaurant: $this->restaurant(), user: auth()->user());

            if ($parcel->isJeko()) {
                $payment = $service->initiateJekoPayment($parcel);

                return redirect()->away($payment['payment_url']);
            }
        } catch (ParcelException $e) {
            $this->addError('form', $e->getMessage());

            return null;
        }

        $this->createdReference = $parcel->reference;
        $this->reset(['dropoff_search', 'dropoff_address', 'dropoff_lat', 'dropoff_lng', 'dropoff_coords', 'recipient_name', 'recipient_phone', 'description', 'note', 'quote']);
        unset($this->parcels);
        session()->flash('success', "Colis {$parcel->reference} créé : nous cherchons un livreur. Payez " . number_format($parcel->price, 0, ',', ' ') . ' F en espèces au livreur au ramassage.');

        return null;
    }

    public function pay(string $reference, ParcelService $service)
    {
        $parcel = $this->ownParcel($reference);

        try {
            return redirect()->away($service->initiateJekoPayment($parcel)['payment_url']);
        } catch (ParcelException $e) {
            session()->flash('error', $e->getMessage());

            return null;
        }
    }

    public function cancel(string $reference, ParcelService $service): void
    {
        try {
            $parcel = $service->cancel($this->ownParcel($reference), 'sender', 'Annulé par le commerçant');
            session()->flash('success', "Colis {$parcel->reference} annulé." . ($parcel->refund_status === 'pending' ? ' MenuPro vous rembourse.' : ''));
        } catch (ParcelException $e) {
            session()->flash('error', $e->getMessage());
        }

        unset($this->parcels);
    }

    private function ownParcel(string $reference): Parcel
    {
        return Parcel::where('restaurant_id', $this->restaurant()->id)->where('reference', $reference)->firstOrFail();
    }

    #[Computed]
    public function parcels()
    {
        return Parcel::where('restaurant_id', $this->restaurant()->id)
            ->with('driver:id,name,phone')
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.restaurant.parcels')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Envoyer un colis',
                'restaurant' => $this->restaurant(),
                'subscription' => $this->restaurant()?->activeSubscription,
            ]);
    }
}

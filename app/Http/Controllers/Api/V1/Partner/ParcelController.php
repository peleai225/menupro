<?php

namespace App\Http\Controllers\Api\V1\Partner;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPartner;
use App\Models\Parcel;
use App\Services\Parcels\ParcelException;
use App\Services\Parcels\ParcelPricingService;
use App\Services\Parcels\ParcelService;
use App\Support\Parcels\ParcelPresenter;
use App\Support\Parcels\ParcelRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API partenaires (Authorization: Bearer <clé>). Courses facturées au partenaire.
 */
class ParcelController extends Controller
{
    public function __construct(
        private ParcelPricingService $pricing,
        private ParcelService $parcels,
    ) {}

    private function partner(Request $request): DeliveryPartner
    {
        return $request->attributes->get('delivery_partner');
    }

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate(ParcelRules::quote(), ParcelRules::messages(), ParcelRules::attributes());

        $q = $this->pricing->quote(
            (float) $data['pickup_lat'], (float) $data['pickup_lng'],
            (float) $data['dropoff_lat'], (float) $data['dropoff_lng'],
            $data['size'],
            $this->partner($request),
        );

        return response()->json([
            'city' => $q['city']->name,
            'distance_km' => $q['distance_km'],
            'price' => $q['price'],
            'currency' => 'XOF',
            'size' => $data['size'],
            'partner_discount' => $q['breakdown']['partner_discount'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(ParcelRules::create(partner: true), ParcelRules::messages(), ParcelRules::attributes());

        [$parcel, $created] = $this->parcels->create($data, Parcel::SENDER_PARTNER, partner: $this->partner($request));

        $payload = ParcelPresenter::forPartner($parcel->load('driver'));
        // Code de remise : à transmettre à la cliente (destinataire), renvoyé à chaque appel idempotent
        $payload['handover_code'] = $parcel->status->isFinal() ? null : $parcel->handover_code;

        return response()->json([
            'parcel' => $payload,
            'idempotent_replay' => !$created,
        ], $created ? 201 : 200);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        return response()->json(['parcel' => ParcelPresenter::forPartner($this->ownParcel($request, $reference)->load('driver'))]);
    }

    public function cancel(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate(['reason' => 'nullable|string|max:300'], ParcelRules::messages(), ParcelRules::attributes());

        $parcel = $this->parcels->cancel($this->ownParcel($request, $reference), 'sender', $data['reason'] ?? 'Annulé par le partenaire');

        return response()->json([
            'message' => 'Colis annulé.',
            'parcel' => ParcelPresenter::forPartner($parcel->load('driver')),
        ]);
    }

    /** {ref} = référence MenuPro (COL-…) ou référence externe du partenaire. */
    private function ownParcel(Request $request, string $reference): Parcel
    {
        $partnerId = $this->partner($request)->id;

        return Parcel::where('delivery_partner_id', $partnerId)
            ->where(fn ($q) => $q->where('reference', $reference)->orWhere('external_reference', $reference))
            ->firstOr(fn () => throw new ParcelException('Colis introuvable.', 'not_found', 404));
    }
}

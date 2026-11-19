<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\ParcelStatus;
use App\Http\Controllers\Controller;
use App\Models\Parcel;
use App\Services\Parcels\ParcelException;
use App\Services\Parcels\ParcelPricingService;
use App\Services\Parcels\ParcelService;
use App\Support\Parcels\ParcelPresenter;
use App\Support\Parcels\ParcelRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Colis envoyés depuis l'app client (contrat : docs/api-colis.md).
 */
class ParcelController extends Controller
{
    public function __construct(
        private ParcelPricingService $pricing,
        private ParcelService $parcels,
    ) {}

    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate(ParcelRules::quote(), ParcelRules::messages(), ParcelRules::attributes());

        $q = $this->pricing->quote(
            (float) $data['pickup_lat'], (float) $data['pickup_lng'],
            (float) $data['dropoff_lat'], (float) $data['dropoff_lng'],
            $data['size'],
        );

        return response()->json([
            'city' => $q['city']->name,
            'distance_km' => $q['distance_km'],
            'price' => $q['price'],
            'currency' => 'XOF',
            'size' => $data['size'],
            'breakdown' => [
                'base_fee' => $q['breakdown']['base_fee'],
                'distance_fee' => $q['breakdown']['distance_fee'],
                'course_rounded' => $q['breakdown']['course_rounded'],
                'size_supplement' => $q['breakdown']['size_supplement'],
            ],
            'payment_methods' => ['jeko', 'cash'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(ParcelRules::create(), ParcelRules::messages(), ParcelRules::attributes());

        $user = $request->user();
        $customer = $user->customer;
        if (!$customer) {
            return response()->json(['message' => 'Profil client introuvable.', 'error_code' => 'no_customer_profile'], 403);
        }

        $data['sender_name'] = $data['sender_name'] ?? $user->name;
        $data['sender_phone'] = $data['sender_phone'] ?? $customer->phone;

        if (empty($data['sender_phone'])) {
            return response()->json([
                'message' => 'Indiquez votre numéro de téléphone (expéditeur).',
                'error_code' => 'sender_phone_required',
            ], 422);
        }

        [$parcel] = $this->parcels->create($data, Parcel::SENDER_CUSTOMER, customer: $customer, user: $user);

        $response = ['parcel' => ParcelPresenter::forSender($parcel->fresh('driver'))];

        if ($parcel->isJeko()) {
            $response['next_step'] = 'payment';
            try {
                $response['payment'] = $this->parcels->initiateJekoPayment($parcel);
            } catch (ParcelException $e) {
                // Colis créé : l'app peut relancer POST /parcels/{ref}/pay
                $response['payment'] = null;
                $response['payment_error'] = ['message' => $e->getMessage(), 'error_code' => $e->errorCode];
            }
        } else {
            $response['next_step'] = 'track';
        }

        return response()->json($response, 201);
    }

    public function pay(Request $request, string $reference): JsonResponse
    {
        $parcel = $this->ownParcel($request, $reference);

        return response()->json(['payment' => $this->parcels->initiateJekoPayment($parcel)]);
    }

    public function index(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 403, 'Profil client introuvable.');

        $request->validate(['status' => 'nullable|in:active,done']);

        $parcels = Parcel::where('customer_id', $customer->id)
            ->when($request->status === 'active', fn ($q) => $q->whereNotIn('status', [
                ParcelStatus::DELIVERED->value, ParcelStatus::CANCELLED->value, ParcelStatus::FAILED->value,
            ]))
            ->when($request->status === 'done', fn ($q) => $q->whereIn('status', [
                ParcelStatus::DELIVERED->value, ParcelStatus::CANCELLED->value, ParcelStatus::FAILED->value,
            ]))
            ->with('driver')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $parcels->getCollection()->map(fn (Parcel $p) => ParcelPresenter::forSender($p))->values(),
            'meta' => [
                'current_page' => $parcels->currentPage(),
                'last_page' => $parcels->lastPage(),
                'total' => $parcels->total(),
            ],
        ]);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        return response()->json(['parcel' => ParcelPresenter::forSender($this->ownParcel($request, $reference)->load('driver'))]);
    }

    public function cancel(Request $request, string $reference): JsonResponse
    {
        $data = $request->validate(['reason' => 'nullable|string|max:300'], ParcelRules::messages(), ParcelRules::attributes());

        $parcel = $this->parcels->cancel(
            $this->ownParcel($request, $reference),
            'sender',
            $data['reason'] ?? 'Annulé par l\'expéditeur',
        );

        return response()->json([
            'message' => $parcel->refund_status === 'pending'
                ? 'Colis annulé. Votre paiement vous sera remboursé par MenuPro.'
                : 'Colis annulé.',
            'parcel' => ParcelPresenter::forSender($parcel->load('driver')),
        ]);
    }

    /** Un client ne voit que ses colis : sinon 404 (pas de fuite d'existence). */
    private function ownParcel(Request $request, string $reference): Parcel
    {
        $customer = $request->user()->customer;

        return Parcel::where('reference', $reference)
            ->where('customer_id', $customer?->id ?? 0)
            ->firstOr(fn () => throw new ParcelException('Colis introuvable.', 'not_found', 404));
    }
}

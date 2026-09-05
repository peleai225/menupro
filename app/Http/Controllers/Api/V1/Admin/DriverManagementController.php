<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverManagementController extends Controller
{
    public function __construct(private FcmService $fcm) {}

    /**
     * Liste tous les livreurs avec filtres.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected,suspended',
            'city'   => 'nullable|string|max:100',
            'online' => 'nullable|boolean',
        ]);

        $query = DeliveryDriver::with('user:id,email')
            ->latest();

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->boolean('online')) {
            $query->where('is_active', true)->where('is_available', true);
        }

        $drivers = $query->paginate(30);

        return response()->json([
            'data' => $drivers->map(fn($d) => $this->formatDriver($d)),
            'meta' => [
                'current_page' => $drivers->currentPage(),
                'last_page'    => $drivers->lastPage(),
                'total'        => $drivers->total(),
            ],
            'counts' => [
                'pending'   => DeliveryDriver::where('verification_status', 'pending')->count(),
                'approved'  => DeliveryDriver::where('verification_status', 'approved')->count(),
                'online'    => DeliveryDriver::where('is_active', true)->where('is_available', true)->count(),
                'suspended' => DeliveryDriver::where('verification_status', 'suspended')->count(),
            ],
        ]);
    }

    /**
     * Détail d'un livreur avec ses documents.
     */
    public function show(int $id): JsonResponse
    {
        $driver = DeliveryDriver::with(['user:id,email', 'deliveries' => fn($q) => $q->latest()->limit(10)])
            ->findOrFail($id);

        return response()->json(array_merge(
            $this->formatDriver($driver),
            [
                'documents' => [
                    'cni_photo_url'      => $driver->cni_photo_path ? asset('storage/' . $driver->cni_photo_path) : null,
                    'license_photo_url'  => $driver->license_photo_path ? asset('storage/' . $driver->license_photo_path) : null,
                    'vehicle_photo_url'  => $driver->vehicle_photo_path ? asset('storage/' . $driver->vehicle_photo_path) : null,
                ],
                'recent_deliveries' => $driver->deliveries->map(fn($d) => [
                    'id'           => $d->id,
                    'status'       => $d->status,
                    'delivered_at' => $d->delivered_at,
                    'created_at'   => $d->created_at,
                ]),
            ]
        ));
    }

    /**
     * Approuver un livreur.
     */
    public function approve(int $id): JsonResponse
    {
        $driver = DeliveryDriver::where('verification_status', 'pending')->findOrFail($id);

        DB::transaction(function () use ($driver) {
            $driver->update([
                'verification_status' => 'approved',
                'is_active'           => true,
            ]);

            // Activer le compte user
            $driver->user?->update(['is_active' => true]);
        });

        if ($driver->fcm_token) {
            try {
                $this->fcm->sendToToken(
                    $driver->fcm_token,
                    '✅ Dossier approuvé',
                    'Félicitations ! Votre dossier MenuPro Livreur a été approuvé. Vous pouvez maintenant vous connecter.',
                    ['type' => 'account_approved'],
                );
            } catch (\Throwable $e) {
                Log::warning('FCM driver approved notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => "Livreur {$driver->name} approuvé."]);
    }

    /**
     * Rejeter un dossier livreur.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $data   = $request->validate(['reason' => 'required|string|max:300']);
        $driver = DeliveryDriver::findOrFail($id);

        $driver->update(['verification_status' => 'rejected']);

        if ($driver->fcm_token) {
            try {
                $this->fcm->sendToToken(
                    $driver->fcm_token,
                    '❌ Dossier non retenu',
                    "Votre dossier n'a pas été retenu. Raison : {$data['reason']}. Contactez le support pour plus d'infos.",
                    ['type' => 'account_rejected'],
                );
            } catch (\Throwable $e) {
                Log::warning('FCM driver rejected notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => "Dossier rejeté. Raison : {$data['reason']}"]);
    }

    /**
     * Suspendre un livreur actif.
     */
    public function suspend(Request $request, int $id): JsonResponse
    {
        $data   = $request->validate(['reason' => 'required|string|max:300']);
        $driver = DeliveryDriver::findOrFail($id);

        DB::transaction(function () use ($driver) {
            // Annuler la livraison active si elle existe
            $activeDelivery = $driver->activeDelivery();
            if ($activeDelivery) {
                $activeDelivery->update([
                    'status'           => \App\Enums\DeliveryStatus::CANCELLED->value,
                    'cancelled_at'     => now(),
                    'cancellation_reason' => 'Livreur suspendu par un administrateur.',
                ]);
                // Remettre la commande en attente de nouveau livreur
                $activeDelivery->order?->update(['driver_assigned_at' => null]);
            }

            $driver->update([
                'verification_status' => 'suspended',
                'is_active'           => false,
                'is_available'        => false,
            ]);

            $driver->user?->update(['is_active' => false]);
        });

        if ($driver->fcm_token) {
            try {
                $this->fcm->sendToToken(
                    $driver->fcm_token,
                    '⚠️ Compte suspendu',
                    "Votre compte a été suspendu. Raison : {$data['reason']}. Contactez le support.",
                    ['type' => 'account_suspended'],
                );
            } catch (\Throwable $e) {
                Log::warning('FCM driver suspended notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => "Livreur {$driver->name} suspendu."]);
    }

    /**
     * Réactiver un livreur suspendu.
     */
    public function reactivate(int $id): JsonResponse
    {
        $driver = DeliveryDriver::where('verification_status', 'suspended')->findOrFail($id);

        DB::transaction(function () use ($driver) {
            $driver->update([
                'verification_status' => 'approved',
                'is_active'           => true,
            ]);

            $driver->user?->update(['is_active' => true]);
        });

        if ($driver->fcm_token) {
            try {
                $this->fcm->sendToToken(
                    $driver->fcm_token,
                    '✅ Compte réactivé',
                    'Votre compte MenuPro Livreur a été réactivé. Vous pouvez reprendre les livraisons.',
                    ['type' => 'account_reactivated'],
                );
            } catch (\Throwable $e) {
                Log::warning('FCM driver reactivated notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json(['message' => "Livreur {$driver->name} réactivé."]);
    }

    private function formatDriver(DeliveryDriver $d): array
    {
        return [
            'id'                  => $d->id,
            'name'                => $d->name,
            'phone'               => $d->phone,
            'email'               => $d->email,
            'city'                => $d->city,
            'zone'                => $d->zone,
            'vehicle_type'        => $d->vehicle_type,
            'vehicle_plate'       => $d->vehicle_plate,
            'verification_status' => $d->verification_status,
            'is_active'           => $d->is_active,
            'is_available'        => $d->is_available,
            'rating'              => $d->rating,
            'total_deliveries'    => $d->total_deliveries,
            'total_cancelled'     => $d->total_cancelled,
            'total_earnings_xof'  => $d->total_earnings_xof,
            'location_updated_at' => $d->location_updated_at,
            'created_at'          => $d->created_at,
        ];
    }
}

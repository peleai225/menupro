<?php

namespace App\Services\Parcels;

use App\Enums\ParcelStatus;
use App\Jobs\DispatchParcelJob;
use App\Jobs\SendPartnerWebhookJob;
use App\Models\Customer;
use App\Models\DeliveryDriver;
use App\Models\DeliveryPartner;
use App\Models\DriverCashDebt;
use App\Models\DriverCashRemittance;
use App\Models\DriverEarning;
use App\Models\LedgerEntry;
use App\Models\Parcel;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\Parcels\ParcelAdminAlertNotification;
use App\Notifications\Parcels\ParcelDriverNotification;
use App\Notifications\Parcels\ParcelSenderNotification;
use App\Services\JekoGateway;
use App\Services\LedgerService;
use App\Support\Parcels\ParcelPresenter;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * Cycle de vie d'un colis : création, paiement, attribution, ramassage, remise, échec,
 * annulation, remboursement. Toutes les transitions passent par Parcel::transitionTo()
 * (machine d'états de ParcelStatus) sous verrou de ligne (lockForUpdate).
 */
class ParcelService
{
    public function __construct(
        private ParcelPricingService $pricing,
        private LedgerService $ledger,
    ) {}

    // =========================================================================
    // CRÉATION / PAIEMENT
    // =========================================================================

    /**
     * Crée un colis. Partenaire + external_reference déjà connue : renvoie le colis existant.
     *
     * @return array{0: Parcel, 1: bool} [colis, créé maintenant ?]
     *
     * @throws ParcelException
     */
    public function create(
        array $data,
        string $senderType,
        ?Customer $customer = null,
        ?Restaurant $restaurant = null,
        ?DeliveryPartner $partner = null,
        ?User $user = null,
    ): array {
        if ($partner && !empty($data['external_reference'])) {
            $existing = $this->findPartnerParcel($partner, $data['external_reference']);
            if ($existing) {
                return [$existing, false];
            }
        }

        $quote = $this->pricing->quote(
            (float) $data['pickup_lat'],
            (float) $data['pickup_lng'],
            (float) $data['dropoff_lat'],
            (float) $data['dropoff_lng'],
            $data['size'],
            $partner,
        );

        $method = $partner ? Parcel::PAYMENT_PARTNER : $data['payment_method'];

        try {
            $parcel = DB::transaction(function () use ($data, $senderType, $customer, $restaurant, $partner, $user, $quote, $method) {
                $parcel = new Parcel([
                    'reference' => Parcel::generateReference(),
                    'tracking_token' => Parcel::generateTrackingToken(),
                    'sender_type' => $senderType,
                    'customer_id' => $customer?->id,
                    'restaurant_id' => $restaurant?->id,
                    'delivery_partner_id' => $partner?->id,
                    'created_by_user_id' => $user?->id,
                    'external_reference' => $partner ? ($data['external_reference'] ?? null) : null,
                    'pickup_contact' => $partner ? ($data['pickup_contact'] ?? null) : null,
                    'delivery_city_id' => $quote['city']->id,
                    'city' => $quote['city']->name,
                    'pickup_address' => $data['pickup_address'],
                    'pickup_latitude' => $data['pickup_lat'],
                    'pickup_longitude' => $data['pickup_lng'],
                    'dropoff_address' => $data['dropoff_address'],
                    'dropoff_latitude' => $data['dropoff_lat'],
                    'dropoff_longitude' => $data['dropoff_lng'],
                    'sender_name' => $data['sender_name'],
                    'sender_phone' => $data['sender_phone'],
                    'recipient_name' => $data['recipient_name'],
                    'recipient_phone' => $data['recipient_phone'],
                    'size' => $data['size'],
                    'description' => $data['description'],
                    'note' => $data['note'] ?? null,
                    'distance_km' => $quote['distance_km'],
                    'price' => $quote['price'],
                    'driver_share' => $quote['driver_share'],
                    'platform_commission' => $quote['platform_commission'],
                    'pricing_breakdown' => $quote['breakdown'],
                    'payment_method' => $method,
                    'payment_status' => $method === Parcel::PAYMENT_PARTNER ? Parcel::PAY_BILLED : Parcel::PAY_PENDING,
                    'jeko_operator' => $method === Parcel::PAYMENT_JEKO ? ($data['jeko_operator'] ?? 'wave') : null,
                    'handover_code' => Parcel::generateHandoverCode(),
                ]);
                $parcel->status = ParcelStatus::PENDING_PAYMENT;

                // Espèces (payées au ramassage) et partenaires (facturés) : recherche immédiate
                if ($method !== Parcel::PAYMENT_JEKO) {
                    $parcel->transitionTo(ParcelStatus::SEARCHING_DRIVER);
                }

                $parcel->save();

                return $parcel;
            });
        } catch (UniqueConstraintViolationException $e) {
            // Deux requêtes simultanées avec la même référence partenaire
            if ($partner && !empty($data['external_reference'])
                && ($existing = $this->findPartnerParcel($partner, $data['external_reference']))) {
                return [$existing, false];
            }
            throw $e;
        }

        if ($parcel->status === ParcelStatus::SEARCHING_DRIVER) {
            $this->startDriverSearch($parcel);
        }

        $this->emit($parcel, null);

        return [$parcel, true];
    }

    private function findPartnerParcel(DeliveryPartner $partner, string $externalReference): ?Parcel
    {
        return Parcel::where('delivery_partner_id', $partner->id)
            ->where('external_reference', $externalReference)
            ->first();
    }

    /**
     * Crée la demande de paiement Jeko (compte plateforme MenuPro, comme les abonnements).
     *
     * @return array{payment_url: string, payment_id: string}
     *
     * @throws ParcelException
     */
    public function initiateJekoPayment(Parcel $parcel): array
    {
        if (!$parcel->isJeko() || $parcel->status !== ParcelStatus::PENDING_PAYMENT || $parcel->payment_status === Parcel::PAY_PAID) {
            throw new ParcelException('Ce colis n\'attend pas de paiement Mobile Money.', 'payment_not_expected');
        }

        $gateway = app(JekoGateway::class)->forMarketplacePlatform();

        if (!$gateway->isConfigured()) {
            throw new ParcelException('Le paiement Mobile Money est momentanément indisponible. Choisissez le paiement en espèces.', 'payment_unavailable', 503);
        }

        $return = fn (string $result) => route('api.v1.parcels.payment.return', ['token' => $parcel->tracking_token, 'result' => $result]);
        $result = $gateway->createPayment($parcel, $return('success'), $return('error'), $parcel->jeko_operator ?: 'wave');

        if (!($result['success'] ?? false)) {
            Log::channel('payments')->error('Colis : création du paiement Jeko impossible', [
                'parcel_id' => $parcel->id,
                'error' => $result['error'] ?? null,
            ]);
            throw new ParcelException('Impossible de lancer le paiement Mobile Money. Réessayez dans un instant.', 'payment_init_failed', 502);
        }

        $parcel->forceFill([
            'payment_link_id' => $result['payment_id'],
            'payment_reference' => $result['payment_id'],
            'payment_status' => Parcel::PAY_PENDING,
        ])->save();

        return ['payment_url' => $result['payment_url'], 'payment_id' => $result['payment_id']];
    }

    /**
     * Paiement Jeko confirmé (webhook signé). À appeler dans une transaction, colis verrouillé.
     */
    public function markPaid(Parcel $parcel, ?string $transactionId): bool
    {
        if ($parcel->payment_status === Parcel::PAY_PAID || $parcel->payment_status === Parcel::PAY_REFUNDED) {
            return false;
        }

        $parcel->forceFill([
            'payment_status' => Parcel::PAY_PAID,
            'paid_at' => now(),
            'payment_reference' => $transactionId ?: $parcel->payment_reference,
        ]);

        $search = false;
        if ($parcel->status === ParcelStatus::PENDING_PAYMENT) {
            $parcel->transitionTo(ParcelStatus::SEARCHING_DRIVER);
            $search = true;
        } elseif ($parcel->status === ParcelStatus::CANCELLED) {
            // Payé après annulation (expiration du délai de paiement) : à rembourser
            $parcel->refund_status = 'pending';
        }

        $parcel->save();

        $this->ledger->writeAfterCommit(fn () => $this->ledger->parcelPaymentEntry($parcel));

        if ($search) {
            $this->startDriverSearch($parcel);
            $this->emit($parcel, null);
        }

        if ($parcel->refund_status === 'pending') {
            $this->alertAdmins($parcel, ParcelAdminAlertNotification::REFUND_REQUIRED);
        }

        return true;
    }

    /** Paiement Jeko refusé : le colis reste en attente, l'expéditeur peut relancer. */
    public function markPaymentFailed(Parcel $parcel): void
    {
        if ($parcel->payment_status === Parcel::PAY_PENDING) {
            $parcel->forceFill(['payment_status' => Parcel::PAY_FAILED])->save();
        }
    }

    // =========================================================================
    // LIVREUR
    // =========================================================================

    /** @throws ParcelException */
    public function accept(string $reference, DeliveryDriver $driver): Parcel
    {
        $parcel = DB::transaction(function () use ($reference, $driver) {
            // Verrou livreur : il ne peut pas prendre deux courses en même temps
            $driver = DeliveryDriver::whereKey($driver->id)->lockForUpdate()->first();

            if (!$driver->is_available) {
                throw new ParcelException('Passez en ligne pour accepter une course.', 'driver_offline');
            }
            if ($driver->hasActiveCourse()) {
                throw new ParcelException('Vous avez déjà une course en cours.', 'driver_busy');
            }

            // Verrou colis : un seul livreur peut le prendre
            $parcel = Parcel::where('reference', $reference)->lockForUpdate()->first();

            if (!$parcel) {
                throw new ParcelException('Colis introuvable.', 'not_found', 404);
            }
            if ($parcel->status !== ParcelStatus::SEARCHING_DRIVER) {
                throw new ParcelException('Ce colis a déjà été pris par un autre livreur ou n\'est plus disponible.', 'already_taken', 409);
            }
            if ($driver->city && mb_strtolower(trim($driver->city)) !== mb_strtolower(trim($parcel->city))) {
                throw new ParcelException('Ce colis n\'est pas dans votre ville.', 'wrong_city', 403);
            }

            $parcel->transitionTo(ParcelStatus::ASSIGNED);
            $parcel->driver_id = $driver->id;
            $parcel->save();

            $driver->update(['is_available' => false]);

            return $parcel;
        });

        $this->emit($parcel, ParcelSenderNotification::DRIVER_FOUND);

        return $parcel;
    }

    /**
     * Ramassage : photo obligatoire ; en espèces, le livreur confirme avoir reçu le prix.
     *
     * @throws ParcelException
     */
    public function pickup(string $reference, DeliveryDriver $driver, UploadedFile $photo, bool $cashReceived): Parcel
    {
        $this->driverParcel($reference, $driver); // contrôle d'accès avant d'écrire la photo

        $path = $photo->store('parcel-pickups/' . now()->format('Y/m'), 'public');

        try {
            $parcel = DB::transaction(function () use ($reference, $driver, $path, $cashReceived) {
                $parcel = $this->driverParcel($reference, $driver, lock: true);
                $this->assertTransition($parcel, ParcelStatus::PICKED_UP, 'marqué comme ramassé');

                if ($parcel->isCash() && !$cashReceived) {
                    throw new ParcelException(
                        'Confirmez avoir reçu ' . number_format($parcel->price, 0, ',', ' ') . ' F en espèces de l\'expéditeur.',
                        'cash_not_confirmed',
                    );
                }

                $parcel->transitionTo(ParcelStatus::PICKED_UP);
                $parcel->pickup_photo_path = $path;

                if ($parcel->isCash()) {
                    $parcel->forceFill([
                        'payment_status' => Parcel::PAY_PAID,
                        'paid_at' => now(),
                        'cash_collected' => true,
                        'cash_collected_at' => now(),
                    ]);

                    // Le livreur garde sa part : il doit à MenuPro la commission (suivi espèces existant)
                    if ($parcel->platform_commission > 0) {
                        DriverCashDebt::create([
                            'driver_id' => $driver->id,
                            'parcel_id' => $parcel->id,
                            'amount_xof' => $parcel->platform_commission,
                            'status' => 'pending',
                        ]);
                    }
                }

                $parcel->save();

                if ($parcel->isCash()) {
                    $this->ledger->writeAfterCommit(fn () => $this->ledger->parcelPaymentEntry($parcel));
                }

                return $parcel;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        $this->emit($parcel, ParcelSenderNotification::PICKED_UP);

        return $parcel;
    }

    /** @throws ParcelException */
    public function startDelivery(string $reference, DeliveryDriver $driver): Parcel
    {
        $parcel = DB::transaction(function () use ($reference, $driver) {
            $parcel = $this->driverParcel($reference, $driver, lock: true);
            $this->assertTransition($parcel, ParcelStatus::DELIVERING, 'mis en livraison');
            $parcel->transitionTo(ParcelStatus::DELIVERING);
            $parcel->save();

            return $parcel;
        });

        $this->emit($parcel, ParcelSenderNotification::DELIVERING);

        return $parcel;
    }

    /**
     * Remise au destinataire avec le code à 4 chiffres. 5 essais, puis blocage + alerte admin.
     *
     * @throws ParcelException
     */
    public function deliver(string $reference, DeliveryDriver $driver, string $code): Parcel
    {
        $max = (int) config('parcels.handover_max_attempts', 5);

        // Les essais ratés sont enregistrés (commit) AVANT de renvoyer l'erreur
        $result = DB::transaction(function () use ($reference, $driver, $code, $max) {
            $parcel = $this->driverParcel($reference, $driver, lock: true);
            $this->assertTransition($parcel, ParcelStatus::DELIVERED, 'marqué comme livré');

            if ($parcel->isHandoverLocked()) {
                return ['parcel' => $parcel, 'error' => 'locked'];
            }

            if (!$parcel->checkHandoverCode($code)) {
                $parcel->handover_attempts++;
                $justLocked = $parcel->handover_attempts >= $max;
                if ($justLocked) {
                    $parcel->handover_locked_at = now();
                }
                $parcel->save();

                return ['parcel' => $parcel, 'error' => $justLocked ? 'just_locked' : 'invalid'];
            }

            $parcel->transitionTo(ParcelStatus::DELIVERED);
            $parcel->save();

            $this->creditDriver($parcel, $driver);

            if ($parcel->payment_method === Parcel::PAYMENT_PARTNER) {
                $this->ledger->writeAfterCommit(fn () => $this->ledger->parcelPaymentEntry($parcel));
            }

            return ['parcel' => $parcel, 'error' => null];
        });

        $parcel = $result['parcel'];

        if ($result['error'] === 'just_locked') {
            $this->alertAdmins($parcel, ParcelAdminAlertNotification::CODE_LOCKED);
        }

        if (in_array($result['error'], ['locked', 'just_locked'], true)) {
            throw new ParcelException(
                'Code bloqué après ' . $max . ' essais. MenuPro a été alerté et va vous contacter.',
                'code_locked',
                423,
            );
        }

        if ($result['error'] === 'invalid') {
            $left = $max - $parcel->handover_attempts;
            throw new ParcelException(
                "Code incorrect. Il vous reste {$left} essai" . ($left > 1 ? 's' : '') . '.',
                'invalid_code',
                422,
                ['attempts_left' => $left],
            );
        }

        $this->emit($parcel, ParcelSenderNotification::DELIVERED);

        return $parcel;
    }

    /**
     * Échec signalé par le livreur.
     *  - avant ramassage (assigned) : le livreur se désiste, le colis repart en recherche ;
     *  - après ramassage : échec de livraison (failed), le colis doit être rendu, admin alerté.
     *
     * @throws ParcelException
     */
    public function fail(string $reference, DeliveryDriver $driver, string $reason): Parcel
    {
        $parcel = DB::transaction(function () use ($reference, $driver, $reason) {
            $parcel = $this->driverParcel($reference, $driver, lock: true);

            if ($parcel->status === ParcelStatus::ASSIGNED) {
                $parcel->transitionTo(ParcelStatus::SEARCHING_DRIVER);
                $parcel->forceFill([
                    'driver_id' => null,
                    'assigned_at' => null,
                    'driver_search_started_at' => now(),
                    'dispatch_attempts' => 0,
                    'no_driver_alerted_at' => null,
                ])->save();

                DeliveryDriver::whereKey($driver->id)->update(['is_available' => true]);
                DeliveryDriver::whereKey($driver->id)->increment('total_cancelled');

                Log::info('Colis : désistement livreur', ['parcel' => $parcel->reference, 'driver_id' => $driver->id, 'reason' => $reason]);

                return $parcel;
            }

            $this->assertTransition($parcel, ParcelStatus::FAILED, 'déclaré en échec');
            $parcel->transitionTo(ParcelStatus::FAILED);
            $parcel->failure_reason = $reason;
            $parcel->save();

            DeliveryDriver::whereKey($driver->id)->update(['is_available' => true]);

            return $parcel;
        });

        if ($parcel->status === ParcelStatus::SEARCHING_DRIVER) {
            $this->startDriverSearch($parcel);
            $this->emit($parcel, null);
        } else {
            $this->alertAdmins($parcel, ParcelAdminAlertNotification::FAILED);
            $this->emit($parcel, ParcelSenderNotification::FAILED);
        }

        return $parcel;
    }

    // =========================================================================
    // ANNULATION / ADMIN
    // =========================================================================

    /**
     * @param string $by sender | admin | system
     *
     * @throws ParcelException
     */
    public function cancel(Parcel $parcel, string $by, string $reason): Parcel
    {
        $driverId = null;

        $parcel = DB::transaction(function () use ($parcel, $by, $reason, &$driverId) {
            $parcel = Parcel::whereKey($parcel->id)->lockForUpdate()->first();

            if ($by !== 'admin' && !$parcel->status->isCancellableBySender()) {
                throw new ParcelException(
                    $parcel->status->isFinal()
                        ? 'Ce colis est déjà ' . mb_strtolower($parcel->status->label()) . '.'
                        : 'Le colis a déjà été ramassé : il ne peut plus être annulé.',
                    'cannot_cancel',
                );
            }

            if (!$parcel->transitionTo(ParcelStatus::CANCELLED)) {
                throw new ParcelException('Ce colis ne peut plus être annulé.', 'cannot_cancel');
            }

            $driverId = $parcel->driver_id;
            $parcel->cancelled_by = $by;
            $parcel->cancellation_reason = $reason;

            if ($parcel->isJeko() && $parcel->payment_status === Parcel::PAY_PAID) {
                $parcel->refund_status = 'pending';
            }

            $parcel->save();

            if ($driverId) {
                DeliveryDriver::whereKey($driverId)->update(['is_available' => true]);
            }

            return $parcel;
        });

        if ($parcel->refund_status === 'pending') {
            $this->alertAdmins($parcel, ParcelAdminAlertNotification::REFUND_REQUIRED);
        }

        if ($driverId && ($driver = DeliveryDriver::find($driverId))) {
            $this->safely(fn () => $driver->notify(new ParcelDriverNotification($parcel, ParcelDriverNotification::CANCELLED)));
        }

        $this->emit($parcel, $by === 'sender' ? null : ParcelSenderNotification::CANCELLED);

        return $parcel;
    }

    /** Remboursement Jeko effectué manuellement par l'admin. */
    public function markRefunded(Parcel $parcel): Parcel
    {
        return DB::transaction(function () use ($parcel) {
            $parcel = Parcel::whereKey($parcel->id)->lockForUpdate()->first();

            if ($parcel->refund_status !== 'pending') {
                throw new ParcelException('Aucun remboursement en attente pour ce colis.', 'no_refund_pending');
            }

            $parcel->forceFill([
                'refund_status' => 'refunded',
                'payment_status' => Parcel::PAY_REFUNDED,
                'refunded_at' => now(),
            ])->save();

            $this->ledger->updateStatus(LedgerService::parcelPaymentKey($parcel->id), LedgerEntry::STATUS_REFUNDED);
            $this->ledger->writeAfterCommit(fn () => $this->ledger->parcelRefundEntry($parcel));

            return $parcel;
        });
    }

    public function unlockHandover(Parcel $parcel): void
    {
        $parcel->forceFill(['handover_attempts' => 0, 'handover_locked_at' => null])->save();
    }

    /** Commission d'un colis payé en espèces reçue du livreur : la dette est soldée. */
    public function settleCashDebt(Parcel $parcel, ?User $admin = null): bool
    {
        return DB::transaction(function () use ($parcel, $admin) {
            $debt = DriverCashDebt::where('parcel_id', $parcel->id)->where('status', 'pending')->lockForUpdate()->first();
            if (!$debt) {
                return false;
            }

            $debt->update(['status' => 'settled', 'settled_at' => now()]);
            DriverCashRemittance::where('debt_id', $debt->id)->where('status', 'pending')->update([
                'status' => 'confirmed',
                'confirmed_by' => $admin?->id,
                'confirmed_at' => now(),
            ]);

            return true;
        });
    }

    // =========================================================================
    // DISPATCH
    // =========================================================================

    public function startDriverSearch(Parcel $parcel): void
    {
        DispatchParcelJob::dispatch($parcel->id)->afterCommit();
    }

    // =========================================================================
    // OUTILS
    // =========================================================================

    /** @throws ParcelException */
    public function driverParcel(string $reference, DeliveryDriver $driver, bool $lock = false): Parcel
    {
        $query = Parcel::where('reference', $reference);
        if ($lock) {
            $query->lockForUpdate();
        }
        $parcel = $query->first();

        if (!$parcel) {
            throw new ParcelException('Colis introuvable.', 'not_found', 404);
        }
        if ($parcel->driver_id !== $driver->id) {
            throw new ParcelException('Ce colis ne vous est pas attribué.', 'not_your_parcel', 403);
        }

        return $parcel;
    }

    /** @throws ParcelException */
    private function assertTransition(Parcel $parcel, ParcelStatus $next, string $action): void
    {
        if (!$parcel->status->canTransitionTo($next)) {
            throw new ParcelException(
                "Le colis ne peut pas être {$action} : il est actuellement « " . mb_strtolower($parcel->status->label()) . ' ».',
                'invalid_transition',
                422,
                ['current_status' => $parcel->status->value],
            );
        }
    }

    /** Gains du livreur (même table et même logique que les repas). */
    private function creditDriver(Parcel $parcel, DeliveryDriver $driver): void
    {
        $cash = $parcel->isCash();

        if (!DriverEarning::where('parcel_id', $parcel->id)->exists()) {
            DriverEarning::create([
                'driver_id' => $driver->id,
                'parcel_id' => $parcel->id,
                'gross_amount' => $parcel->price,
                'platform_cut' => $parcel->platform_commission,
                'net_amount' => $parcel->driver_share,
                // Espèces : le livreur a déjà sa part en main (pas de virement à lui faire)
                'status' => $cash ? 'paid' : 'available',
                'paid_at' => $cash ? now() : null,
                'payment_method' => $cash ? 'cash' : null,
            ]);
        }

        DeliveryDriver::whereKey($driver->id)->update(['is_available' => true]);
        DeliveryDriver::whereKey($driver->id)->increment('total_deliveries');
        DeliveryDriver::whereKey($driver->id)->increment('total_earnings_xof', $parcel->driver_share);
    }

    /**
     * Après validation : push à l'expéditeur (client ou commerce) et webhook au partenaire.
     */
    public function emit(Parcel $parcel, ?string $senderEvent): void
    {
        DB::afterCommit(function () use ($parcel, $senderEvent) {
            $parcel->loadMissing(['driver', 'customer', 'restaurant', 'partner']);

            if ($senderEvent) {
                $this->safely(function () use ($parcel, $senderEvent) {
                    $notification = new ParcelSenderNotification($parcel, $senderEvent);
                    if ($parcel->customer) {
                        $parcel->customer->notify($notification);
                    } elseif ($parcel->restaurant) {
                        Notification::send($parcel->restaurant->staffToAlert(), $notification);
                    }
                });
            }

            if ($parcel->partner?->webhook_url && $parcel->partner->is_active) {
                $this->safely(fn () => SendPartnerWebhookJob::dispatch(
                    $parcel->partner->id,
                    $parcel->id,
                    'parcel.status_changed',
                    [
                        'event' => 'parcel.status_changed',
                        'sent_at' => now()->toIso8601String(),
                        'data' => ParcelPresenter::forPartner($parcel),
                    ],
                ));
            }
        });
    }

    public function alertAdmins(Parcel $parcel, string $alert): void
    {
        DB::afterCommit(fn () => $this->safely(fn () => Notification::send(
            User::superAdmins()->get(),
            new ParcelAdminAlertNotification($parcel->loadMissing('driver'), $alert),
        )));
    }

    private function safely(callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            Log::warning('Colis : notification impossible', ['error' => $e->getMessage()]);
        }
    }
}

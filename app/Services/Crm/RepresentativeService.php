<?php

namespace App\Services\Crm;

use App\Enums\Crm\AgentStatus;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Crm\RepresentativeProfile;
use App\Models\Crm\RepresentativeStatement;
use App\Models\Crm\Wallet;
use App\Models\LedgerEntry;
use App\Models\PartnerApplication;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Programme « Représentant de ville ».
 *
 * Réutilise le module CRM existant : rôle CRM dédié (representative), accès par le
 * middleware crm.role, portefeuille crm_wallets, commissions crm_commissions (type
 * representative_share, créditées par CommissionEngine) et retraits crm_withdrawals
 * (validés par l'écran « Retraits » du CRM). Le lien de parrainage réutilise le
 * mécanisme ?ref= / ref_agent de l'inscription restaurant.
 *
 * Rémunération mensuelle, calculée depuis le journal des transactions (ledger_entries) :
 *   - taux abonnement × abonnements encaissés (gross_amount) des restaurants attribués ;
 *   - taux livraison × commission MenuPro (menupro_commission) des commandes app client
 *     en livraison effectivement livrées (commande « completed »).
 * Seules les lignes réussies ou reversées comptent (remboursées / échouées exclues),
 * hors compte démo. Un restaurant suspendu ne rapporte rien. Pour un restaurant attribué
 * par la seule exclusivité de ville, seules les lignes postérieures à l'octroi comptent.
 */
class RepresentativeService
{
    public const SETTLED = [LedgerEntry::STATUS_SUCCEEDED, LedgerEntry::STATUS_PAID_OUT];

    public function __construct(private CommissionEngine $commissions) {}

    // =========================================================================
    // CRÉATION DEPUIS UNE CANDIDATURE
    // =========================================================================

    /**
     * Crée le compte représentant (utilisateur CRM + profil) depuis une candidature acceptée.
     *
     * @return array{user: User, profile: RepresentativeProfile, password: string}
     */
    public function createFromApplication(PartnerApplication $application, array $data = []): array
    {
        if (!$application->isAccepted()) {
            throw ValidationException::withMessages(['application' => 'La candidature doit être acceptée avant de créer le représentant.']);
        }
        if ($application->user_id) {
            throw ValidationException::withMessages(['application' => 'Un représentant a déjà été créé pour cette candidature.']);
        }

        $city = trim($data['city'] ?? $application->city);
        $email = $application->email
            ?: 'rep.' . Str::slug($application->full_name) . '.' . preg_replace('/\D/', '', $application->phone) . '@partenaires.menupro.ci';

        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => "Un compte existe déjà avec l'adresse {$email}."]);
        }

        $exclusive = (bool) ($data['exclusivity_active'] ?? false);
        if ($exclusive) {
            $this->assertCityExclusivityFree($city);
        }

        $password = Str::password(10, symbols: false);
        $config = config('crm.representatives');

        return DB::transaction(function () use ($application, $data, $city, $email, $exclusive, $password, $config) {
            // role et restaurant_id sont gardés ($guarded) : assignés après create()
            $user = User::create([
                'name' => $application->full_name,
                'email' => $email,
                'phone' => $application->phone,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'is_active' => true,
                'agent_status' => AgentStatus::ACTIF,
            ]);
            $user->role = UserRole::REPRESENTATIVE;
            $user->save();

            $profile = RepresentativeProfile::create([
                'user_id' => $user->id,
                'partner_application_id' => $application->id,
                'city' => $city,
                'exclusivity_active' => $exclusive,
                'exclusivity_granted_at' => $exclusive ? now() : null,
                'subscription_rate' => $data['subscription_rate'] ?? $config['subscription_rate_percent'],
                'delivery_rate' => $data['delivery_rate'] ?? $config['delivery_rate_percent'],
                'trial_ends_at' => $data['trial_ends_at'] ?? now()->addMonthsNoOverflow($config['trial_months'])->toDateString(),
                'quarterly_target' => $data['quarterly_target'] ?? $config['default_quarterly_target'],
            ]);

            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance_cents' => 0, 'total_earned_cents' => 0, 'total_withdrawn_cents' => 0]
            );

            $application->update(['user_id' => $user->id]);

            return ['user' => $user, 'profile' => $profile, 'password' => $password];
        });
    }

    /**
     * Met à jour les réglages d'un représentant (taux, essai, objectif, exclusivité).
     */
    public function updateSettings(RepresentativeProfile $profile, array $data): RepresentativeProfile
    {
        $city = trim($data['city'] ?? $profile->city);
        $exclusive = (bool) ($data['exclusivity_active'] ?? $profile->exclusivity_active);

        if ($exclusive) {
            $this->assertCityExclusivityFree($city, $profile->id);
        }

        $cityChanged = RepresentativeProfile::normalizeCity($city) !== RepresentativeProfile::normalizeCity($profile->city);

        $profile->fill([
            'city' => $city,
            'subscription_rate' => $data['subscription_rate'] ?? $profile->subscription_rate,
            'delivery_rate' => $data['delivery_rate'] ?? $profile->delivery_rate,
            'trial_ends_at' => array_key_exists('trial_ends_at', $data) ? $data['trial_ends_at'] : $profile->trial_ends_at,
            'quarterly_target' => $data['quarterly_target'] ?? $profile->quarterly_target,
            'exclusivity_active' => $exclusive,
        ]);

        // Date d'octroi : posée à l'activation (ou au changement de ville), effacée au retrait.
        if ($exclusive && (!$profile->exclusivity_granted_at || $cityChanged || $profile->isDirty('exclusivity_active'))) {
            $profile->exclusivity_granted_at = now();
        } elseif (!$exclusive) {
            $profile->exclusivity_granted_at = null;
        }

        $profile->save();

        return $profile;
    }

    private function assertCityExclusivityFree(string $city, ?int $exceptProfileId = null): void
    {
        $taken = RepresentativeProfile::query()
            ->where('exclusivity_active', true)
            ->whereRaw('LOWER(TRIM(city)) = ?', [RepresentativeProfile::normalizeCity($city)])
            ->when($exceptProfileId, fn ($q) => $q->where('id', '!=', $exceptProfileId))
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages(['exclusivity_active' => "Un autre représentant a déjà l'exclusivité sur {$city}."]);
        }
    }

    // =========================================================================
    // ATTRIBUTION
    // =========================================================================

    /**
     * Restaurants attribués, avec la source d'attribution (« lien » ou « ville »).
     */
    public function attributedRestaurants(RepresentativeProfile $profile): Collection
    {
        return $profile->attributedRestaurantsQuery()
            ->orderBy('name')
            ->get(['id', 'slug', 'name', 'city', 'status', 'is_demo', 'referred_by_user_id', 'created_at'])
            ->each(function ($restaurant) use ($profile) {
                $restaurant->attribution_source = $profile->isReferred($restaurant) ? 'lien' : 'ville';
            });
    }

    // =========================================================================
    // CALCUL MENSUEL
    // =========================================================================

    /**
     * Calcule la part d'un représentant pour un mois (AAAA-MM).
     *
     * Sans $persist : simple calcul (aperçu, --dry-run, espace représentant).
     * Avec $persist : enregistre le relevé (unique par mois) et crédite au wallet CRM
     * uniquement la différence entre la part due et ce qui a déjà été versé — relancer
     * le calcul ne double donc jamais un versement.
     *
     * @return array{statement: ?RepresentativeStatement, data: array, credited_cents: int}
     */
    public function computeMonth(RepresentativeProfile $profile, string $month, bool $persist = true): array
    {
        $data = $this->calculate($profile, $month);

        if (!$persist) {
            return ['statement' => null, 'data' => $data, 'credited_cents' => 0];
        }

        $creditedNow = 0;

        $statement = DB::transaction(function () use ($profile, $month, $data, &$creditedNow) {
            RepresentativeStatement::firstOrCreate(
                ['representative_profile_id' => $profile->id, 'month' => $month]
            );

            // Verrou : deux calculs simultanés du même mois ne peuvent pas créditer deux fois.
            $statement = RepresentativeStatement::where('representative_profile_id', $profile->id)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            $statement->fill([
                'restaurants_attributed' => $data['restaurants_attributed'],
                'restaurants_active' => $data['restaurants_active'],
                'restaurants_paying' => $data['restaurants_paying'],
                'subscription_collected' => $data['subscription_collected'],
                'delivery_commission' => $data['delivery_commission'],
                'subscription_rate' => $data['subscription_rate'],
                'delivery_rate' => $data['delivery_rate'],
                'subscription_share' => $data['subscription_share'],
                'delivery_share' => $data['delivery_share'],
                'total_share' => $data['total_share'],
                'details' => $data['restaurants'],
                'computed_at' => now(),
            ]);

            $dueCents = $data['total_share'] * 100;
            $deltaCents = $dueCents - (int) $statement->credited_cents;

            if ($deltaCents > 0 && $profile->user) {
                $commission = $this->commissions->creditRepresentativeShare($profile->user, $statement, $deltaCents);
                if ($commission) {
                    $statement->credited_cents = (int) $statement->credited_cents + $deltaCents;
                    $creditedNow = $deltaCents;
                }
            }

            $statement->save();

            return $statement;
        });

        return ['statement' => $statement, 'data' => $data, 'credited_cents' => $creditedNow];
    }

    /**
     * Calcul pur (aucune écriture) depuis le journal des transactions.
     */
    public function calculate(RepresentativeProfile $profile, string $month): array
    {
        $start = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00')->startOfMonth();
        $end = $start->endOfMonth();

        $restaurants = $this->attributedRestaurants($profile);
        $eligible = $restaurants->filter(fn ($r) => !$r->is_demo && $r->status !== RestaurantStatus::SUSPENDED);

        $sums = $this->ledgerSums($profile, $eligible, $start, $end);

        $subRate = (float) $profile->subscription_rate;
        $delRate = (float) $profile->delivery_rate;

        $lines = [];
        $subTotal = 0;
        $delTotal = 0;
        $paying = 0;

        foreach ($restaurants as $r) {
            $sub = (int) ($sums[$r->id]['subscription'] ?? 0);
            $del = (int) ($sums[$r->id]['delivery'] ?? 0);
            $subTotal += $sub;
            $delTotal += $del;
            if ($sub > 0) {
                $paying++;
            }

            $lines[] = [
                'restaurant_id' => $r->id,
                'slug' => $r->slug,
                'name' => $r->name,
                'city' => $r->city,
                'status' => $r->status?->value,
                'source' => $r->attribution_source,
                'subscription_collected' => $sub,
                'delivery_commission' => $del,
                'share' => $this->share($sub, $subRate) + $this->share($del, $delRate),
            ];
        }

        $subShare = $this->share($subTotal, $subRate);
        $delShare = $this->share($delTotal, $delRate);

        return [
            'month' => $month,
            'restaurants_attributed' => $restaurants->count(),
            'restaurants_active' => $restaurants->filter(fn ($r) => $r->status === RestaurantStatus::ACTIVE)->count(),
            'restaurants_paying' => $paying,
            'subscription_collected' => $subTotal,
            'delivery_commission' => $delTotal,
            'subscription_rate' => $subRate,
            'delivery_rate' => $delRate,
            'subscription_share' => $subShare,
            'delivery_share' => $delShare,
            'total_share' => $subShare + $delShare,
            'restaurants' => $lines,
        ];
    }

    /**
     * Sommes par restaurant : [restaurant_id => ['subscription' => F, 'delivery' => F]].
     */
    private function ledgerSums(RepresentativeProfile $profile, Collection $eligible, CarbonImmutable $start, CarbonImmutable $end, bool $subscriptionsOnly = false): array
    {
        if ($eligible->isEmpty()) {
            return [];
        }

        $referredIds = $eligible->where('attribution_source', 'lien')->pluck('id')->all();
        $cityIds = $eligible->where('attribution_source', 'ville')->pluck('id')->all();
        $grantedAt = $profile->exclusivity_granted_at;

        $base = LedgerEntry::query()
            ->real()
            ->whereIn('status', self::SETTLED)
            ->whereBetween('occurred_at', [$start, $end])
            ->where(function ($q) use ($referredIds, $cityIds, $grantedAt) {
                $q->whereIn('restaurant_id', $referredIds ?: [0]);
                if ($cityIds) {
                    $q->orWhere(function ($q) use ($cityIds, $grantedAt) {
                        $q->whereIn('restaurant_id', $cityIds);
                        if ($grantedAt) {
                            $q->where('occurred_at', '>=', $grantedAt);
                        }
                    });
                }
            });

        $sums = [];

        (clone $base)
            ->whereIn('type', LedgerEntry::SUBSCRIPTION_TYPES)
            ->groupBy('restaurant_id')
            ->selectRaw('restaurant_id, SUM(gross_amount) as total')
            ->get()
            ->each(function ($row) use (&$sums) {
                $sums[$row->restaurant_id]['subscription'] = (int) $row->total;
            });

        if (!$subscriptionsOnly) {
            (clone $base)
                ->where('type', LedgerEntry::TYPE_ORDER_APP_DELIVERY)
                ->whereExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('orders')
                        ->whereColumn('orders.id', 'ledger_entries.order_id')
                        ->where('orders.status', \App\Enums\OrderStatus::COMPLETED->value);
                })
                ->groupBy('restaurant_id')
                ->selectRaw('restaurant_id, SUM(menupro_commission) as total')
                ->get()
                ->each(function ($row) use (&$sums) {
                    $sums[$row->restaurant_id]['delivery'] = (int) $row->total;
                });
        }

        return $sums;
    }

    private function share(int $amount, float $rate): int
    {
        return $amount > 0 && $rate > 0 ? (int) round($amount * $rate / 100) : 0;
    }

    // =========================================================================
    // OBJECTIF TRIMESTRIEL
    // =========================================================================

    /**
     * Progression de l'objectif trimestriel : restaurants attribués ayant payé un
     * abonnement dans le trimestre.
     */
    public function quarterProgress(RepresentativeProfile $profile, ?\DateTimeInterface $date = null): array
    {
        $date = CarbonImmutable::instance($date ?? now());
        $start = $date->firstOfQuarter()->startOfDay();
        $end = $date->lastOfQuarter()->endOfDay();

        $eligible = $this->attributedRestaurants($profile)
            ->filter(fn ($r) => !$r->is_demo && $r->status !== RestaurantStatus::SUSPENDED);

        $sums = $this->ledgerSums($profile, $eligible, $start, $end, subscriptionsOnly: true);
        $achieved = collect($sums)->filter(fn ($s) => ($s['subscription'] ?? 0) > 0)->count();
        $target = max(0, (int) $profile->quarterly_target);

        return [
            'label' => 'T' . $date->quarter . ' ' . $date->year,
            'target' => $target,
            'achieved' => $achieved,
            'percent' => $target > 0 ? min(100, (int) round($achieved * 100 / $target)) : 0,
            'from' => $start,
            'to' => $end,
        ];
    }
}

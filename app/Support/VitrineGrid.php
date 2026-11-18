<?php

namespace App\Support;

use App\Models\Plan;
use Database\Seeders\PlanSeeder;
use Illuminate\Support\Collection;

/**
 * Grille d'abonnements affichée sur le site vitrine (accueil, tarifs, FAQ, inscription).
 *
 * Prix lus en base (grille appliquée par `plans:appliquer-grille`), repli sur PlanSeeder
 * (source de vérité) ; règles transverses lues dans config/plans.php. Jamais de prix en dur.
 */
class VitrineGrid
{
    protected static ?Collection $plans = null;

    /** Plans actifs de la grille, indexés par slug (stand, essentiel, pro, gold). */
    public static function plans(): Collection
    {
        if (static::$plans !== null && ! app()->runningUnitTests()) {
            return static::$plans;
        }

        try {
            $db = Plan::whereIn('slug', PlanSeeder::ACTIVE_SLUGS)->get()->keyBy('slug');
        } catch (\Throwable) {
            $db = collect();
        }

        return static::$plans = collect(PlanSeeder::definitions())
            ->only(PlanSeeder::ACTIVE_SLUGS)
            ->map(fn (array $def, string $slug) => $db[$slug] ?? new Plan($def));
    }

    public static function plan(string $slug): ?Plan
    {
        return static::plans()->get($slug);
    }

    public static function price(string $slug): int
    {
        return (int) (static::plan($slug)?->price ?? 0);
    }

    public static function weeklyPrice(string $slug = 'stand'): int
    {
        return (int) (static::plan($slug)?->weekly_price ?? 0);
    }

    /** Taux de commission (en %) appliqué aux commandes livrées pour un plan. */
    public static function commission(?string $slug = null): string
    {
        $c = config('plans.commission');
        $rate = $slug !== null && in_array($slug, $c['reduced_plans'] ?? [], true)
            ? $c['reduced_rate']
            : $c['default_rate'];

        return static::percent($rate);
    }

    public static function reducedCommission(): string
    {
        return static::percent(config('plans.commission.reduced_rate'));
    }

    /**
     * Prix de départ d'une offre de commerce (config('vitrine.offers')).
     *
     * @return array{amount: string, unit: string, monthly: string}
     */
    public static function offerFrom(string $offer): array
    {
        $o = config("vitrine.offers.{$offer}", []);
        $slug = $o['from_plan'] ?? 'essentiel';
        $weekly = ($o['from_weekly'] ?? false) && static::weeklyPrice($slug) > 0;

        return [
            'amount'  => static::money($weekly ? static::weeklyPrice($slug) : static::price($slug)),
            'unit'    => $weekly ? 'F/semaine' : 'F/mois',
            'monthly' => static::money(static::price($slug)),
        ];
    }

    public static function trialDays(): int
    {
        return (int) config('plans.trial_days', 30);
    }

    /** 15000 → « 15 000 » (espaces normales ; envelopper dans whitespace-nowrap à l'affichage). */
    public static function money(int|float|null $amount): string
    {
        return number_format((int) $amount, 0, ',', ' ');
    }

    protected static function percent(float|int $rate): string
    {
        return rtrim(rtrim(number_format((float) $rate, 1, ',', ''), '0'), ',') . ' %';
    }
}

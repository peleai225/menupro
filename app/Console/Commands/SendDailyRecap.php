<?php

namespace App\Console\Commands;

use App\Enums\RestaurantStatus;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\RevenueCalculator;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendDailyRecap extends Command
{
    protected $signature = 'recap:daily {--restaurant= : Send only to a specific restaurant ID}';

    protected $description = 'Send daily revenue recap via WhatsApp to all active restaurants';

    public function handle(WhatsAppService $whatsapp): int
    {
        $query = Restaurant::where('status', RestaurantStatus::ACTIVE)
            ->whereNotNull('phone');

        if ($id = $this->option('restaurant')) {
            $query->where('id', $id);
        }

        $restaurants = $query->get();
        $sent = 0;

        foreach ($restaurants as $restaurant) {
            $calc = RevenueCalculator::for(
                $restaurant->id,
                today()->startOfDay(),
                now()
            );

            $summary = $calc->summary();

            if ($summary['valid_orders_count'] === 0) {
                continue;
            }

            $yesterdayCalc = RevenueCalculator::for(
                $restaurant->id,
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay()
            );
            $yesterdayRevenue = $yesterdayCalc->grossRevenue();

            $changePercent = $yesterdayRevenue > 0
                ? round((($summary['gross_revenue'] - $yesterdayRevenue) / $yesterdayRevenue) * 100)
                : 0;
            $changeLabel = $changePercent > 0 ? "+{$changePercent}%" : "{$changePercent}%";
            $changeArrow = $changePercent >= 0 ? '↑' : '↓';

            $topProduct = $calc->topProducts(1)->first();
            $topLabel = $topProduct ? "{$topProduct->dish_name} ({$topProduct->total_sold}x)" : '—';

            $stockAlerts = $restaurant->ingredients()
                ->whereColumn('current_quantity', '<=', 'min_quantity')
                ->where('min_quantity', '>', 0)
                ->limit(3)
                ->get();

            $stockLines = $stockAlerts->isEmpty()
                ? ''
                : "\n⚠️ *Stock bas :* " . $stockAlerts->map(fn ($i) => "{$i->name} ({$i->current_quantity} {$i->unit})")->implode(', ');

            $message = "📊 *Résumé du jour — {$restaurant->name}*\n"
                . "━━━━━━━━━━━━━━━━━\n"
                . "💰 CA : " . number_format($summary['gross_revenue'], 0, ',', ' ') . " F"
                . ($changePercent !== 0 ? " ({$changeArrow}{$changeLabel} vs hier)" : '') . "\n"
                . "💵 Net (après commission) : " . number_format($summary['net_revenue'], 0, ',', ' ') . " F\n"
                . "📦 Commandes : {$summary['valid_orders_count']} validées\n"
                . "🎯 Panier moyen : " . number_format($summary['average_ticket'], 0, ',', ' ') . " F\n"
                . "🏆 Top : {$topLabel}"
                . $stockLines . "\n"
                . "━━━━━━━━━━━━━━━━━\n"
                . "À demain ! — MenuPro";

            if ($whatsapp->send($restaurant->phone, $message)) {
                $sent++;
            }
        }

        $this->info("Récapitulatif envoyé à {$sent} restaurant(s).");

        return self::SUCCESS;
    }
}

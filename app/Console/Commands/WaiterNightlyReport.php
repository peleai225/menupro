<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\WaiterDailyReport;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaiterNightlyReport extends Command
{
    protected $signature = 'waiter:nightly-report {--date= : Date to report on (default: today)}';

    protected $description = 'Generate and store nightly waiter performance report for GOLD restaurants';

    public function handle(WhatsAppService $whatsapp): int
    {
        $reportDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : Carbon::today();

        $this->info("Rapport serveurs pour le {$reportDate->format('d/m/Y')}");

        // Retrieve GOLD plan IDs (plans with multi-spaces feature)
        $goldPlanIds = Plan::where('has_multi_spaces', true)->pluck('id');

        if ($goldPlanIds->isEmpty()) {
            $this->warn('Aucun plan GOLD trouvé.');
            return self::SUCCESS;
        }

        $processed   = 0;
        $totalInserts = 0;

        Restaurant::whereIn('current_plan_id', $goldPlanIds)
            ->chunkById(50, function ($restaurants) use ($reportDate, $whatsapp, &$processed, &$totalInserts) {
                foreach ($restaurants as $restaurant) {
                    $inserts = $this->processRestaurant($restaurant, $reportDate, $whatsapp);
                    if ($inserts > 0) {
                        $processed++;
                        $totalInserts += $inserts;
                    }
                }
            });

        $this->info("Traité : {$processed} restaurant(s), {$totalInserts} rapport(s) serveur enregistré(s).");

        return self::SUCCESS;
    }

    private function processRestaurant(Restaurant $restaurant, Carbon $reportDate, WhatsAppService $whatsapp): int
    {
        // Aggregate orders by waiter for the day — skip null waiter_ids
        $rows = DB::table('orders')
            ->where('restaurant_id', $restaurant->id)
            ->whereNotNull('waiter_id')
            ->whereDate('created_at', $reportDate)
            ->groupBy('waiter_id')
            ->select([
                'waiter_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('ROUND(AVG(total)) as average_ticket'),
                DB::raw('MIN(TIME(created_at)) as first_order_at'),
                DB::raw('MAX(TIME(created_at)) as last_order_at'),
            ])
            ->get();

        if ($rows->isEmpty()) {
            return 0;
        }

        $inserts = 0;
        $reportLines = [];

        foreach ($rows as $row) {
            WaiterDailyReport::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'waiter_id'     => $row->waiter_id,
                    'report_date'   => $reportDate->toDateString(),
                ],
                [
                    'orders_count'   => (int) $row->orders_count,
                    'revenue'        => (int) $row->revenue,
                    'average_ticket' => (int) $row->average_ticket,
                    'first_order_at' => $row->first_order_at,
                    'last_order_at'  => $row->last_order_at,
                    'created_at'     => now(),
                ]
            );

            $inserts++;

            // Fetch waiter name for the WhatsApp message
            $waiterName = DB::table('waiters')
                ->where('id', $row->waiter_id)
                ->value('name') ?? "Serveur #{$row->waiter_id}";

            $revenueFormatted = number_format((int) $row->revenue, 0, ',', ' ');
            $reportLines[] = "{$waiterName}: {$row->orders_count} cmd, {$revenueFormatted} F";
        }

        // Send WhatsApp summary to the restaurant admin if a phone is available
        $adminPhone = $restaurant->phone;
        if ($adminPhone && ! empty($reportLines)) {
            $dateLabel = $reportDate->format('d/m');
            $linesStr  = implode(' | ', $reportLines);
            $message   = "Rapport serveurs {$dateLabel} — {$linesStr}";

            try {
                $whatsapp->send($adminPhone, $message);
            } catch (\Throwable $e) {
                Log::warning("waiter:nightly-report — WhatsApp échoué pour restaurant #{$restaurant->id} : " . $e->getMessage());
            }
        }

        return $inserts;
    }
}

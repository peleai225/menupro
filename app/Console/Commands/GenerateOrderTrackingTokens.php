<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class GenerateOrderTrackingTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:generate-tracking-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tracking tokens for existing orders that don\'t have one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating tracking tokens for existing orders...');

        $count = 0;
        Order::whereNull('tracking_token')->chunk(100, function ($orders) use (&$count) {
            foreach ($orders as $order) {
                $order->update([
                    'tracking_token' => Order::generateTrackingToken()
                ]);
                $count++;
            }
        });

        $this->info("Generated {$count} tracking tokens successfully!");
        
        return Command::SUCCESS;
    }
}

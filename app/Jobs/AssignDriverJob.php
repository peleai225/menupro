<?php

namespace App\Jobs;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Services\DriverAssignmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AssignDriverJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 180, 300]; // Retry après 1, 3, 5 minutes

    public function __construct(
        public int $deliveryId,
        public bool $isRetry = false,
    ) {}

    public function handle(DriverAssignmentService $driverAssignment): void
    {
        $delivery = Delivery::with(['order', 'restaurant'])->find($this->deliveryId);

        if (!$delivery) {
            Log::warning('AssignDriverJob: delivery not found', ['delivery_id' => $this->deliveryId]);
            return;
        }

        // Guard 1 : Vérifier que la commande n'est pas annulée/remboursée
        if ($delivery->order->status->isFinal()) {
            Log::info('AssignDriverJob: skipped, order is final', [
                'delivery_id' => $this->deliveryId,
                'order_status' => $delivery->order->status->value,
            ]);
            return;
        }

        // Guard 2 : Vérifier que le livreur n'est pas déjà assigné
        if ($delivery->status !== DeliveryStatus::PENDING) {
            Log::info('AssignDriverJob: skipped, already assigned', [
                'delivery_id' => $this->deliveryId,
                'delivery_status' => $delivery->status->value,
            ]);
            return;
        }

        // Tentative d'assignation
        $driver = $driverAssignment->assign($delivery);

        if (!$driver && !$this->isRetry) {
            // Aucun livreur disponible → Retry dans 3 minutes
            Log::warning('AssignDriverJob: no driver available, scheduling retry', [
                'delivery_id' => $this->deliveryId,
            ]);

            self::dispatch($this->deliveryId, isRetry: true)
                ->delay(now()->addMinutes(3));

            // TODO : Notifier restaurant qu'aucun livreur n'est disponible
            // NotifyRestaurant::dispatch($delivery, 'no_driver_available');
        } elseif (!$driver && $this->isRetry) {
            // Toujours aucun livreur après retry
            Log::error('AssignDriverJob: no driver available after retry', [
                'delivery_id' => $this->deliveryId,
            ]);

            // TODO : Alerte critique restaurant + support
        } else {
            Log::info('AssignDriverJob: driver assigned successfully', [
                'delivery_id' => $this->deliveryId,
                'driver_id' => $driver->id,
                'driver_name' => $driver->name,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('AssignDriverJob failed', [
            'delivery_id' => $this->deliveryId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}

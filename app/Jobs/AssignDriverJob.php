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
    public $backoff = [60, 120, 180];

    public function __construct(
        public int $deliveryId,
        public int $attempt = 1,
    ) {}

    public function handle(DriverAssignmentService $driverAssignment): void
    {
        $delivery = Delivery::with(['order', 'restaurant'])->find($this->deliveryId);

        if (!$delivery) {
            Log::warning('AssignDriverJob: delivery not found', ['delivery_id' => $this->deliveryId]);
            return;
        }

        if ($delivery->order->status->isFinal()) {
            Log::info('AssignDriverJob: skipped, order is final', [
                'delivery_id' => $this->deliveryId,
                'order_status' => $delivery->order->status->value,
            ]);
            return;
        }

        if ($delivery->status !== DeliveryStatus::PENDING) {
            Log::info('AssignDriverJob: skipped, already accepted', [
                'delivery_id' => $this->deliveryId,
                'delivery_status' => $delivery->status->value,
            ]);
            return;
        }

        $notifiedCount = $driverAssignment->notifyNearbyDrivers($delivery, $this->attempt);

        if ($notifiedCount === 0 && $this->attempt < 3) {
            Log::warning('AssignDriverJob: no drivers found, retrying with wider radius', [
                'delivery_id' => $this->deliveryId,
                'attempt' => $this->attempt,
            ]);

            self::dispatch($this->deliveryId, attempt: $this->attempt + 1)
                ->delay(now()->addMinutes(2));
        } elseif ($notifiedCount === 0) {
            Log::error('AssignDriverJob: no drivers available after all attempts', [
                'delivery_id' => $this->deliveryId,
            ]);
        } else {
            Log::info('AssignDriverJob: drivers notified', [
                'delivery_id' => $this->deliveryId,
                'notified_count' => $notifiedCount,
                'attempt' => $this->attempt,
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

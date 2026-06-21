<?php

namespace App\Jobs;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        protected Order $order,
        protected string $notificationType
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        if (!$this->order->customer_phone) {
            return;
        }

        try {
            if ($this->notificationType === 'confirmation') {
                $whatsAppService->sendOrderConfirmation($this->order);
            } else {
                $whatsAppService->sendOrderStatusUpdate($this->order);
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp job failed for order ' . $this->order->reference . ': ' . $e->getMessage());
            throw $e;
        }
    }
}

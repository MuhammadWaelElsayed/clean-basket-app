<?php

namespace App\Observers;

use App\Jobs\SendPartnerWebhook;
use App\Models\Order;
use App\Models\PartnerWebhook;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //        $this->sendWebhookNotification($order, 'order.created');
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (!$order->wasRecentlyCreated && $order->wasChanged('status')) {
            $this->sendWebhookNotification($order);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //        $this->sendWebhookNotification($order, 'order.deleted');
    }

    /**
     * Send webhook notification to all registered partner webhooks.
     */
    private function sendWebhookNotification(Order $order): void
    {
        $webhooks = PartnerWebhook::where('source_secret', $order->source_secret)->get();

        foreach ($webhooks as $webhook) {
            $payload = [
                'orderId' => (string) $order->id,
                'status' => $order->status,
                'statusDisplay' => $order->status_display,
            ];

            SendPartnerWebhook::dispatch($webhook, $payload);
        }
    }
}

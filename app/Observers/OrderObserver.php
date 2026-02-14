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
        if(!$order->wasRecentlyCreated)
        $this->sendWebhookNotification($order, 'order.updated');
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
    private function sendWebhookNotification(Order $order, string $event): void
    {
        $order->loadMissing(['items', 'vendor', 'driver', 'user']);
        // Get all active partner webhooks
        $webhooks = PartnerWebhook::where('source_secret', $order->source_secret)->get();

        foreach ($webhooks as $webhook) {
            // Prepare webhook payload
            $payload = [
                'status' => true,
                'event' => $event,
                'timestamp' => now()->timestamp,
                'data' => [
                    'order' => $order,
                ]
            ];

            // Dispatch job to send webhook
            SendPartnerWebhook::dispatchSync($webhook, $payload);
        }
    }
}

<?php

namespace App\Jobs;

use App\Models\PartnerWebhook;
use App\Models\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendPartnerWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min
    public $timeout = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PartnerWebhook $webhook,
        public array          $payload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            $response = $this->sendWebhook();

            $duration = round((microtime(true) - $startTime) * 1000);

            // Check response body for success (endpoint always returns HTTP 200)
            $body = json_decode($response->body(), true);
            $isSuccess = $response->successful() && ($body['success'] ?? true);

            $this->logWebhook(
                status: $isSuccess ? 'success' : 'failed',
                statusCode: $response->status(),
                responseBody: $response->body(),
                duration: $duration,
                errorMessage: $isSuccess ? null : ($body['message'] ?? 'Partner reported failure in response body')
            );

            Log::info('Partner webhook sent', [
                'webhook_id' => $this->webhook->id,
                'source_name' => $this->webhook->source_name,
                'status_code' => $response->status(),
                'body_success' => $isSuccess,
            ]);
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);

            $this->logWebhook(
                status: 'failed',
                statusCode: null,
                responseBody: null,
                duration: $duration,
                errorMessage: $e->getMessage()
            );

            Log::error('Partner webhook failed', [
                'webhook_id' => $this->webhook->id,
                'source_name' => $this->webhook->source_name,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Send the webhook request.
     */
    private function sendWebhook()
    {
        $apiKey = config("partner_webhooks.api_keys.{$this->webhook->source_name}");

        $headers = [
            'Content-Type' => 'application/json',
            'X-API-Key' => $apiKey,
        ];

        if ($this->webhook->method === 'POST') {
            return Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($this->webhook->url, $this->payload);
        }

        return Http::timeout($this->timeout)
            ->withHeaders($headers)
            ->get($this->webhook->url, $this->payload);
    }

    /**
     * Log webhook attempt.
     */
    private function logWebhook(
        string  $status,
        ?int    $statusCode,
        ?string $responseBody,
        int     $duration,
        ?string $errorMessage = null
    ): void {
        WebhookLog::create([
            'partner_webhook_id' => $this->webhook->id,
            'event' => 'status_update',
            'payload' => $this->payload,
            'status' => $status,
            'status_code' => $statusCode,
            'response_body' => $responseBody,
            'duration_ms' => $duration,
            'error_message' => $errorMessage,
            'attempt' => $this->attempts(),
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Partner webhook permanently failed', [
            'webhook_id' => $this->webhook->id,
            'source_name' => $this->webhook->source_name,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Log final failure
        if (class_exists(WebhookLog::class)) {
            WebhookLog::create([
                'partner_webhook_id' => $this->webhook->id,
                'event' => 'status_update',
                'payload' => $this->payload,
                'status' => 'permanently_failed',
                'error_message' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);
        }
    }
}

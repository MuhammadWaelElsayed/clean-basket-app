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
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            // Generate signature for security
            $signature = $this->generateSignature($this->payload, $this->webhook->source_secret);

            // Send webhook based on method
            $response = $this->sendWebhook($signature);

            $duration = round((microtime(true) - $startTime) * 1000); // in milliseconds

            // Log successful webhook
            $this->logWebhook(
                status: 'success',
                statusCode: $response->status(),
                responseBody: $response->body(),
                duration: $duration
            );

            Log::info('Partner webhook sent successfully', [
                'webhook_id' => $this->webhook->id,
                'source_name' => $this->webhook->source_name,
                'event' => $this->payload['event'] ?? null,
                'status_code' => $response->status(),
            ]);

        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000);

            // Log failed webhook
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

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Send the webhook request.
     */
    private function sendWebhook(string $signature)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Source' => $this->webhook->source_name,
            'User-Agent' => 'PartnerWebhook/1.0',
        ];

        if ($this->webhook->method === 'POST') {
            return Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($this->webhook->url, $this->payload);
        }

        // For GET requests, send payload as query parameters
        return Http::timeout($this->timeout)
            ->withHeaders($headers)
            ->get($this->webhook->url, $this->payload);
    }

    /**
     * Generate HMAC signature for webhook verification.
     */
    private function generateSignature(array $payload, string $secret): string
    {
        $payloadJson = json_encode($payload);
        return hash_hmac('sha256', $payloadJson, $secret);
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
    ): void
    {
        WebhookLog::create([
            'partner_webhook_id' => $this->webhook->id,
            'event' => $this->payload['event'] ?? null,
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
            'event' => $this->payload['event'] ?? null,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Log final failure
        if (class_exists(WebhookLog::class)) {
            WebhookLog::create([
                'partner_webhook_id' => $this->webhook->id,
                'event' => $this->payload['event'] ?? null,
                'payload' => $this->payload,
                'status' => 'permanently_failed',
                'error_message' => $exception->getMessage(),
                'attempt' => $this->attempts(),
            ]);
        }
    }
}

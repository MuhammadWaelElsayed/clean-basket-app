<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FazzaDeliveryService
{
    protected string $baseUrl;
    protected string $oauthUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $grantType;

    protected int $timeout;
    protected int $retries;
    protected int $retryDelayMs;

    // Cache keys
    protected string $cacheTokenKey  = 'fazza.access_token';
    protected string $cacheExpiryKey = 'fazza.access_token_expires_at';

    public function __construct()
    {
        $this->baseUrl      = rtrim(config('fazza.base_url'), '/');
        $this->oauthUrl     = (string) config('fazza.oauth_url');
        $this->clientId     = (string) config('fazza.client_id');
        $this->clientSecret = (string) config('fazza.client_secret');
        $this->grantType    = (string) config('fazza.grant_type', 'client_credentials');

        $this->timeout      = (int) config('fazza.timeout', 15);
        $this->retries      = (int) config('fazza.retries', 2);
        $this->retryDelayMs = (int) config('fazza.retry_delay_ms', 500);
    }

    /**
     * get access_token from cache or create a new one.
     */
    protected function getAccessToken(bool $forceRefresh = false): string
    {
        $payload = [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => $this->grantType,
        ];
        Log::info('Fazza OAuth: payload', ['payload' => $payload]);
        // most OAuth services expect x-www-form-urlencoded
        $resp = Http::asForm()
            ->acceptJson()
            ->timeout($this->timeout)
            ->post($this->oauthUrl, $payload);

        if (!$resp->successful()) {
            Log::error('Fazza OAuth failed', ['status' => $resp->status(), 'body' => $resp->body()]);
            throw new \RuntimeException('Fazza OAuth error ('.$resp->status().'): '.$resp->body(), $resp->status());
        }

        $data = $resp->json();
        $accessToken = $data['access_token'] ?? null;

        if (!$accessToken) {
            throw new \RuntimeException('Fazza OAuth: invalid token response: '.json_encode($data));
        }

        Log::info('Fazza OAuth: new token obtained', ['access_token' => $accessToken]);

        return $accessToken;
    }

    /**
     * build signed request with token.
     */
    protected function authedRequest(?string $token = null)
    {
        $token = $token ?: $this->getAccessToken();

        $req = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout);

        if ($this->retries > 0) {
            $req = $req->retry($this->retries, $this->retryDelayMs);
        }

        return $req;
    }

    /**
     * send order with automatic token refresh on 401 and retry once.
     */
    protected function sendWithAutoRefresh(callable $fn)
    {
        $token = $this->getAccessToken();
        $response = $fn($this->authedRequest($token));

        if ($response->status() === 401) {
            Log::warning('Fazza 401: refreshing token and retrying once');
            $token = $this->getAccessToken(true); // force refresh
            $response = $fn($this->authedRequest($token));
        }

        return $response;
    }

    /**
     * create order on Fazza platform.
     */
    public function createRideRequest(array $payload): array
    {
        // Validate سريع
        $validator = Validator::make($payload, [
            'id'         => 'required',
            'email'            => 'required|email',
            'service_id'       => 'required|integer',
            'phone'            => 'required|string',
            'user_Id'          => 'required|string',
            'start_latitude'   => 'required|numeric',
            'start_longitude'  => 'required|numeric',
            'start_address'    => 'required|string',
            'end_latitude'     => 'required|numeric',
            'end_longitude'    => 'required|numeric',
            'end_address'      => 'required|string',
            'payment_type'     => 'required|string',
            'payment_status'   => 'required|integer',
            'status'           => 'required|string',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $url = $this->baseUrl . '/api/provider/api/riderequest/store';

        Log::info('Fazza createRideRequest: sending', ['url' => $url, 'payload' => $payload]);

        $response = $this->sendWithAutoRefresh(function ($req) use ($url, $payload) {
            return $req->post($url, $payload);
        });

        if ($response->successful()) {
            $json = $response->json() ?? [];
            Log::info('Fazza createRideRequest: success', ['response' => $json]);
            return $json;
        }

        Log::error('Fazza createRideRequest: failed', [
            'status'  => $response->status(),
            'body'    => $response->body(),
            'headers' => $response->headers(),
        ]);

        throw new \RuntimeException('Fazza API error ('.$response->status().'): '.$response->body(), $response->status());
    }
}

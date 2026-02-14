<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $client;
    protected $baseUrl;
    protected $token;
    protected $sender;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = env('SMS_BASE_URL');
        $this->token = env('SMS_TOKEN');
        $this->sender = env('SMS_SENDER');
    }

    public function sendSms($phoneNumber, $message)
    {
        if (empty($this->baseUrl)) {
            Log::info('SMS_BASE_URL is not defined in .env file');
            return [
                'success' => false,
                'error' => 'SMS_BASE_URL is not defined in .env file',
            ];
        }

        try {
            $response = $this->client->post($this->baseUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'number' => $phoneNumber,
                    'senderName' => $this->sender,
                    'sendAtOption' => 'Now',
                    'messageBody' => $message,
                ],
            ]);
            Log::info('response from sms service', ['response' => (string) $response->getBody()]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::info('error from sms service', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StatusSmsWhatsappService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.status_sms.base_url');
        $this->token   = config('services.status_sms.token');
    }

    public function send($endpoint,$name , $phone)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ])->post($this->baseUrl . $endpoint, [
            [
                'phone' => $phone,
                'name' => $name,
            ],
        ]);

        return $response->json();
    }

    public function sendWithParams($endpoint, $name, $phone, $link)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ])->post($this->baseUrl . $endpoint, [
            [
                'name' => $name,
                'phone' => $phone,
                'link' => $link,
            ],
        ]);

        return $response->json();
    }

    public function pickupOrder($name, $phone)
    {
        return $this->send('68876f203ff4b4b952ca2ed2', $name, $phone);
    }

    public function deliverOrder($name, $phone)
    {
        return $this->send('6887704f3ff4b4b952ca3337', $name, $phone);
    }

    public function customerSignup($name, $phone)
    {
        return $this->send('688770a03ff4b4b952ca34e3', $name, $phone);
    }

    public function AbandonedCartMale($name, $phone)
    {
        $link = 'https://m.clean-basket.com/draftorder';
        return $this->sendWithParams('68e6a3bd7647b354907f3020', $name, $phone, $link);
    }

    public function AbandonedCartFemale($name, $phone )
    {
        $link = 'https://m.clean-basket.com/draftorder';
        return $this->sendWithParams('68e6a8277647b354907f4481', $name, $phone, $link);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappBotWebhookService
{
    public function sendOrderConfirmed($phone, $orderNum)
    {
        $url = 'https://automations.white-lab.io/webhook/d58812ae-557f-4997-bc64-2a52b707f59e';
        return Http::post($url, [
            'phone'     => $phone,
            'orderNum'  => $orderNum,
        ]);
    }

    public function sendOrderStarted($phone)
    {
        $url = 'https://automations.white-lab.io/webhook/fef46ae6-d117-4b1e-baf8-9170ecd862de';
        return Http::post($url, [
            'phone' => $phone,
        ]);
    }

    public function sendOrderCompleted($phone)
    {
        $url = 'https://automations.white-lab.io/webhook/b05db646-1b06-4c9b-acf8-274af5dd0540';
        return Http::post($url, [
            'phone' => $phone,
        ]);
    }
}

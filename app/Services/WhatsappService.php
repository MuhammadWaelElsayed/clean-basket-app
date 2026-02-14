<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappService
{ 
    public static function send($to, $message)
    {
        $response= Http::acceptJson()->withToken(env('4WHATS_API_KEY'))->get(
            'https://api.4whats.net/sendMessage',
            [
                'instanceid' => env('4WHATS_INSTANCE_ID'),
                'token' => env('4WHATS_API_TOKEN'),
                'phone' => $to,
                'body' => $message,
            ]
        );
        return $response->json();
    }
    
}
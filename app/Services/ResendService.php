<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ResendService
{ 
    public static function send($to, $data)
    {
        $response= Http::acceptJson()->withToken(env('RESEND_API_KEY'))->post(
            'https://api.resend.com/emails',
            [
                'to' => [$to],
                'subject' => $data['title'],
                'html' =>  view('mails.'.$data['mail']['template'],compact('data'))->render(),
                'from' => 'LegalPlatform <support@legalplatform.co>',
            ]
        );
        // dd($response->json());
        return $response;
    }
    
}
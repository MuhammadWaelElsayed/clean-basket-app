<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google_Client;


class FCMService
{


    public function send($tokens, $notification,$data=["data"=>"no"])
    {
        $token=$this->getAccessToken();
        $projectId='clean-basket';
        $response = null;
        foreach ($tokens as $to) {
            try {
                $response= Http::acceptJson()->withToken($token)->post(
                    'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send',
                    [
                        'message'=>[
                            'token' => $to,
                            'notification' => $notification,
                            'data' => $data,
                        ]

                    ]
                );
                Log::info('Firebase response: '.json_encode($response->json()));
                return $response->json();
            } catch (\Exception $ex) {
                Log::info('FCMService:32 '.json_encode($ex->getMessage()));
            }
        }
    }

    private function getAccessToken()
    {
        $credentialsPath = storage_path('app/firebase-service-account.json'); // Path to your service account file

        $client = new Google_Client();
        $client->setAuthConfig($credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }


}

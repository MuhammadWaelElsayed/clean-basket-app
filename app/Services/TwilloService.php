<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
// use Twilio\Rest\Client;

class TwilloService
{ 
    public static function sendWhatsapp($phone, $message)
    {

        $to = '+' . $phone;
        try {

            $client = new Client();

            $response = $client->post('https://verify.twilio.com/v2/Services/'.env('TWILIO_SERVICE_SID').'/Verifications', [
                'auth' => [
                    env('TWILIO_ACCOUNT_SID'),
                    env('TWILIO_AUTH_TOKEN')
                ],
                'form_params' => [
                    'To' => $to,
                    // 'From' => env('TWILIO_FROM'),
                    'Body' => "Test Message",
                    'Channel' => "whatsapp"
                ]
            ]);

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully.',
                'sid' => json_decode($response->getBody())->sid,
                // 'otp' => $otp
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => false,
                'message' => $ex->getMessage()
            ]);
        }
    }

    public static function verifyWhatsapp($phone, $code)
    {

        $to = '+' . $phone;
        try {
            
            $client = new Client();
            $response = $client->post('https://verify.twilio.com/v2/Services/VA8774acee52fb6de69003468a27b5701d/Verifications', [
                'auth' => [
                    env('TWILIO_ACCOUNT_SID'),
                    env('TWILIO_AUTH_TOKEN')
                ],
                'form_params' => [
                    'To' => $to,
                    'Channel' => 'sms'
                ]
            ]);
            $result=$response->getBody()->getContents();
            $data=json_decode($result, true);
            dd($data);

            $verification_status = $data['status'];

            if ($verification_status == 'approved') {
                return [
                    'status' => true,
                    'message' => $data,
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'OTP is wronged'
                ];
            }
            
        } catch (\Exception $ex) {
            return [
                'status' => false,
                'message' => $ex->getMessage()
            ];
        }
    }



    public static function sendWhatsappSms($to, $message)
    {
        $accountSid = env('TWILIO_ACCOUNT_SID');
        $authToken = env('TWILIO_AUTH_TOKEN');
        $twilioNumber = env('TWILIO_FROM');
        $recipientNumber = '+'.$to;

        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $accountSid . '/Messages.json';

        $data = [
            'From' =>'whatsapp:'. $twilioNumber,
            'To' => 'whatsapp:'. $recipientNumber,
            'Body' => $message
        ];

        $query = http_build_query($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $accountSid . ':' . $authToken);
        $response = curl_exec($ch);
        curl_close($ch);

        $jsonResponse = json_decode($response);
        if($jsonResponse->status==400){
            return [
                "status"=> false,
                "message"=> 'Phone format is incorrect',
            ];
        }else{
            return [
                "status"=> true,
                "message"=> 'Message Send Successfully',
                "response" =>$jsonResponse
            ];

        }

    }

}
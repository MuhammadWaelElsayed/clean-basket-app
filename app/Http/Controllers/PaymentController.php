<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('MYFATOORAH_API_KEY');
        $this->baseUrl = env('MYFATOORAH_BASE_URL');
    }

    public function initiatePayment()
    {
        $paymentData = [
            'CustomerName' => 'John Doe',
            'InvoiceValue' => 10.000,
            'DisplayCurrencyIso' => 'KWD',
            'CustomerEmail' => 'customer@example.com',
            'CallBackUrl' => route('payment.callback'),
            'ErrorUrl' => route('payment.callback'),
            'Language' => 'en',
            'CustomerReference' => 'ref 12345',
            'MobileCountryCode' => '+965',
            'CustomerMobile' => '12345678',
        ];

        $response = Http::withToken($this->apiKey)
            ->post("{$this->baseUrl}/v2/SendPayment", $paymentData);

        if ($response->successful()) {
            return redirect($response->json()['Data']['InvoiceURL']);
        }

        return redirect()->back()->with('error', 'Failed to initiate payment');
    }

    public function paymentCallback(Request $request)
    {
        $paymentId = $request->query('paymentId');
        $response = Http::withToken($this->apiKey)
            ->get("{$this->baseUrl}/v2/getPaymentStatus", [
                'Key' => $paymentId,
                'KeyType' => 'PaymentId'
            ]);

        if ($response->successful()) {
            $paymentStatus = $response->json();
            // Handle payment status (e.g., update order status in database)
            return response()->json($paymentStatus);
        }

        return response()->json(['error' => 'Failed to get payment status'], 500);
    }
}

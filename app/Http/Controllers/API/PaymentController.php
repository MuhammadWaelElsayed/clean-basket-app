<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Vendor;
use Stripe\StripeClient;


class PaymentController extends Controller
{

    private $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.api_keys.secret_key'));
    }



    public function stripePayment(Request $request)
    {
        $request->validate([
            "amount"=>"required|min:1",
        ]);
        try {
            $customerId=auth()->user()->stripe_customer_id;
            // Use an existing Customer ID if this is a returning customer.
            if($customerId!=null){
                $payment_methods = $this->stripe->paymentMethods->all([
                    'customer' => $customerId,
                    'type' => 'card'
                ]);
                if(!isset($payment_methods->data[0])){
                    // return ['status'=>false,'message'=>'No Payment Method is attached with customer'];
                    $customer = $this->stripe->customers->create();
                    $customerId=$customer->id;
                    Vendor::find(auth()->user()->id)->update(['stripe_customer_id'=>$customerId]);

                    $ephemeralKey = $this->stripe->ephemeralKeys->create([
                        'customer' => $customerId,
                        ], [
                        'stripe_version' => '2022-08-01',
                    ]);
                    $paymentIntent = $this->stripe->paymentIntents->create([
                    'amount' => $request->amount*100,
                    'currency' => 'usd',
                    'customer' => $customerId,
                    'automatic_payment_methods' => [
                        'enabled' => 'true',
                    ],
                    'metadata' => [
                        'name' => auth()->user()->name,
                        'email' => auth()->user()->email,
                    ],
                    ]);
                }
                else{
                    $ephemeralKey = $this->stripe->ephemeralKeys->create([
                        'customer' => $customerId,
                        ], [
                        'stripe_version' => '2022-08-01',
                    ]);
                    $paymentIntent = $this->stripe->paymentIntents->create([
                        'amount' => $request->amount*100,
                        'currency' => 'usd',
                        'payment_method' => (isset($payment_methods->data[0]))?$payment_methods->data[0]->id:null,
                        'customer' => $customerId,
                        'automatic_payment_methods' => [
                            'enabled' => 'true',
                        ],
                        'metadata' => [
                            'name' => auth()->user()->name,
                            'email' => auth()->user()->email,
                            // "cart_items" => $items
                        ],
                        // 'confirm' => true,
                        // 'off_session' => true
                      ]);
                }
            }
            //Create New Customer
            else{
                $customer = $this->stripe->customers->create();
                $customerId=$customer->id;
                Vendor::find(auth()->user()->id)->update(['stripe_customer_id'=>$customerId]);

                $ephemeralKey = $this->stripe->ephemeralKeys->create([
                    'customer' => $customerId,
                    ], [
                    'stripe_version' => '2022-08-01',
                ]);
                $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $request->amount*100,
                'currency' => 'usd',
                'customer' => $customerId,
                'automatic_payment_methods' => [
                    'enabled' => 'true',
                ],
                ]);
            }

            return json_encode(
            [
                'status' => true,
                'paymentIntent' => $paymentIntent->client_secret,
                'ephemeralKey' => $ephemeralKey->secret,
                'customer' => $customerId,
                'publishableKey' => env('STRIPE_KEY')
            ] );
        } catch (\Throwable $th) {
            //throw $th;
            return json_encode(
                [
                    'status' => false,
                    'message' => $th->getMessage(),
                ] );
        }
    }


}

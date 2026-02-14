<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\VendorPackage;
use App\Models\Package;
use App\Models\Notification;
use App\Models\StripeLog;
use App\Models\CaseInterest;
use Carbon\Carbon;
use App\Http\Controllers\PaymentController;
use App\Services\FCMService;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class CronJobController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function renewSubscription(Request $request)
    {
        // Log::info('Test Cron Call '.date('H:i')); 
        $users=Vendor::where(['status'=>1,'is_approved'=>1,'deleted_at'=>null])->get();
        foreach ($users as $user ) {
           
            $package=VendorPackage::with('package')->whereHas('package')->where(['vendor_id'=>$user->id,"is_addon"=>0])->orderBy('id','desc')->first();
           
            if($package!=null){
                $now=Carbon::now();
                $expiredDate = Carbon::parse($package->expired_at);
                $last3= Carbon::now()->subDays(3);
                // dd($expiredDate->format('Y-m-d') ,$now->format('Y-m-d') );
                
                //Check Pakcage Not Cancelled and Expired within the next 3 days and Expiry < Now 
                if($package->is_cancelled==0 && $expiredDate >= $last3  && $expiredDate->format('H:i')==date('H:i')  ){
                    // Log::info('All Okay. userid='.$user->id); 
                  
                    //Check If Exact Date and Time make payment Else send notification of expiry soon
                    if($expiredDate->format('Y-m-d') != $now->format('Y-m-d') ){

                        //Free Trial Package
                        if($package->package_id==8){
                            $this->sendFreeTrialNotification($user);
                        }else{
                            $this->sendExpiryNotification($user);
                        }
                        continue;
                    }
                   
                    $pay=new PaymentController;
                    $customerId=$pay->getStripeCustomer($user->id);

                    $card=$this->getCard($customerId);
                    // dd($card);

                    if($card['status']==true){
                       $payment = $this->makePayment($user->id,$customerId,$card['id'],$package->package_id);
                        //    If Payment Failed
                       if($payment['status']==false){
                            $this->sendFCM($user);
                       }
                    }else{
                        //If Card not found
                        $this->sendFCM($user);
                    }
                }
            }

        }

    }

    public function getCard($customerId)  {
        try {
            $cards = \Stripe\PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card', 
            ]);

            if(isset($cards->data) && !empty($cards->data)){
                return [
                    "status"=>true,
                    "id" =>$cards->data[0]->id
                ];
            }else{
                return [
                    "status"=>false,
                ];
            }

        } catch (\Stripe\Exception\InvalidRequestException $ex) {
            return [
                "status"=>false,
            ];
        }
        
    }

    public function makePayment($userId,$customerId,$paymentMethod,$planId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $plan= Package::find($planId);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $plan->price*100, 
                'currency' => 'usd',
                'payment_method' => $paymentMethod,
                'customer' => $customerId,
                // 'automatic_payment_methods' => [
                //     'enabled' => 'true',
                // ],
                'confirm' => true,
                'off_session' => false,
                'return_url' => 'https://example.com/success'
            ]);
            // Handle the PaymentIntent status
            if ($paymentIntent->status === 'succeeded') {
                $currentDate = Carbon::now();
                $buy_at=$currentDate->format('Y-m-d H:i:s');
                if($plan->period_type=="month"){
                    $expired_at=$currentDate->addMonths($plan->period)->format('Y-m-d H:i:s');
                }
                elseif($plan->period_type=="year"){
                    $expired_at=$currentDate->addYears($plan->period)->format('Y-m-d H:i:s');
                }else{
                    $expired_at=$currentDate->addDays($plan->period)->format('Y-m-d H:i:s');
                }
                UserPackage::create([
                    "user_id" => $userId,
                    "package_id" => $plan->id,
                    "buy_at" => $buy_at,
                    "expired_at" => $expired_at,
                    "is_auto_renewed" => 1,
                ]);
                return [
                    "status"=>true,
                ];

            } else {
                return [
                    "status"=>false,
                    "message"=>"Error in creating payment intent",
                ];
            }

        } catch (\Exception $e) {
            Log::info($e->getMessage().' userid='.$userId); 
            return [
                "status"=>false,
                "message"=>$e->getMessage()
            ];
        }
    }

    public function sendFCM($user) {
        $title="LegalPlatform subscription renewal failed";
        $body="Payment failed, you can renew your subscription from LegalPlatform App manually.";
        $title_ar="فشل تجديد الاشتراك في LegalPlatform";
        $body_ar="فشل الدفع، يمكنك تجديد اشتراكك من تطبيق LegalPlatform يدويًا.";

        FCMService::send(
            $user->deviceToken,
            [
                'title' => $title,
                'body' => $body,
            ]
        );
        Notification::create([
            'title' => $title,
            'title_ar' => $title_ar,
            'message' => $body,
            'message_ar' => $body_ar,
            'user_id' => $user->id,
        ]);
    }

    public function sendExpiryNotification($user) {
        $data=[
            "title"=>"Your Subscription is Expiring soon.",
            "title_ar"=>"اشتراكك سينتهي قريبا",
            "message"=> "Your Subscription is Expiring soon.",
            "message_ar"=> "اشتراكك سينتهي قريبا",
            // "mail" => [
            //     "template"=>"case_interest"
            // ],
            "user"=>$user
        ];
        // dd($vendors);
        $this->sendNotifications($data,'vendor');
    }

    public function sendFreeTrialNotification($user) {
        $data=[
            "title"=>"Free trail getting end soon",
            "title_ar"=>"سينتهي المسار المجاني قريبًا",
            "message"=> "Free trail getting end soon",
            "message_ar"=> "سينتهي المسار المجاني قريبًا",
            // "mail" => [
            //     "template"=>"case_interest"
            // ],
            "user"=>$user
        ];
        // dd($vendors);
        $this->sendNotifications($data,'vendor');
    }

    public function reviewNotification(Request $request)
    {
        // Log::info('Test Cron Call '.date('H:i')); 
        $twelveHoursAgo = Carbon::now()->subHours(12);
        $thirteenHoursAgo = Carbon::now()->subHours(13);

        $interests = CaseInterest::with('case.user')->whereHas('case')->whereBetween('created_at', [$thirteenHoursAgo, $twelveHoursAgo])
        ->get();
        
        foreach ($interests as $interest ) {
            $user=$interest->case->user;
            $data=[
                "title"=>"How's your experience with vendor",
                "title_ar"=> "How's your experience with vendor Arabic message",
                "message"=> "How's your experience with vendor on your case #".$interest->case_id,
                "message_ar"=> "How's your experience with vendor on your case #".$interest->case_id,
                "user"=> $user
            ];
            $fcmData=[
                "title"=> ($user['app_lang']=="ar")?$data['title_ar']:$data['title'],
                "body"=> ($user['app_lang']=="ar")?$data['message_ar']:$data['message'],
            ];
            $data=[
                "case_interest_id"=> $interest->id
            ];
            if($user['notification_enable']==1){
                FCMService::sendWithData($user['deviceToken'], $fcmData,$data);
            }
           

        }

    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AuthValidation;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\FcmToken;
use App\Models\PromoCode;
use App\Models\Referral;
use App\Models\UserPromoCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Services\FCMService;
use App\Services\StatusSmsWhatsappService;
use App\Services\TwilloService;
use App\Services\WhatsappService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * Redirect the user to the Provider authentication page.
     *
     * @param $provider
     * @return JsonResponse
     */
    // unique:vendors,name,{$id},id,deleted_at,NULL",
    // public function testNotification()
    // {
    //     $user = User::findOrFail(1);
    //     FCMService::send(
    //         $user->fcm_token,
    //         [
    //             'title' => 'your title',
    //             'body' => 'your body',
    //         ]
    //     );
    // }

    public function register(Request $request)
    {
        Log::info("register: " . $request);
        if (isset($request->language) && $request->language == "ar") {
            $messages = [
                // 'email.required' => 'البريد الإلكتروني مطلوب.',
                'email.unique' => 'البريد الإلكتروني موجود بالفعل.',
                // 'first_name.required' => 'الاسم الأول مطلوب.',
                'phone.required' => 'رقم الهاتف مطلوب.',
                'phone.regex' => 'رقم الهاتف غير صالح. يجب أن يبدأ بـ 966 ويكون مكونًا من 7 إلى 10 أرقام.',
                'phone.unique' => 'رقم الهاتف موجود بالفعل.',
            ];
        } else {
            $messages = [];
        }

        // التحقق من وجود مستخدم محذوف قبل التحقق من القواعد
        $phone = $request->phone;
        if (strpos($phone, '9660') === 0) {
            $phone = preg_replace('/^9660/', '966', $phone);
        }

        $deletedUser = User::withTrashed()
            ->where(function($query) use ($phone, $request) {
                $query->where('phone', $phone);
                if ($request->email) {
                    $query->orWhere('email', $request->email);
                }
            })
            ->whereNotNull('deleted_at')
            ->first();

        // إذا كان هناك مستخدم محذوف، لا نحتاج للتحقق من القواعد
        if (!$deletedUser) {
            $request->validate([
                "email" => 'nullable|unique:users,email,NULL,id,deleted_at,NULL',
                "first_name" => 'nullable',
                "phone" => [
                    'required',
                    'regex:/^966\d{7,10}$/',
                    'unique:users,phone,NULL,id,deleted_at,NULL'
                ],
                "gender" => 'nullable|in:male,female',
            ], $messages);
        } else {
            // التحقق من صحة البيانات فقط إذا كان هناك مستخدم محذوف
            $request->validate([
                "email" => 'nullable',
                "first_name" => 'nullable',
                "phone" => [
                    'required',
                    'regex:/^966\d{7,10}$/',
                ],
                "gender" => 'nullable|in:male,female',
            ], $messages);
        }

        $verify_token = rand(1000, 9999);
        // $verify_token=1234;
        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $phone,
            'otp' => $verify_token,
            'app_lang' => $request->language ?? 'en',
            'gender' => $request->gender,
        ];
        if (isset($request->deviceToken) && $request->deviceToken !== null) {
            $data['deviceToken'] = $request->deviceToken;
        }

        if ($deletedUser) {
            // إعادة تفعيل المستخدم المحذوف بدون تعديل البيانات
            $deletedUser->restore();
            $user = $deletedUser;

            // تحديث OTP فقط لإرسال رمز التحقق
            $user->update(['otp' => $verify_token]);
        } else {
            // إنشاء مستخدم جديد
            $user = User::create($data);
        }

        Log::info("referral_code: 1" . $request->referral_code);
        if ($request->has('referral_code')) {
            Log::info("referral_code: " . $request->referral_code);
            $user->referral_used = $request->referral_code;
            $user->save();
        }

        $this->giveAllPromo($user);
        try {
            $details = [
                'name' => $user->name,
                'email' => $user->email,
                'otp' => $verify_token
            ];
            if ($data['app_lang'] == "ar") {
                $message = "رمز التحقق الخاص بك في كلين باسكت هو  " . $verify_token . " ، يرجى عدم مشاركته مع أي شخص";
            } else {
                $message = "Your Clean Basket verification code is " . $verify_token . ", please do not share it with anyone";
            }
            //Send OTP to Phone
            // $res=WhatsappService::send($phone, $message);

            $smsService = new \App\Services\SmsService();
            $res = $smsService->sendSms($request->phone, $message);
            Log::info($res);
            if (isset($res['sent']) && $res['sent'] != true) {
                return [
                    "status" => false,
                    "message" => __('api')['otp_failed'],
                    "data" => []
                ];
            }
        } catch (\Exception $ex) {
            logger($ex->getMessage());
            return [
                "status" => false,
                "message" => __('api')['otp_failed'],
                "data" => $ex->getMessage()
            ];
        }

        Cache::put("otp:purpose:{$user->phone}", 'register', now()->addMinutes(15));
        return [
            'message' => __('api')['otp_sent'],
            'status' => true,
            'data' => [
                "user" => $user,
                "otp" => $verify_token,
            ],
        ];
    }

    /**
     * @group Auth
     *
     * User login using phone number.
     *
     * This endpoint sends an OTP code to the user’s phone number.
     *
     * @unauthenticated
     *
     * @bodyParam phone string required The phone number of the user. Must start with country code 966. Example: 966535128922
     *
     * @response 200 {
     *   "status": true,
     *   "message": "Otp is send to your phone. Check your phone",
     *   "data": {
     *     "user": {
     *       "id": 8861,
     *       "first_name": "omer",
     *       "last_name": "ali",
     *       "phone": "966535128922"
     *     }
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Record not found.",
     *   "status": false
     * }
     */

    public function login(Request $request)
    {
        $request->validate(["phone" => 'required']);

        $user = User::whereNull('deleted_at')->where('phone', $request->phone)->first();

        if (!$user) {
            return [
                'status' => false,
                'message' => __('api')['login_wrong'],
            ];
        }
        if ($user->status == 0) {
            return [
                'status' => false,
                'message' =>  __('api')['account_inactive'],
            ];
        }
        // $code = rand(1000, 9999);
        if($request->phone == '966534161403' or $request->phone == '966555444444'){
            $code = 1234;
        } else {
            $code = rand(1000, 9999);
        }
        $user->update(['otp' => $code]);
        if (isset($request->language) && $request->language !== null) {
            $user->update(['app_lang' => $request->language]);
        }
        if (isset($request->deviceToken) && $request->deviceToken !== null) {
            $user->update(['deviceToken' => $request->deviceToken]);
        }
        $details = [
            'name' => $user->name,
            'email' => $user->email,
            'otp' => $code
        ];
        try {
            if ($user->app_lang == "ar") {
                $message = "رمز التحقق الخاص بك في كلين باسكت هو  " . $code . " ، يرجى عدم مشاركته مع أي شخص";
            } else {
                $message = "Your Clean Basket verification code is " . $code . ", please do not share it with anyone";
            }
            // Send OTP To Phone
            // $res=WhatsappService::send($request->phone, $message);

            $smsService = new \App\Services\SmsService();
            $res = $smsService->sendSms($request->phone, $message);

            // dd($res);
            if (isset($res['sent']) && $res['sent'] != true) {
                return [
                    "status" => false,
                    "message" => __('api')['otp_failed'],
                    "data" => []
                ];
            }
        } catch (\Exception $ex) {
            return [
                "status" => false,
                "message" => __('api')['otp_failed'],
                "data" => $ex->getMessage()
            ];
        }
        Cache::put("otp:purpose:{$user->phone}", 'login', now()->addMinutes(15));
        return [
            'status' => true,
            'message' => __('api')['otp_sent'],
            'data' => [
                // 'auth_token' => auth()->user()->createToken('auth_token')->plainTextToken,
                "user" => $user
            ],
        ];
    }

    public function guestLogin(Request $request)
    {
        // Log the request for debugging
        Log::info('Guest Login Request:', [
            'deviceToken' => $request->deviceToken,
            'language' => $request->language,
            'all_data' => $request->all()
        ]);

        // إنشاء deviceToken فريد إذا لم يتم إرساله
        $deviceToken = $request->deviceToken;
        if (!$deviceToken || empty($deviceToken)) {
            $deviceToken = 'guest_' . uniqid() . '_' . time();
            Log::info('Generated new deviceToken for guest', ['deviceToken' => $deviceToken]);
        }

        // البحث عن مستخدم ضيف موجود بنفس deviceToken
        $user = User::where('deviceToken', $deviceToken)
            // ->where('name', 'Guest')
            ->where('first_name', 'Guest')
            ->whereNull('deleted_at')
            ->first();

        // إذا لم يوجد مستخدم ضيف، أنشئ واحد جديد
        if (!$user) {
            try {
                // إنشاء مستخدم ضيف جديد
                $user = User::create([
                    'deviceToken' => $deviceToken,
                    // 'name' => 'Guest',
                    'first_name' => 'Guest',
                    'status' => 1,
                    'app_lang' => $request->language ?? 'en'
                ]);
                Log::info('Guest user created successfully', ['user_id' => $user->id, 'deviceToken' => $deviceToken]);
            } catch (\Exception $e) {
                Log::error('Failed to create guest user', ['error' => $e->getMessage()]);

                // في حالة فشل الإنشاء، جرب إنشاء مستخدم بدون deviceToken
                try {
                    $user = User::create([
                        // 'name' => 'Guest',
                        'first_name' => 'Guest',
                        'status' => 1,
                        'app_lang' => $request->language ?? 'en'
                    ]);
                    Log::info('Guest user created without deviceToken', ['user_id' => $user->id]);
                } catch (\Exception $e2) {
                    Log::error('Failed to create guest user without deviceToken', ['error' => $e2->getMessage()]);
                    return [
                        'status' => false,
                        'message' => 'Failed to create guest user',
                        'data' => []
                    ];
                }
            }
        } else {
            Log::info('Existing guest user found', ['user_id' => $user->id, 'deviceToken' => $deviceToken]);
        }

        if (isset($request->language) && $request->language == 'ar') {
            $success = 'نجح تسجيل دخول الضيف';
        } else {
            $success = 'Guest Login Success';
        }

        // تحديث اللغة إذا تم تمريرها
        if (isset($request->language) && $request->language !== null) {
            $user->update(['app_lang' => $request->language]);
        }

        $user->isGuest = true;
        return [
            'status' => true,
            'success' => $success,
            "data" => [
                'auth_token' => $user->createToken('tokens')->plainTextToken,
                "user" => $user
            ]
        ];
    }

    public function googleSignin(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required",
            "googleId" => "required",
        ]);

        $user = User::firstOrCreate(
            [
                'email' => $request->email,
            ],
            [
                'name' => $request->name,
                'googleId' => $request->googleId,
            ]
        );
        if (isset($request->deviceToken) && $request->deviceToken !== null) {
            User::find($user->id)->update(['deviceToken' => $request->deviceToken]);
        }
        // $user->assignRole('Vendor');
        return response()->json([
            'status' => true,
            'message' => "Google Signin Successfull",
            'data' => [
                'auth_token' => $user->createToken('auth-token')->plainTextToken,
                'user' => $user,
            ]

        ]);
    }

    public function appleSignin(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required",
            "appleId" => "required",
        ]);
        $user = User::firstOrCreate(
            [
                'email' => $request->email,
            ],
            [
                'name' => $request->name,
                'appleId' => $request->appleId,
            ]
        );
        if (isset($request->deviceToken) && $request->deviceToken !== null) {
            User::find($user->id)->update(['deviceToken' => $request->deviceToken]);
        }
        // $user->assignRole('Vendor');
        return response()->json([
            'status' => true,
            'message' => "Apple Signin Successfull",
            'data' => [
                'auth_token' => $user->createToken('auth-token')->plainTextToken,
                'user' => $user,
            ]
        ]);
    }
    public function facebookSignin(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required",
            "facebookId" => "required",
        ]);
        $user = User::firstOrCreate(
            [
                'email' => $request->email,
            ],
            [
                'name' => $request->name,
                'facebookId' => $request->facebookId,
            ]
        );
        if (isset($request->deviceToken) && $request->deviceToken !== null) {
            User::find($user->id)->update(['deviceToken' => $request->deviceToken]);
        }
        if (isset($request->language) && $request->language == 'ar') {
            $success = 'تم تسجيل الدخول إلى Facebook بنجاح';
        } else {
            $success = 'Facebook Signin Successfull';
        }
        // $user->assignRole('Vendor');
        return response()->json([
            'status' => true,
            'message' => $success,
            'data' => [
                'auth_token' => $user->createToken('auth-token')->plainTextToken,
                'user' => $user,
            ]

        ]);
    }

    public function verifyOTP(Request $request)
    {
        $request->validate(["phone" => 'required', 'otp' => 'required']);

        $conditions = ['phone' => $request->phone, 'deleted_at' => null, 'status' => 1, 'otp' => $request->otp];

        $user = User::where($conditions)->first();

        if ($user || !empty($user)) {

            // save device token
            if (isset($request->deviceToken) && $request->deviceToken !== null) {
                FcmToken::updateOrCreate(
                    ["token" => $request->deviceToken],
                    [
                        "token"   => $request->deviceToken,
                        "user_id" => $user->id
                    ]
                );
            }

            try {
                $purpose = \Illuminate\Support\Facades\Cache::pull("otp:purpose:{$user->phone}");
                $firstTime = !$user->tokens()->exists();
                $welcomeKey = "welcome:sent:{$user->phone}";
                $welcomeAlreadySent = \Illuminate\Support\Facades\Cache::has($welcomeKey);

                if ((($purpose === 'register') || $firstTime) && !$welcomeAlreadySent) {
                    try {
                        $statusSmsWhatsappService = new \App\Services\StatusSmsWhatsappService();
                        $statusSmsWhatsappService->customerSignup($user->first_name . ' ' . $user->last_name, $user->phone);
                        \Illuminate\Support\Facades\Cache::put($welcomeKey, true, now()->addDays(30));
                    } catch (\Throwable $e) {
                        Log::warning('Welcome WhatsApp failed: ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OTP purpose/welcome cache handling failed: ' . $e->getMessage());
            }
            $user->update(['otp' => null]);

            return [
                'status'  => true,
                'message' => __('api')['otp_verified'],
                'data'    => [
                    "auth_token" => $user->createToken('tokens')->plainTextToken,
                    "user"       => $user
                ]
            ];
        } else {
            return [
                'status'  => false,
                'message' => __('api')['otp_wronged'],
                'data'    => null
            ];
        }
    }

    public function resendOTP(Request $request)
    {
        $request->validate(["phone" => 'required']);

        $code = rand(1000, 9999);
        // $code=1234;

        $user = User::where(['phone' => $request->phone, 'status' => 1, 'deleted_at' => null])->first();
        if ($user == null) {
            return [
                'status' => false,
                'message' => __('api')['phone_not_matched'],
                'data' => []
            ];
        }
        $details = [
            'name' => $user->name,
            'phone' => $user->phone,
            'otp' => $code
        ];
        try {
            if ($user->app_lang == "ar") {
                $message = "رمز التحقق الخاص بك في كلين باسكت هو  " . $code . " ، يرجى عدم مشاركته مع أي شخص";
            } else {
                $message = "Your Clean Basket verification code is " . $code . ", please do not share it with anyone";
            }

            // Send OTP to phone
            // $res=WhatsappService::send($request->phone, $message);

            $smsService = new \App\Services\SmsService();
            $res = $smsService->sendSms($request->phone, $message);

            if (isset($res['sent']) && $res['sent'] != true) {
                return [
                    "status" => false,
                    "message" => __('api')['otp_failed'],
                    "data" => []
                ];
            }
        } catch (\Exception $ex) {
            return [
                "status" => false,
                "message" => __('api')['otp_failed'],
                "data" => $ex->getMessage()
            ];
        }
        $user->update([
            'otp' => $code,
        ]);
        return [
            'status' => true,
            'message' =>  __('api')['otp_sent'],
            'data' => ['otp' => $code],
        ];
    }

    public function signout()
    {
        auth()->user()->tokens()->delete();

        return [
            "status" => true,
            'message' => __('api')['logout_success'],
            "data" => []
        ];
    }

    public function giveAllPromo($user)
    {
        $codes = PromoCode::where(['user_type' => 'All', 'status' => 1])->get();
        foreach ($codes as $key => $code) {
            UserPromoCode::create([
                "user_id" => $user->id,
                "code_id" => $code->id
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Item;
use App\Models\Service;
use App\Models\Banner;
use App\Models\Setting;
use App\Models\UserAddress;
use App\Models\City;
use App\Models\Order;
use App\Models\Onboard;

use App\Models\Area;
use App\Models\UserContact;
use App\Models\Notification;
use App\Models\Country;
use App\Models\TimeSlot;
use App\Models\PartnerInquery;

use Carbon\Carbon;
use App\Services\SMSService;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * @OA\Info(
     *     title="Clean Basket API",
     *     version="1.0.0"
     * )
     *
     * @OA\PathItem(path="/api")
     */

    public function testSMS(Request $request)
    {
        $response = SMSService::send('+923434692063', 'This is Test message from Faseelah');
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     tags={"Dashboard"},
     *     summary="Get dashboard data",
     *     description="Returns the banners, services, orders, etc.",
     *     @OA\Response(
     *         response=200,
     *         description="Success"
     *     )
     * )
     */

    // public function getDashboard(Request $request)
    // {
    //     $name_field = (isset($request->language) && $request->language == 'ar') ? 'name_ar as name' : 'name';

    //     $banners = Banner::all();
    //     $services = Service::select('id', $name_field, 'image')->whereNull('deleted_at')->where('status', 1)->get();
    //     $basket = UserAddress::select('basket_status', 'address_type')->where(['user_id' => auth()->user()->id])
    //         ->whereNull('deleted_at')->latest('id')->first();
    //     $order = Order::where('user_id', auth()->user()->id)->whereNotIn('status', ['Delivered', 'Cancelled'])->first();
    //     $unapid = Order::where('user_id', auth()->user()->id)->where('pay_status', 'Unpaid')->where('status', '!=', 'CANCELLED')
    //         ->where('grand_total', '>', 0)->count();

    //     $partners = Setting::where('key', 'partners')->pluck('value');
    //     $partners = json_decode($partners[0]);
    //     // dd($partners);
    //     foreach ($partners as $key => $partner) {
    //         $partner->logo = asset('media/' . $partner->logo);
    //     }


    //     return [
    //         "status" => true,
    //         "message" => "Dashboard Data get successfully!",
    //         "data" => [
    //             "banners" => $banners,
    //             "services" => $services,
    //             "partners" => $partners,
    //             "basket_status" => ($basket) ? 'Delivered' : 'Not Requested',
    //             "address_type" => ($basket) ? $basket['address_type'] : 'None',
    //             "active_order" => $order,
    //             "payment_pending" => ($unapid > 0) ? true : false,
    //             "support_info" => [
    //                 "email" => "support@cleanbasket.com",
    //                 "phone" => "+971234567890"
    //             ]
    //         ]
    //     ];
    // }

    public function getDashboard(Request $request)
    {
        $name_field = (isset($request->language) && $request->language == 'ar') ? 'name_ar as name' : 'name';

        $banners  = Banner::all();
        $services = Service::select('id', $name_field, 'image')->whereNull('deleted_at')->where('status', 1)->get();
        $basket   = UserAddress::select('basket_status', 'address_type')
            ->where(['user_id' => auth()->user()->id])
            ->whereNull('deleted_at')->latest('id')->first();

        $order = Order::where('user_id', auth()->user()->id)
            ->whereNotIn('status', ['Delivered', 'Cancelled'])
            ->first();

        // 🔹 إضافة due_amount
        if ($order) {
            $order->append(['due_amount']);
        }

        $unapid = Order::where('user_id', auth()->user()->id)
            ->where('pay_status', 'Unpaid')
            ->where('status', '!=', 'CANCELLED')
            ->where('grand_total', '>', 0)->count();

        $partners = Setting::where('key', 'partners')->pluck('value');
        $partners = json_decode($partners[0]);
        foreach ($partners as $key => $partner) {
            $partner->logo = asset('media/' . $partner->logo);
        }

        return [
            "status"  => true,
            "message" => "Dashboard Data get successfully!",
            "data"    => [
                "banners"         => $banners,
                "services"        => $services,
                "partners"        => $partners,
                "basket_status"   => ($basket) ? 'Delivered' : 'Not Requested',
                "address_type"    => ($basket) ? $basket['address_type'] : 'None',
                "active_order"    => $order, // يحتوي الآن على due_amount
                "payment_pending" => ($unapid > 0) ? true : false,
                "support_info"    => [
                    "email" => "support@cleanbasket.com",
                    "phone" => "+971234567890"
                ]
            ]
        ];
    }


    public function getServiceItems(Request $request)
    {
        // $request->validate([
        //     "service_id"=>"required",
        // ]);
        $name_field = (isset($request->language) && $request->language == 'ar') ? 'name_ar as name' : 'name';
        $desc_field = (isset($request->language) && $request->language == 'ar') ? 'description_ar as description' : 'description';

        $service = null;
        $items = Item::select(['id', $name_field, $desc_field, 'image', 'price'])->whereNull('deleted_at')->where(['status' => 1])->where('id', '<', 1000);
        if ($request->service_id > 0) {
            $items->where(['service_id' => $request->service_id]);
            $service = Service::select(['id', $name_field, 'image'])->find($request->service_id);
        }
        $items = $items->get();

        return [
            "status" => true,
            "message" => " Data get successfully!",
            "data" => [
                "service" => $service,
                "items" => $items,
            ]
        ];
    }


    public function getServices(Request $request)
    {
        $name_field = (isset($request->language) && $request->language == 'ar') ? 'name_ar as name' : 'name';
        $desc_field = (isset($request->language) && $request->language == 'ar') ? 'description_ar as description' : 'description';

        $data = Service::with(['items' => function ($query) use ($name_field, $desc_field) {
            $query->select(['id', 'service_id', $name_field, 'price', 'image', $desc_field]);
        }])->select(['id', $name_field, 'image'])->where(['status' => 1])
            ->whereNull('deleted_at')->latest('id')->get();

        return [
            "status" => true,
            "message" => "Services get successfully!",
            "data" => [
                "services" => $data,
            ]
        ];
    }

    public function getCities(Request $request)
    {
        $name_field = (isset($request->language) && $request->language == 'ar') ? 'name_ar as name' : 'name';

        $cities = City::select(['id', $name_field])->where(['status' => 1])
            ->whereNull('deleted_at')->latest('id')->get();

        return [
            "status" => true,
            "message" => "Cities get successfully!",
            "data" => [
                "cities" => $cities,
            ]
        ];
    }
    public function onboardData(Request $request)
    {

        $video = Onboard::where(['status' => 1, 'type' => 'video'])->latest('id')->first();
        $banners = Onboard::where(['status' => 1, 'type' => 'banner'])->latest('id')->get();

        return [
            "status" => true,
            "message" => "Onboard Data get successfully!",
            "data" => [
                "banners" => $banners,
                "video" => $video,
            ]
        ];
    }

    public function getAreasByCity(Request $request)
    {
        // Set the city name and radius
        $name_field = (isset($request->language) && $request->language == 'ar') ? 'name_ar as name' : 'name';
        $city = $request->city_id;
        $areas = Area::select(['id', $name_field])->where('city_id', $request->city_id);
        if (isset($request->search) && $request->search != '') {
            $name_clm = (isset($request->language) && $request->language == 'ar') ? 'name_ar' : 'name';
            $areas->where($name_clm, 'LIKE', '%' . $request->search . '%');
        }
        $areas = $areas->get();
        return [
            "status" => true,
            "message" => "City Areas get successfully!",
            "data" => [
                "areas" => $areas,
            ]
        ];
    }
    public function becomePartner(Request $request)
    {
        $validated = $request->validate([
            "first_name" => "required|max:250",
            "last_name" => "max:250",
            "email" => "required|email|max:250",
            "contact_number" => "required|max:250",
            "business_name" => "required|max:250",
        ]);
        $saved = PartnerInquery::create($validated);
        if ($saved) {
            return [
                'status' => true,
                'message' => "your become partner query saved!",
                'data' => $saved,
            ];
        }
    }

    public function getUserAddress(Request $request)
    {
        //Only One Address
        $user_address = UserAddress::where(['user_id' => auth()->user()->id])
            ->whereNull('deleted_at')->latest('id')->first();

        return [
            "status" => ($user_address == null) ? false : true,
            "message" => ($user_address == null) ? 'No Address Found' : 'User Addresses get successfully!',
            "data" => $user_address,
        ];
    }

    public function addUserAddress(Request $request)
    {
        // $isAlready = UserAddress::where('user_id', auth()->user()->id)->count();
        // if ($isAlready > 0) {
        //     return [
        //         'status' => false,
        //         "message" => __('api')['address_already'],
        //     ];
        // }

        $validatedData = $request->validate([
            "lat" => "required",
            "lng" => "required",
            "area" => "nullable",
            "address_type" => "nullable|in:House,Apartment",
            "street_no" => "nullable:address_type,House",
            "house_no" => "nullable:address_type,House",

            "building" => "nullable:address_type,Apartment",
            "appartment" => "nullable:address_type,Apartment",
            "floor" => "nullable:address_type,Apartment",
            "door_password" => "max:50",
        ]);
        // $vendor=$this->getNearbyVendor($request->lat,$request->lng);
        $vendor = $this->getAreaVendor($request->lat, $request->lng);
        if ($vendor == null) {
            return [
                "status" => false,
                "message" => __('api')['order_vendor'],
                "data" => [],
            ];
        }
        $vendorId = $vendor->id ?? 0;
//        $driver = $this->getNearbyDriver($vendorId, $request->lat, $request->lng);

        $validatedData['vendor_id'] = $vendorId;
//        $validatedData['driver_id'] = $driver->id ?? 0;
        $validatedData['user_id'] = auth()->user()->id;
        $validatedData['door_password'] = $request->door_password ?? '';

        if ($validatedData['address_type'] == "House") {
            $last_address = UserAddress::where('address_type', 'House')->latest()->first();
            $basket_no = (int) str_replace('H', '', $last_address->basket_no ?? 100);
            $validatedData['basket_no'] = 'H' . ($basket_no + 1);
        } else {
            $last_address = UserAddress::where('address_type', 'Apartment')->latest()->first();
            $basket_no = (int) str_replace('A', '', $last_address->basket_no ?? 100);
            $validatedData['basket_no'] = 'A' . ((int)$basket_no + 1);
        }

        $user = UserAddress::create($validatedData);

        if ($user) {
            return [
                'status' => true,
                'message' => "User Address added successfully!",
            ];
        }
    }

    public function updateUserAddress(Request $request)
    {
        $rules = [
            "address_id" => "required|exists:user_address,id",
            "lat" => "required",
            "lng" => "required",
            "area" => "required",
            "address_type" => "required|in:House,Apartment",
            "street_no" => "required_if:address_type,House",
            "house_no" => "required_if:address_type,House",
            "building" => "required_if:address_type,Apartment",
            "appartment" => "required_if:address_type,Apartment",
            "floor" => "required_if:address_type,Apartment",
            "door_password" => "max:50",
        ];
        $validated = $request->validate($rules);

        $userAddress = UserAddress::where('id', $request->address_id)
            ->where('user_id', $request->user()->id)
            ->whereNull('deleted_at')
            ->first();

        if (!$userAddress) {
            return [
                'status' => false,
                'message' => "Address not found.",
            ];
        }

        $userAddress->lat = $request->lat;
        $userAddress->lng = $request->lng;
        $userAddress->area = $request->area;
        $userAddress->address_type = $request->address_type;
        $userAddress->street_no = $request->street_no ?? null;
        $userAddress->house_no = $request->house_no ?? null;
        $userAddress->building = $request->building ?? null;
        $userAddress->appartment = $request->appartment ?? null;
        $userAddress->floor = $request->floor ?? null;
        $userAddress->door_password = $request->door_password ?? '';
        $userAddress->save();

        return [
            'status' => true,
            'message' => "User Address updated successfully!",
            'data' => $userAddress,
        ];
    }

    public function removeAddress(Request $request)
    {
        $request->validate(["address_id" => 'required']);

        $cards = UserAddress::find($request->address_id)->delete();
        return [
            'status' => true,
            'message' => "Address removed successfully",
        ];
    }
    public function makeAddressDefault(Request $request)
    {
        $request->validate(["address_id" => 'required']);

        $old_card = UserAddress::where('user_id', $request->user()->id)->update(['is_default' => 0]);
        $user = UserAddress::where('id', $request->address_id)->update(['is_default' => 1]);
        return [
            'status' => true,
            'message' => "User default Address updated successfully",
        ];
    }

    public function getPickupTimeslots(Request $request)
    {

        $slots = Setting::where(['key' => 'pickup_slots'])->pluck('value')->first();
        $slots = json_decode($slots, true);
        // dd($slots);
        return [
            'status' => true,
            'message' => "Data get successfully",
            "data" => [
                'timeslots' => $slots
            ]
        ];
    }

    public function getDropoffTimeslots(Request $request)
    {
        $rules = ["day" => 'required'];
        $validated = $request->validate($rules);
        if ($validated !== true) {
            return $validated;
        }
        $slots = TimeSlot::where(['day' => $request->day, 'type' => 'Dropoff'])->get();
        return [
            'status' => true,
            'message' => "Data get successfully",
            "data" => [
                'timeslots' => $slots
            ]
        ];
    }


    ///Extras
    public function profile(Request $request)
    {
        $userId = auth()->user()->id;
        $user = User::find($userId);
        $active_orders = Order::where(['user_id' => $userId, 'status' => 'Pending'])->count();

        if (!$user) {
            return [
                'status' => false,
                'message' => "User not found!",
                'data' => null
            ];
        }

        return [
            'status' => true,
            'message' => "User profile retrieved successfully!",
            'data' => [
                "user" => $user->toArray(),
                "active_orders" => $active_orders,
            ]
        ];
    }

    public function updateLocation(Request $request)
    {
        $rules = [
            "lat" => "required",
            "lng" => "required",
        ];
        Log::info("updateLocation", $request->all());
        $validated = $request->validate($rules);

        // تحديث جدول users
        $user = User::whereId($request->user()->id)->update([
            "lat" => $request->lat,
            "lng" => $request->lng,
            // "address" => $request->address,
        ]);

        // تحديث آخر عنوان للمستخدم في جدول user_address
        $userAddress = UserAddress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'deleted_at' => null
            ],
            [
                'lat' => $request->lat ?? null,
                'lng' => $request->lng ?? null
            ]
        );

        Log::info("updateLocation after update", $request->all());
        if ($user) {
            return [
                'status' => true,
                'message' => "User Location updated successfully!",
            ];
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            "first_name" => "required",
            "gallery.*" => "image|mimes:jpeg,png,jpg|max:2048",
            "picture" => "nullable|image|mimes:jpeg,png,jpg|max:2048",
            "leave_at_the_door" => "nullable|in:0,1,true,false",
            "hand_over_directly" => "nullable|in:0,1,true,false",
            "call_upon_arrival" => "nullable|in:0,1,true,false",
            "dont_call" => "nullable|in:0,1,true,false",
            "dont_ring_the_doorbell" => "nullable|in:0,1,true,false",
        ]);

        $data = [];

        if ($image = $request->file('picture')) {
            $imageName = date('ymdhis') . "_user." . $image->getClientOriginalExtension();
            $image->storeAs('uploads', $imageName, 'public'); // Use Laravel storage
            $data['picture'] = $imageName;
        }

        if ($request->hasFile('gallery')) {
            $gallery = [];
            foreach ($request->file('gallery') as $image) {
                $imageName = date('ymdhis') . "_gallery_" . uniqid() . "." . $image->getClientOriginalExtension();
                $image->storeAs('uploads', $imageName, 'public'); // Use Laravel storage
                $gallery[] = $imageName;
            }
            $data['gallery'] = $gallery;
        }

        $fields = [
            'first_name',
            'last_name',
            'phone',
            'gender',
            'referral_code',
            'leave_at_the_door',
            'hand_over_directly',
            'call_upon_arrival',
            'dont_call',
            'dont_ring_the_doorbell',
            'password'
        ];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->$field;

                if($field=='password'){
                    $data['password'] = Hash::make($request->password);
                }
            }
        }

        $data['email'] = $request->email ?? 'N/A';

        if ($request->referral_code) {
            Log::info("referral_code: " . $request->referral_code);
            $data['referral_used'] = $request->referral_code;
        }

        $user = User::find($request->user()->id);

        if (blank($user->phone)) {
            return response()->json([
                'status' => false,
                'message' => "No phone found, please contact support",
                'data' => []
            ], 400);
        }

        $user->update($data);

        return [
            'status' => true,
            'message' => "Profile updated successfully!",
            'data' => [
                "user" => $user
            ],
        ];
    }
    public function updateFcmToken(Request $request)
    {
        $request->validate([
            "fcm_token" => "required",
        ]);

        auth('sanctum')->user()->update([
            "fcm_token" => $request->fcm_token,
        ]);

        return response()->json(['status' => true, 'message' => "FCM Token updated successfully."]);
    }

    public function deleteAccount(Request $request)
    {

        $user = User::where('id', auth()->user()->id)->delete();

        auth()->user()->tokens()->delete();
        return [
            'status' => true,
            'message' => "Account is deleted successfully!",
        ];
    }


    public function getNotifications(Request $request)
    {
        $title_field = (isset($request->language) && $request->language == 'ar') ? 'title_ar as title' : 'title';
        $message_field = (isset($request->language) && $request->language == 'ar') ? 'message_ar as message' : 'message';


        $notifications = Notification::select('id', $title_field, $message_field, 'created_at', 'is_read')
            ->where(['user_id' => auth()->user()->id]);

        if (isset($request->unread) && $request->unread == 1) {
            $notifications->where('is_read', 0);
        }
        $notifications = $notifications->latest('id')->get();

        return [
            "status" => true,
            "message" => "Notifications get successfully!",
            "data" => [
                "notifications" => $notifications,
            ]
        ];
    }
    public function clearAllNotifications(Request $request)
    {

        $notifications = Notification::where(['user_id' => auth()->user()->id])
            ->delete();

        return [
            "status" => true,
            "message" => "Notifications cleared successfully!",
            "data" => []
        ];
    }

    public function markasReadNotification(Request $request)
    {
        $request->validate([
            "notification_id" => "required",
        ]);

        $notification = Notification::findOrFail($request->notification_id)->update([
            "is_read" => 1
        ]);
        if ($notification) {
            return [
                'status' => true,
                'message' => "Success! Notification is marked as read",
                'data' => [],
            ];
        }
    }

    public function submitContact(Request $request)
    {
        $rules = ["name" => 'required', 'email' => 'required', 'message' => 'required'];
        $validated = $request->validate($rules);
        if ($validated !== true) {
            return $validated;
        }
        $contact = UserContact::create([
            "name" => $request->name,
            'email' => $request->email,
            'message' => $request->message,
            'user_id' => auth()->user()->id,
        ]);
        return [
            'status' => true,
            'message' => "Contact Submitted successfully",
            'data' => $contact
        ];
    }

    public function isLastOrderReviewed(Request $request)
    {
        $user = auth('sanctum')->user();

        $lastOrder = $user->orders()->where('status', 'DELIVERED')->latest()->first();

        $is_reviewed = true;

        if ($lastOrder) {
            $is_reviewed = Ticket::whereRelation('category', 'name', 'Rating')
                ->where('order_code', $lastOrder->order_code)
                ->exists();
        }

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'order_id' => $lastOrder?->id,
                'is_reviewed' => $is_reviewed,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\FCMService;
use App\Services\ResendService;
use App\Models\Notification;
use App\Models\DriverNotification;
use App\Models\VendorNotification;
use App\Models\AdminNotification;
use App\Models\Vendor;
use App\Models\Driver;
use App\Models\FcmToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public static function sendNotifications($data, $to)  {
        // Add validation to ensure required data exists
        if ($to !== "admin" && (!isset($data['user']) || is_null($data['user']))) {
            Log::error('sendNotifications called with null user data', [
                'to' => $to,
                'data' => $data
            ]);
            return;
        }

        $noti_data=[
            "title"=>$data['title'],
            "title_ar"=>$data['title_ar'] ?? '',
            "message"=>$data['message'],
            "message_ar"=>$data['message_ar'] ?? '',
        ];

        if($to=="user"){
            $noti_data['user_id']=$data['user']['id'];
            Notification::create($noti_data);
        }
        elseif($to=="driver"){
            $noti_data['driver_id']=$data['user']['id'];
            DriverNotification::create($noti_data);
        }
        else if($to=="vendor"){
            $noti_data['vendor_id']=$data['user']['id'];
            VendorNotification::create($noti_data);
        }
        else if($to=="admin"){
            AdminNotification::create([
                "title"=>$data['title'],
                "message"=>$data['message'],
                "link"=> $data['link'] ?? null,
            ]);
        }

        // Send FCM to User, Partner, Driver Vendor not Admin
        if($to!="admin"){
            if($to=="user"){
                $device_token=FcmToken::where('user_id',$data['user']['id'])->pluck('token')->toArray();
            }else{
                $device_token = isset($data['user']['deviceToken']) ? [$data['user']['deviceToken']] : [];
            }

            $fcmData=[
                "title"=> ($data['user']['app_lang']=="ar")?$data['title_ar']:$data['title'],
                "body"=> ($data['user']['app_lang']=="ar")?$data['message_ar']:$data['message'],
            ];

            if(isset($data['user']['notification_enable']) && $data['user']['notification_enable']==1 && !empty($device_token)){
                $fcm = new FCMService();
                $fcm->send($device_token, $fcmData);
            }
        }

        // Send Email if set
        if (isset($data['mail']) && isset($data['user']['email'])) {
            try {
                // ResendService::send($data['user']['email'],$data);
            } catch (\Exception $ex) {
                Log::info('Error in Controller.php sending email: '.$ex->getMessage());
            }
        }
    }


    public function getAreaVendor($userLat, $userLng)  {
        // dd($userLat, $userLng);

        // Get all vendors with their area coordinates
        $vendors = Vendor::where(['is_approved'=>1, 'status' => 1, 'deleted_at' => null])->get(); //'status'=>1, 'deleted_at'=>null
        // Iterate through each vendor and check if the user's location is inside the vendor's area
        foreach ($vendors as $vendor) {
            // $area = json_decode($vendor->areas, true);
            if ($this->isPointInPolygon($vendor->areas, $userLat, $userLng)) {
                return $vendor; // Return the first vendor found within the user's area
            }
        }

        return null;
    }

    public function getNearbyVendor($userLat, $userLng)  {
        // dd($userLat, $userLng);
        $radius=1000;
        $nearbyVendors = Vendor::where(['status'=>1,'is_approved'=>1,'deleted_at'=>null])->selectRaw("*,
        6371 * acos(
            cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) +
            sin(radians(?)) * sin(radians(lat))
        ) AS distance", [$userLat, $userLng, $userLat])
        ->having('distance', '<', $radius)
        ->orderBy('distance')
        ->first();
        return $nearbyVendors;
    }

    public function getNearbyDriver($vendorId,$userLat, $userLng)  {
        // Account for many-to-many relation between drivers and vendors via driver_vendor pivot
        $radius = 1000;

        $nearbyDriver = Driver::where('status', 1)
            ->whereNull('deleted_at')
            ->whereHas('vendors', function($query) use ($vendorId) {
                $query->where('vendors.id', $vendorId);
            })
            ->with(['vendors' => function($query) use ($vendorId) {
                $query->where('vendors.id', $vendorId);
            }])
            ->selectRaw("drivers.*,
        6371 * acos(
            cos(radians(?)) * cos(radians(drivers.lat)) * cos(radians(drivers.lng) - radians(?)) +
            sin(radians(?)) * sin(radians(drivers.lat))
        ) AS distance", [$userLat, $userLng, $userLat])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->first();

        return $nearbyDriver;
    }

    public function getNearbyDrivers($vendorId,$userLat, $userLng)  {
        // Account for many-to-many relation between drivers and vendors via driver_vendor pivot
        $radius = 1000;

        $nearbyDrivers = Driver::where('status', 1)
            ->whereNull('deleted_at')
            ->whereHas('vendors', function($query) use ($vendorId) {
                $query->where('vendors.id', $vendorId);
            })
            ->with(['vendors' => function($query) use ($vendorId) {
                $query->where('vendors.id', $vendorId);
            }])
            ->selectRaw("drivers.*,
        6371 * acos(
            cos(radians(?)) * cos(radians(drivers.lat)) * cos(radians(drivers.lng) - radians(?)) +
            sin(radians(?)) * sin(radians(drivers.lat))
        ) AS distance", [$userLat, $userLng, $userLat])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();

        return $nearbyDrivers;
    }
    // Helper function to check if a point is inside a polygon
    public function isPointInPolygon($polygon, $latitude, $longitude) {
        $inside = false;
        $x = $longitude;
        $y = $latitude;
        $n = count($polygon);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                         ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }


}

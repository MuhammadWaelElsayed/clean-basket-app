<?php

namespace App\Http\Controllers\API\B2b;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessOrderJob;
use App\Models\AddOn;
use App\Models\Driver;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderPriority;
use App\Models\OrderTracking;
use App\Models\Service;
use App\Models\SettingsServiceFee;
use App\Models\UserAddress;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user('b2b');

        $addresses = $client->addresses()->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    public function show(Request $request, $id)
    {
        $client = $request->user('b2b');

        $address = $client->addresses()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'address_type' => 'required|in:Home,Office',
            'street_no' => 'required|string',
            'house_no' => 'required|string',
            'area' => 'required|exists:areas,id',
            'building' => 'required|string',
            'appartment' => 'required|string',
            'floor' => 'nullable|string',
            'lat' => 'required|string',
            'lng' => 'required|string',
        ]);

        $client = $request->user('b2b');

        $data['user_id'] = 0;
        $data['client_id'] = $client->id;
        $data['is_default'] = 0;
        $data['created_at'] = now();

        $vendor = $this->getAreaVendor($data['lat'], $data['lng']);

        if($vendor){
            $data['vendor_id'] = $vendor->id;
        }

        $address = $client->addresses()->create($data);

        return response()->json([
            'success' => true,
            'data' => $address
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'address_type' => 'nullable|in:Home,Office',
            'street_no' => 'nullable|string',
            'house_no' => 'nullable|string',
            'area' => 'nullable|exists:areas,id',
            'building' => 'nullable|string',
            'appartment' => 'nullable|string',
            'floor' => 'nullable|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
            'set_default' => 'nullable'
        ]);

        if (isset($data['set_default']) && $data['set_default'] == 1) {
            unset($data['set_default']);
            $data['is_default'] = 1;
        }

        $client = $request->user('b2b');
        $address = $client->addresses()->findOrFail($id);
        $address->update($data);

        $vendor = $this->getAreaVendor($data['lat'], $data['lng']);

        if($vendor){
            $address->update(['vendor_id' => $vendor->id]);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $client = $request->user('b2b');
        $address = $client->addresses()->findOrFail($id);
        $address->delete();
        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    public function getAreaVendor($userLat, $userLng)  {
        // dd($userLat, $userLng);

        // Get all vendors with their area coordinates
        $vendors = Vendor::where(['status'=>1, 'is_approved'=>1, 'deleted_at'=>null])->get();
        // Iterate through each vendor and check if the user's location is inside the vendor's area
        foreach ($vendors as $vendor) {
            // $area = json_decode($vendor->areas, true);
            if ($this->isPointInPolygon($vendor->areas, $userLat, $userLng)) {
                return $vendor; // Return the first vendor found within the user's area
            }
        }

        return null;
    }

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

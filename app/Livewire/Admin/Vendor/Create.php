<?php

namespace App\Livewire\Admin\Vendor;

use Livewire\Component;

use App\Models\City;
use App\Models\Vendor;
use App\Models\Area;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class Create extends Component
{
    use WithFileUploads;
    public $areas=[];
    public $eId=null;
    //Form Fields
    public $first_name='';
    public $last_name='';
    public $business_name='';
    public $email=null;
    public $phone=null;
    public $about=null;
    public $city=null;
    public $area=null;
    public $location=null;
    public $lat=null;
    public $lng=null;
    public $commission=null;
    public $image=null;
    public $imageUrl=null;
    public $vendor_areas=[];



    protected $listeners = ['addressChanged' => 'updateLatLng','areasChanged', 'createDefaultCircleFromUI'];

    public $rules=null;


    public function mount($id=null)
    {

        abort_unless(auth()->user()->can('create_partner'), 403);

        $this->imageUrl=asset('uploads/blank.png');

        if($id > 0){
            $this->eId=$id;
            $vendor=Vendor::findOrFail($this->eId);
            $this->first_name=$vendor->first_name;
            $this->last_name=$vendor->last_name;
            $this->business_name=$vendor->business_name;
            $this->email=$vendor->email;
            $this->phone=$vendor->phone;
            $this->about=$vendor->about;
            $this->imageUrl=$vendor->picture;
            $this->commission=$vendor->commission;

            $this->city=$vendor->city_id;
            $this->getCityAreas();
            $this->location=$vendor->location;
            $this->lat=$vendor->lat;
            $this->lng=$vendor->lng;
            $this->vendor_areas= $vendor->areas;
            $this->dispatch('defaultAreas', $this->vendor_areas);
        }

        $this->rules=[
            "email"=>'required|unique:vendors,email,'.$this->eId.',id,deleted_at,NULL',
            "business_name"=>'required|unique:vendors,business_name,'.$this->eId.',id,deleted_at,NULL',
            "phone"=>'regex:/^966\d{7,10}$/|required|unique:vendors,phone,'.$this->eId.',id,deleted_at,NULL',
            "first_name"=>'required',
            "city"=>'required',
            "location"=>'required',
            "commission"=>'required',
        ];
    }

    public function render()
    {
        $cities=City::select('id as value','name as label')->where('status',1)->whereNull('deleted_at')->get()->toArray();
        $cities= json_encode($cities);
        // dd($cities);
        return view('livewire.admin.vendors.create',compact('cities'))
        ->layout('components.layouts.admin-dashboard');

    }

    public function updated($field)
    {

        $this->validateOnly($field,$this->rules);
    }

    public function store()
    {
         Log::debug('▶ vendor_areas in store():', ['vendor_areas' => $this->vendor_areas]);

        $this->validate($this->rules);
        Log::debug('[Livewire] In store(), vendor_areas =', ['vendor_areas' => $this->vendor_areas]);

        $vendor_areas = is_string($this->vendor_areas)
        ? json_decode($this->vendor_areas, true)
        : $this->vendor_areas;
        Log::debug('[Livewire] vendor_areas =', ['vendor_areas' => $vendor_areas]);

        // إذا كان إنشاء جديد ولم يتم تحديد مناطق، قم بإنشاء دائرة نصف قطرها 6 كم
        if (!$this->eId && (empty($vendor_areas) || count($vendor_areas) === 0)) {
            $vendor_areas = $this->createDefaultCircle(6); // 6 كيلومترات
        }

        // Save areas as received from frontend (array of arrays)
        $data=[
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'business_name' => $this->business_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'city_id' => $this->city,
            'about' => $this->about,
            'commission' => $this->commission,
            'location' => $this->location,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'areas' => $vendor_areas, // no conversion
        ];
        // dd($data);
        Log::debug('[Livewire] Data being saved for vendor:', $data);

        if($this->image){
            $imageName = date('ymdhis')."_vendor." . $this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('public/uploads', $imageName);
            $data['picture']=$imageName;
        }

        if($this->eId >0){
            $user = Vendor::findOrFail($this->eId)->update($data);
            $this->dispatch('success', 'Partner updated successfully!');
        }else{
            $data['password']= bcrypt(123456);
            $user = Vendor::create($data);
            $this->dispatch('success', 'Partner registered successfully!');
        }
        return $this->redirectRoute('admin.partners', navigate: true);
    }

    public function getCityAreas()
    {
        $this->areas=Area::select('id as value','name as label')->where('city_id',$this->city)
        ->where('status',1)->whereNull('deleted_at')->get()->toArray();
        $this->dispatch('updateAreas', $this->areas);

    }


    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

    public function updateLatLng($locationText='',$lat='', $lng='')
    {
        $this->location = $locationText;
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function areasChanged($areas)
    {
        Log::debug('areasChanged payload:', ['areas' => $areas]);
        $this->vendor_areas = $areas;
        Log::debug('vendor_areas after update:', ['vendor_areas' => $this->vendor_areas]);
    }

    /**
     * إنشاء دائرة افتراضية حول موقع المغسلة
     * @param float $radiusKm نصف قطر الدائرة بالكيلومترات
     * @return array مصفوفة من النقاط التي تشكل الدائرة
     */
    private function createDefaultCircle($radiusKm = 6)
    {
        if (!$this->lat || !$this->lng) {
            return [];
        }

        $centerLat = $this->lat;
        $centerLng = $this->lng;
        $radius = $radiusKm; // بالكيلومترات

        // عدد النقاط في الدائرة (كلما زاد العدد، كلما كانت الدائرة أكثر دقة)
        $numPoints = 32;

        $points = [];

        for ($i = 0; $i < $numPoints; $i++) {
            $angle = ($i / $numPoints) * 2 * M_PI;

            // حساب النقطة على الدائرة
            $lat = $centerLat + ($radius / 111.32) * cos($angle);
            $lng = $centerLng + ($radius / (111.32 * cos(deg2rad($centerLat)))) * sin($angle);

            $points[] = [
                'lat' => round($lat, 6),
                'lng' => round($lng, 6)
            ];
        }

        // إغلاق الدائرة بإضافة النقطة الأولى مرة أخرى
        if (count($points) > 0) {
            $points[] = $points[0];
        }

        Log::debug('Created default circle:', [
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
            'radius' => $radiusKm . 'km',
            'points_count' => count($points)
        ]);

        return $points;
    }

    /**
     * إنشاء دائرة افتراضية عند الطلب من الواجهة
     */
    public function createDefaultCircleFromUI()
    {
        if ($this->lat && $this->lng) {
            $this->vendor_areas = $this->createDefaultCircle(6);
            $this->dispatch('defaultAreas', $this->vendor_areas);
        }
    }

}

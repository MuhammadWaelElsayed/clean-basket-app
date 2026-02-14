<?php

namespace App\Livewire\Landing;

use Livewire\Component;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Arbitrator;

use App\Models\VendorCategory;
use App\Models\VendorSubCategory;
use App\Models\VendorArbitrator;
use App\Models\VendorJurisdiction;
use App\Models\Jurisdiction;
use App\Models\Vendor;
use App\Models\Package;
use App\Models\VendorPackage;
use App\Models\InviteVendor;
use App\Models\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class BecomePartner extends Component
{
    use WithFileUploads;
    public $sub_categories=[];

    public $name='';
    public $is_company=1;
    public $company_name='';
    public $email=null;
    public $phoneCode='+971';
    public $phone=null;
    public $min_case_value=null;
    public $cases_won=null;
    public $country=null;
    public $city=null;
    public $nationality=null;
    public $vendor_categories=[];
    public $vendor_sub_categories=[];
    public $vendor_arbitrators=[];
    public $vendor_jurisdictions=[];
    public $vendor_languages=[];
    public $about='';
    public $image;
    public $certificate;
    public $license;
    public $privacy_policy;
    public $partner=null;
    public $invite=null;
    public $status=200; //OK
    public $rules=[
        "email"=>'required|email|unique:vendors,email,NULL,id,deleted_at,NULL',
        "name"=>'required',
        "phone"=>'digits_between:9,12|required|unique:vendors,phone,NULL,id,deleted_at,NULL',
        "country"=>'required',
        // "min_case_value"=>'required',
        "cases_won"=>'required',
        // "certificate"=>'required|max:2048',
        "license"=>'required|max:2048',
        "nationality"=>'required',
        "vendor_languages"=>'required',
        "vendor_arbitrators"=>'required|array|min:1',
        "vendor_jurisdictions"=>'required|array|min:1',
        "vendor_categories"=>'required|array|min:1',
        "vendor_sub_categories"=>'required',
        "about"=>'required',
        "privacy_policy"=>"required"
    ];
    // public $messages;
   

    public function mount()
    {
        if(app()->getLocale()=="ar"){
            $this->messages = [
                "name.required" => __("errors")['name']['required'],
                "email.required" => __("errors")['email']['required'],
                "email.unique" => __("errors")['email']['unique'],
                "email.email" => __("errors")['email']['email'],
                "phone.required" => __("errors")['phone']['required'],
                "phone.unique" => __("errors")['phone']['unique'],
                "phone.digits_between" =>  __("errors")['phone']['digits_between'],
                "country.required" => __("errors")['country']['required'],
                // "min_case_value.required" => __("errors")['min_case_value']['required'],
                "cases_won.required" => __("errors")['cases_won']['required'],
                // "certificate.required" => __("errors")['certificate']['required'],
                "license.required" => __("errors")['license']['required'],
                "nationality.required" => __("errors")['nationality']['required'],
                "vendor_languages.required" => __("errors")['vendor_languages']['required'],
                "vendor_arbitrators.required" => __("errors")['vendor_arbitrators']['required'],
                "vendor_jurisdictions.required" => __("errors")['vendor_arbitrators']['required'],
                "vendor_categories.required" => __("errors")['vendor_categories']['required'],
                "vendor_sub_categories.required" => __("errors")['vendor_sub_categories']['required'],
                "about.required" =>__("errors")['about']['required'],
                "privacy_policy.required" => __("errors")['privacy_policy']['required'],
            ];
        }
        if(isset($_GET['invite'])){
            $invite= InviteVendor::with('company')->find($_GET['invite']);
            if($invite==null){
                $this->status=404; // Not found or deleted from company
                return 1;
            }else{
                $this->invite=$invite;
            }
            $isAlready= Vendor::where(['email'=>$invite->email])->whereNull('deleted_at')->count();
            if($isAlready > 0){
                $this->status=409 ; // Already Exist
            }
            $this->is_company=0;
            $this->email= $this->invite->email ?? '';
        }
    }
      

    public function render()
    {

        $countries=Controller::getCountries();
        $languages=Controller::getLanguages();
        $nameField="name";
        if(app()->getLocale()=="ar"){
            $nameField="name_ar as name";
        }
        $categories=Category::select('id', $nameField)->orderBy('sort_order')->where('status',1)->whereNull('deleted_at')->get();
        $arbitrators=Arbitrator::select('id', $nameField)->orderBy('sort_order')->where('status',1)->whereNull('deleted_at')->get();
        $jurisdictions=Jurisdiction::select('id',$nameField)->orderBy('sort_order')->where('status',1)->whereNull('deleted_at')->get();

        return view('livewire.landing.become-partner',compact('categories','countries','languages','arbitrators','jurisdictions'))
        ->layout('components.layouts.landing');
    }

    public function updated($field)
    {
        $this->validateOnly($field,$this->rules);
    }

    public function store()
    {

        $this->validate($this->rules);
        $data=[
            'is_company' => $this->is_company,
            'company_name' => ($this->is_company)?$this->name:'',
            'name' => $this->name,
            'email' => $this->email,
            'phone' =>  str_replace("+", "", $this->phoneCode.$this->phone),
            'city' => $this->city,
            'country' => $this->country,
            'nationality' => $this->nationality,
            'languages' => json_encode($this->vendor_languages),
            'cases_won' => $this->cases_won,
            'about' => $this->about,
            'password'=>    bcrypt(123456),
        ];
        if ($this->invite!=null) {
           $data['company_id']=$this->invite->vendor_id ?? 0;
        }
        
        if($this->image){
            $imageName = date('ymdhis')."_vendor." . $this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('public/uploads', $imageName);
            $data['picture']=$imageName;  
        }
        if($this->certificate){
            $imageName = date('ymdhis')."_certificate." . $this->certificate->getClientOriginalExtension();
            $path = $this->certificate->storeAs('public/uploads', $imageName);
            $data['certificate']=$imageName;  
        }
        if($this->license){
            $imageName = date('ymdhis')."_license." . $this->license->getClientOriginalExtension();
            $path = $this->license->storeAs('public/uploads', $imageName);
            $data['license']=$imageName;  
        }

        $user = Vendor::create($data);
       
        foreach ($this->vendor_categories as $key => $cat) {
            VendorCategory::create([
                'vendor_id'=>$user->id,
                'category_id'=>$cat,
            ]);
        }
        foreach ($this->vendor_sub_categories as $key => $cat) {
            VendorSubCategory::create([
                'vendor_id'=>$user->id,
                'sub_category_id'=>$cat,
            ]);
        }
        foreach ($this->vendor_arbitrators as $key => $cat) {
            VendorArbitrator::create([
                'vendor_id'=>$user->id,
                'arbitrator_id'=>$cat,
            ]);
        }
        foreach ($this->vendor_jurisdictions as $key => $cat) {
            VendorJurisdiction::create([
                'vendor_id'=>$user->id,
                'jurisdiction_id'=>$cat,
            ]);
        }

        $data=[
            "title"=>"New Partner Registered",
            "message"=> "New Partner Registered. View thier profile to approve it.",
            "link" => "/admin/vendors/".$user->id,
            "mail" => [
                "template"=>"new_vendor"
            ],
            "user"=>[
                "name"=> env('APP_NAME')." Admin",
                "email"=> env('ADMIN_EMAIL')
            ],
            "vendor"=>$user
        ];
        // dd($vendors);
        Controller::sendNotifications($data,'admin');

        $data=[
            "title" => "Account created successfully!",
            "message" => "Thank you for registering. Once your account is verified, you will receive a confirmation email, granting you access to all the features of our platform.",
            "mail" => [
                "template"=>"vendor_register"
            ],
            "user" => $user
        ];
        \Mail::to($user->email)->send(new \App\Mail\CommonEmail($data));
        //Update Invite Status
        if($this->invite){
            $invite= InviteVendor::find($this->invite->id)->update(["status"=>"Accepted"]);
        }

        $this->dispatch('success', __('partner_registered'));
        return redirect('/');
    }
    public function getSubCategories()
    {
        $nameField="name";
        if(app()->getLocale()=="ar"){
            $nameField="name_ar as name";
        }
        $this->sub_categories=SubCategory::select('id',$nameField)->whereIn('category_id',$this->vendor_categories)
        ->where('status',1)->whereNull('deleted_at')->get();
        $this->dispatch('updateSubCategories', $this->sub_categories);
        // dd($this->vendor_categories);

    }

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }
    public function updatedCertificate(TemporaryUploadedFile $file)
    {
        $this->certificate = $file;
        // dd($file->getClientOriginalName());
    }
    public function updatedLicense(TemporaryUploadedFile $file)
    {
        $this->license = $file;
    }
    public function setCompany()
    {
        $this->is_company=($this->is_company==1)?0:1;
    }
}

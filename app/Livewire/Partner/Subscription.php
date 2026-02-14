<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Vendor;
use App\Models\VendorPackage;
use App\Models\Package;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;

class Subscription extends Component
{
   
    public $tab='email';

    public $listeners = ['cancel'];
   
    public function render()
    {
        $package=VendorPackage::with('package')->where(['vendor_id'=>session('partner')->id,'is_addon'=>0])->latest('id')->first();
        $addons=Package::where(['status'=>1,'is_addon'=>1,'package_for'=>'Company'])->latest('id')->get();

        $my_addons=[];
        foreach ($addons as $key => $addon) {
          $check=VendorPackage::where(['package_id'=>$addon->id,'vendor_id'=>session('partner')->id])->latest('id')->first();
          if($check!=null){
            if($check->is_cancelled==1){
              $my_addons[$addon->id]=["status"=>"Cancelled","expired_at"=>$check->expired_at];
            }
            elseif ($check->expired_at < date('Y-m-d h:i')){
              $my_addons[$addon->id]=["status"=>"Expired","expired_at"=>$check->expired_at];
            }
            else{
              $my_addons[$addon->id]=["status"=>"Active","expired_at"=>$check->expired_at];
            }
          }
          
        }
        
        return view('livewire.partner.my-subscription',compact('package','addons','my_addons'))->layout('components.layouts.partner-dashboard');
    }

    public function cancel()  {
      VendorPackage::where(['vendor_id'=>session('partner')->id,'is_addon'=>0])->latest('id')->first()->update([
        "is_cancelled" => 1
      ]);
      Vendor::find(session('partner')->id)->update([
        "min_case_value"=> null
    ]);

      AdminNotification::create([
          'title' => 'Vendor Cancelled his Subscription ',
          'message' => session('partner')->name." cancelled his subscription plan",
          "link" => "/admin/subscription-history"
      ]);
      $data=[
        "title"=>"You have cancelled your current subscription",
        "title_ar"=>"لقد قمت بإلغاء اشتراكك الحالي",
        "message"=> "You have cancelled your current subscription",
        "message_ar"=> "لقد قمت بإلغاء اشتراكك الحالي",
      ];
      // dd($vendors);
      $data['user']= session('partner');
      Controller::sendNotifications($data,'vendor');
      
      $this->dispatch('success', 'Subscription is cancelled Successfully!');
    }
}

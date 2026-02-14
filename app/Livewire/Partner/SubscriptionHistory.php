<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Vendor;
use App\Models\VendorPackage;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class SubscriptionHistory extends Component
{
   
    public $tab='email';

    public $listeners = ['cancel'];
   
    public function render()
    {
        $package=VendorPackage::with('package')->where('vendor_id',session('partner')->id)->latest('id')->first();
        
        return view('livewire.partner.my-subscription',compact('package'))->layout('components.layouts.partner-dashboard');
    }

    public function cancel()  {
      VendorPackage::where('vendor_id',session('partner')->id)->latest('id')->first()->update([
        "is_cancelled" => 1
      ]);
      $this->dispatch('success', 'Subscription is cancelled Successfully!');
    }
}

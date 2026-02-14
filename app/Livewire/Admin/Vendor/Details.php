<?php

namespace App\Livewire\Admin\Vendor;

use Livewire\Component;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Details extends Component
{

    public $name='';
    public $itemId;
    public $item;
    public $drivers;
    public $orders;
    public $statuses=null;
    public $tab="details";

    public function mount($id)
    {
        abort_unless(auth()->user()->can('view_partner'), 403);

        $this->statuses=config('order_status');

        $this->itemId = $id;
        $this->item=Vendor::with(['city','area'])->find($this->itemId);

        // التحقق من وجود الشريك
        if (!$this->item) {
            abort(404, 'Partner not found');
        }
    }

    public function render()
    {
        if($this->tab=="drivers"){
            // Updated to respect many-to-many relation between drivers and vendors via driver_vendor pivot
            $this->drivers = Driver::query()
                ->where(['status' => 1])
                ->whereNull('deleted_at')
                ->join('driver_vendor', 'drivers.id', '=', 'driver_vendor.driver_id')
                ->where('driver_vendor.vendor_id', $this->itemId)
                ->select('drivers.*')
                ->latest('drivers.id')
                ->get();
        }
        if($this->tab=="orders"){
            $this->orders=Order::where(['vendor_id'=>$this->itemId])->latest()->get();
        }

        return view('livewire.admin.vendors.show')->layout('components.layouts.admin-dashboard');
    }



}

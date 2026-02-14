<?php

namespace App\Livewire\Partner\Order;

use Livewire\Component;
use App\Models\Service;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Details extends Component
{
    use WithFileUploads;
    
    public $name='';
    public $orderId;
    public $order;
    public $statuses = '';
    public $bill = '';
    
    public function mount($id)
    {
        $this->orderId = $id;
        $this->statuses = config('order_status');
       
    }

    public function render()
    {
        $this->order=Order::with(['user','orderItems','deliveryAddress'])->find($this->orderId);

        return view('livewire.partner.orders.show')->layout('components.layouts.partner-dashboard');
    }

    public function updateBill()
    {
        $this->validate([
            "bill"=>"required|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);
        if($this->bill){
            $imageName = date('ymdhis')."_item." . $this->bill->getClientOriginalExtension();
            $path = $this->bill->storeAs('public/uploads', $imageName);

            Order::findOrFail($this->orderId)->update([
                "bill"=>$imageName
            ]);
            $this->dispatch('success', 'Order bill added Successfully!');
        }

       
    }

   
}

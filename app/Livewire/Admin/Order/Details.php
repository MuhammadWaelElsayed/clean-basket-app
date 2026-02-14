<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderTracking;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Details extends Component
{

    public $lang='en';
    public $name='';
    public $orderId;
    public $order;
    public $statuses = null;
    public $print = false;

    public function mount($id)
    {
        abort_unless(auth()->user()->can('view_order'), 403);


        $this->statuses=config('order_status');
        $this->orderId = $id;
        $this->order=Order::with(['user', 'client', 'orderItems','deliveryAddress', 'walletTransactions', 'paymentLogs'])->find($this->orderId);
        if(isset($_GET['print'])){
            $this->print=true;
        }

    }

    public function render()
    {
        if ($this->print) {
            if(isset($_GET['language']) && $_GET['language']=='ar'){
                $this->lang='ar';
            }
            return view('livewire.admin.orders.print-slip')->layout('components.layouts.login');
        }else{
            $order_pickup=OrderTracking::where('status','PICKED_UP')->pluck('created_at')->first();
            $order_delivery=OrderTracking::where('status','DELIVERED')->pluck('created_at')->first();
            // dd($order_pickup);
            return view('livewire.admin.orders.show',compact('order_pickup','order_delivery'))->layout('components.layouts.admin-dashboard');
        }
    }



}

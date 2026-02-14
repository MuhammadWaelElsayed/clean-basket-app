<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Dashboard extends Component
{
    
    public $chart_data;
    public $stats;
    public $show_pass=false;
    public $completed_orders;
    public $total_orders;

    public function render()
    {
        $this->stats['new_orders'] = Order::where('deleted_at',null)->where('vendor_id',session('partner')->id)->where('status','PLACED')->whereYear('created_at',date('Y'))->count();
        $this->stats['total_order'] = Order::where('deleted_at',null)->where('vendor_id',session('partner')->id)->whereYear('created_at',date('Y'))->count();
        $this->stats['orders_delivered'] = Order::where('vendor_id',session('partner')->id)->where('status','DELIVERED')->count();
        $this->stats['pending_orders'] = Order::where('vendor_id',session('partner')->id)->whereNotIn('status',['PLACED','DELIVERED','CANCELLED'])->count();
        $this->stats['month_orders'] = Order::where('vendor_id',session('partner')->id)->whereMonth('created_at',date('m'))->whereYear('created_at',date('Y'))->count();
        // dd($this->stats);
        for ($i=1; $i <= 12; $i++) { 
            $this->chart_data['month_orders'][] = Order::where('vendor_id',session('partner')->id)->where('deleted_at',null)->whereMonth('created_at',$i)->whereYear('created_at',date('Y'))->count();
        }

      

        return view('livewire.partner.dashboard')->layout('components.layouts.partner-dashboard');
    }

  
   
}

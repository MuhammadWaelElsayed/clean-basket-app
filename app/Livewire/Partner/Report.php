<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Report extends Component
{
    public $monthYear='';
    public $month='';
    public $year='';
    public $data=[];
    public $percentage=[];

    public function mount()
    {
        $this->month=date('m');
        $this->year=date('Y');
        $this->monthYear=date('M');

        if(isset($_GET['month'])){
            $this->monthYear = explode('-',$request->month);
            $this->year = $this->monthYear[0];
            $this->month = $this->monthYear[1];
            $this->monthYear=$request->month;
        }
        // dd($this->monthYear);
    }
    public function render()
    {
        $orderObj=Order::whereNull('deleted_at')->where('vendor_id', session('partner')->id)->whereIn('status',['Delivered'])
        ->whereMonth('created_at',$this->month)->whereYear('created_at',$this->year);

        $data['total_orders']=$orderObj->count();
        $data['total_revenue']=$orderObj->sum('sub_total');
        $data['total_commission']=$orderObj->sum('commission_amount');
        $data['vendorEarning']=$data['total_revenue']-$data['total_commission'];

       
        $this->data=$data;
        $this->percentage=$this->getLastMonthStats($this->month,$this->year,$data);
        // dd($data,$percentage);

        return view('livewire.partner.report')->layout('components.layouts.partner-dashboard');
    }

    public function getLastMonthStats($month,$year,$data)
    {
        $lastMonth=$month-1;
        if ($lastMonth == 0) {
            $lastMonth = 12;
            $year--;
        }
        $orderObj=Order::where('vendor_id', session('partner')->id)->whereIn('status',["Delivered"])->whereMonth('created_at',$lastMonth)->whereYear('created_at',$year);

        $monthStats['total_orders']=$orderObj->count();
        $monthStats['total_revenue']=$orderObj->sum('sub_total');
        $monthStats['vendorEarning']=$orderObj->sum('commission_amount');

        //Get Last Month Total Orders Percentage 
        if($monthStats['total_orders']==0){
            $percentage['orders'] = 100;
        }
        else{
            $percentage['orders'] = ( ($data['total_orders'] - $monthStats['total_orders'])  / $monthStats['total_orders']) * 100;
        }
        //Get Last Month Total Reveneue Percentage 
        if($monthStats['total_revenue']==0){
            $percentage['revenue'] =100;
        }
        else{
            $percentage['revenue'] = (($data['total_revenue'] - $monthStats['total_revenue']) / $monthStats['total_revenue']) * 100;
        }

        //vendor Earning Percentage
        if($monthStats['vendorEarning']==0){
            $percentage['vendorEarning'] =100;
        }
        else{
            $percentage['vendorEarning'] = (($data['vendorEarning'] - $monthStats['vendorEarning']) / $monthStats['vendorEarning']) * 100;
        }
        return $percentage;
    }

   
   
}

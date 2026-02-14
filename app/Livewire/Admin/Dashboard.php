<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Driver;
use App\Models\VendorStat;
use App\Models\VendorPackage;
use App\Models\Order;
use App\Models\CaseInterest;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use League\Csv\Writer;

class Dashboard extends Component
{

    public $chart_data;
    public $stats;
    public $show_pass=false;
    public $completed_orders;
    public $total_orders;

    public function render()
    {

        $this->stats['total_vendors'] = Vendor::where('deleted_at',null)->count();
        $this->stats['total_drivers'] = Driver::where('deleted_at',null)->count();
        $this->stats['total_users'] = User::where('deleted_at',null)->count();
        $this->stats['total_orders'] = Order::where('deleted_at',null)->count();
        $this->stats['new_orders'] = Order::where(['deleted_at'=>null,'status'=>'PLACED'])->count();
        $this->stats['pending_orders'] = Order::where(['deleted_at'=>null])->whereNotIn('status',['PLACED','DELIVERED','CANCELLED'])->count();
        $this->stats['completed_orders'] = Order::where(['deleted_at'=>null])->whereIn('status',['DELIVERED'])->count();

        $this->stats['total_earnings']= Order::where(['deleted_at'=>null])->where('status','DELIVERED')
        ->selectRaw('SUM(sub_total - commission_amount) as earnings')->value('earnings');
        $this->stats['total_sales']= Order::where(['deleted_at'=>null])->where('status','DELIVERED')->sum('grand_total');

        $this->stats['month_earnings']= Order::where(['deleted_at'=>null])->where('status','DELIVERED')->WhereMonth('created_at',date('m'))
        ->selectRaw('SUM(sub_total - commission_amount) as earnings')->value('earnings');
        $this->stats['month_sales']= Order::where(['deleted_at'=>null])->where('status','DELIVERED')->WhereMonth('created_at',date('m'))->sum('grand_total');
        $this->stats['avg_order']= Order::where(['deleted_at'=>null])->where('status','DELIVERED')->avg('grand_total');

        // All Months
        for ($i=1; $i <= 12; $i++) {
            $this->chart_data['month_users'][] = User::where('deleted_at',null)->whereMonth('created_at',$i)->whereYear('created_at',date('Y'))->count();
            $this->chart_data['month_orders'][] = Order::where(['deleted_at'=>null,'status'=>"DELIVERED"])->whereMonth('created_at',$i)->whereYear('created_at',date('Y'))->count();
        }

        // Current Month days
        $monthYear = date('Y-m');
        $last_date= date("t", strtotime($monthYear));
        for ($i=1; $i <= $last_date; $i++) {
            $this->chart_data['dates'][]= intval($i);
            $this->chart_data['orders'][]= Order::where(['deleted_at'=>null])->where('status','DELIVERED')->WhereDate('created_at',$monthYear.'-'.$i)->count();
        }

        $this->stats['top_partners']= Order::with('vendor')->where('status',"DELIVERED")
           ->select('vendor_id', \DB::raw('COUNT(*) as count'))
           ->groupBy('vendor_id')
           ->orderByDesc('count')->limit(5)
           ->get();
        $this->stats['top_users']= Order::with('user')->where('status',"DELIVERED")
           ->select('user_id', \DB::raw('COUNT(*) as count'))
           ->groupBy('user_id')
           ->orderByDesc('count')->limit(5)
           ->get();




        return view('livewire.admin.dashboard')->layout('components.layouts.admin-dashboard');
    }


    public function exportData()
    {
        $packages=VendorPackage::with('package','vendor')->whereHas('vendor')->whereHas('package')->latest('id');

        $data=$packages->get();
        // Define your data
        $columns = [ 'Vendor', 'Package','Purchase Date','Expired Date','Status'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // Add headers to the CSV
        $csv->insertOne($columns);
        foreach ($data as $row) {
            if ($row->is_cancelled==1){
                $status="Cancelled";
            } elseif($row->expired_at < date('Y-m-d h:i')){
                $status="Expired";
            }else{
                $status="Active";
            }
            $single=[
                $row->vendor->name,
                ($row->package!=null)?$row->package->name:'N/A',$row->buy_at,$row->expired_at,
                $status
            ];
            $csv->insertOne($single);
        }
        $filename = 'report_' . date('Y-m-d')  . '.csv';
        // Open a temporary file for writing
        $file = fopen('php://temp', 'w');
        fwrite($file, $csv->getContent());
        rewind($file);
        // Set the appropriate headers for downloading the file
        $headers = [    'Content-Type' => 'text/csv',    'Content-Disposition' => 'attachment; filename="' . $filename . '"',];

        return response()->streamDownload(function () use ($file) {
            fpassthru($file);
        }, $filename, $headers);

    }
}

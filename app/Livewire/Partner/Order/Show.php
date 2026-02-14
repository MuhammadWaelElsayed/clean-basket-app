<?php

namespace App\Livewire\Partner\Order;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithPagination;
use League\Csv\Writer;
use App\Models\Notification;

class Show extends Component
{
    use WithPagination;
    public $aId='';
    public $statusId;
    public $dId='';
    public $status;
    protected $orders=[];
    public $search='';
    public $export=false;
    public $daterange='';
    
    public $statuses = [];
    
    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delOrder'];

    public function render()
    {
        $this->statuses=config('order_status');
        
        $orders=Order::where('vendor_id',session('partner')->id)->orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $orders->where('order_no','LIKE', '%'.$this->search.'%')
                ->orWhere('status','LIKE', '%'.$this->search.'%');
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $orders->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                // dd('her');
                $order=$orders->get();
                $this->export($order);
            }
        $this->orders=$orders->paginate(15);

        return view('livewire.partner.orders.index',[
            'orders' => $this->orders
        ])->layout('components.layouts.partner-dashboard');
    }

    public function updated($field)
    {
        $orders=Order::orderBy('id','desc')->whereNull('deleted_at')->paginate(15);
    }

   
    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';
        
    }

    public function gotoDetails($id) {
        return redirect('partner/order-details/'.$id);
    }

    public function exportData()
    {
        $orders=Order::with('user')->orderBy('id','desc')->whereNull('deleted_at');

        if($this->search!==''){
            $orders->where('order_no','LIKE', '%'.$this->search.'%')
            ->orWhere('status','LIKE', '%'.$this->search.'%');
        }
        if($this->daterange!==''){
            // dd($this->daterange);
            $date= explode(' to ',$this->daterange);
            $startDate=date('Y-m-d',strtotime($date[0]));
            if(isset($date[1])){
                $endDate=date('Y-m-d',strtotime($date[1]));
                $orders->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
            }
        }
            $data=$orders->get();
        // Define your data
        $columns = ['Order#', 'User', 'Pickup','Pickup Time','Dropoff','Dropoff Time','Delivery Fee','Order Amount','Order Time','Status'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // Add headers to the CSV
        $csv->insertOne($columns);
        foreach ($data as $row) {
            $single=[$row->order_no,$row->user->name,$row->pickup_date,$row->pickup_time,$row->dropoff_date,$row->dropoff_time,
            env('CURRENCY').$row->delivery_fee,env('CURRENCY').$row->grand_total,
            $row->created_at,$row->status];
            $csv->insertOne($single);
        }
        $filename = 'orders_' . date('Y-m-d')  . '.csv';
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
  
    public function setDel($id)
    {
        $this->dId=$id;
    }

    public function delOrder()
    {
        Order::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Order Deleted Successfully!');

    }
    public function setStatus($id)
    {
        $this->statusId=$id;
    }
    public function newStatus($status)
    {
        $this->status=$status;
    }

    public function changeStatus()
    {
        
       $order=Order::with(['user'])->findOrFail($this->statusId);
       $order->update(['status'=>$this->status]);
        $response= FCMService::sendWithData(
            $order->user->deviceToken,
            [
                'title' => 'Hi, '.$order->user->name .' Your order has been '.$this->status,
                'body' => 'Your Order with ID #'.$order->order_no.' has been '.$this->status,
            
            ],
            ['order_id' => $this->statusId, 'status'=>$this->status]);
            
            Notification::create([
                'title' => 'Hi, '.$order->user->name .' Your order has been '.$this->status,
                'message' => 'Your Order with ID #'.$order->order_no.' has been '.$this->status,
                "user_id" => $order->user->id
            ]);

        $this->dispatch('success', 'Order Status Updated Successfully!');
        return redirect('partner/orders/');

    }

    public function export($data){
        // dd($data);
        if(count($data) > 0){ 
            
            $filename = 'orders_'. date('Y-m-d') . ".csv"; 
            $csv = Writer::createFromPath(storage_path('app/' . $filename), 'w+');
            $csv->insertOne(['Order#', 'User', 'Delivery Fee', 'Grand Total','Order Time' ,'Status']);

            foreach($data as $row){
                $lineData = [
                    $row->order_no,$row->user->name,
                $row->delivery_fee, $row->grand_total,
                date('H:i A d M, Y',strtotime($row->created)),$row->status
                ]; 
                $csv->insertOne($lineData);
            } 
            // Return the CSV file as a download response
            return response()->download(storage_path('app/' . $filename), $filename, [
                'Content-Type' => 'text/csv',
            ]);
            // Insert the header row
            unlink(storage_path('app/' . $filename));
        } 
        // exit; 
    }
   
}

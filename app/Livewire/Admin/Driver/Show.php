<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use League\Csv\Writer;
use Livewire\WithPagination;
use App\Http\Controllers\Controller;

class Show extends Component
{
    use WithPagination;

    public $aId='';
    public $dId='';
    public $status='';
    protected $drivers=[];
    public $search='';
    public $export=false;
    public $daterange='';
    public $is_company='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delDriver','approveAccount'];

    public function mount(){
        abort_unless(auth()->user()->can('list_driver'), 403);
    }

    public function render()
    {

        $drivers=Driver::with('vendor')->orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $drivers->where('name','LIKE', '%'.$this->search.'%')
                ->orWhere('phone','LIKE', '%'.$this->search.'%');
            }
            if($this->is_company!==''){
                $drivers->where('is_company',$this->is_company);
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $drivers->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }

        $this->drivers=$drivers->paginate(15);

        return view('livewire.admin.drivers.index',[
            'drivers' => $this->drivers
        ])->layout('components.layouts.admin-dashboard');
    }




    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';
        $this->is_company='';

    }


    public function submitActive()
    {
        $status=($this->status==1)?0:1;

        $vendor=Driver::findOrFail($this->aId);
        $vendor->update(['status'=>$status]);
        if($status==0){
            $vendor->tokens()->delete();
        }
        if($status==1){
            $title="Your CleanBasket account is Active";
            $message="Your CleanBasket account is Active and your can login and enjoy our services";
        }else{
            $title="Your CleanBasket account is Inactive";
            $message="Your CleanBasket account is Inactive. Contact to admin for further details";
        }
        $data=[
            "title" => $title,
            "message" => $message,
            "mail" => [
                "template"=>"account_status"
            ],
            "user" => $vendor
        ];
        try {
            // Controller::sendNotifications($data,'vendor');
         } catch (\Exception $ex) {
            dd($ex->getMessage());
         }

        $this->dispatch('success', 'Driver Updated Successfully!');
    }


    public function activeInactive($id,$status)
    {
        $this->aId=$id;
        $this->status=$status;
    }

    public function setDel($id)
    {
        $this->dId=$id;
    }

    public function delDriver()
    {
        Driver::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Driver Deleted Successfully!');

    }
    public function exportData()
    {
        $orders=Driver::with('vendor')->orderBy('id','desc')->whereNull('deleted_at');

        if($this->search!==''){
            $orders->where('name','LIKE', '%'.$this->search.'%')
            ->orWhere('phone','LIKE', '%'.$this->search.'%');
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
        $columns = [ 'Name', 'Phone','Partner','Created at','Status'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // Add headers to the CSV
        $csv->insertOne($columns);
        foreach ($data as $row) {
            $single=[
                $row->name,$row->phone,
                ($row->vendor!=null)?$row->vendor->business_name:'N/A',
                $row->created_at,($row->status==1)?'Active':'Inactive'
            ];
            $csv->insertOne($single);
        }
        $filename = 'drivers_' . date('Y-m-d')  . '.csv';
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
    public function gotoDetails($id) {
        return redirect('admin/partners/'.$id);
    }
}

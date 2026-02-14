<?php

namespace App\Livewire\Admin\Vendor;

use Livewire\Component;
use App\Models\Vendor;
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
    protected $vendors=[];
    public $search='';
    public $export=false;
    public $daterange='';
    public $is_company='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delVendor','approveAccount'];

    public function mount(){
        abort_unless(auth()->user()->can('list_partner'), 403);
    }

    public function render()
    {

        $vendors=Vendor::with('city','area')->orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $vendors->where('business_name','LIKE', '%'.$this->search.'%')->orWhere('email','LIKE', '%'.$this->search.'%')
                ->orWhere('phone','LIKE', '%'.$this->search.'%');
            }
            if($this->is_company!==''){
                $vendors->where('is_company',$this->is_company);
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $vendors->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }

        $this->vendors=$vendors->paginate(15);

        return view('livewire.admin.vendors.index',[
            'vendors' => $this->vendors
        ])->layout('components.layouts.admin-dashboard');
    }




    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';
        $this->is_company='';

    }

    public function gotoWorkingHours($vendorId)
    {
        return redirect()->route('admin.partners.working-hours.vendor', ['id' => $vendorId]);
    }


    public function submitActive()
    {
        $status=($this->status==1)?0:1;

        $vendor=Vendor::findOrFail($this->aId);
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
            "title_ar" => $title,
            "message" => $message,
            "message_ar" => $message,
            "mail" => [
                "template"=>"account_status"
            ],
            "user" => $vendor
        ];
        try {
            Controller::sendNotifications($data,'vendor');
         } catch (\Exception $ex) {
            dd($ex->getMessage());
         }

        $this->dispatch('success', 'Partner Updated Successfully!');
    }

    public function approveAccount()
    {
        $status=($this->status==1)?0:1;
        $vendor=Vendor::findOrFail($this->aId);
        $vendor->update(['is_approved'=>$status]);
        if($status==1){
            // $password=rand(100000,990999);
            $password=123456;
            $vendor->update(['password'=> bcrypt($password)]);
            $title="Your CleanBasket account is Approved";
            if($vendor->is_company==1){
                $message="Your CleanBasket account is Approved and your Login details are here <br> Email: ".$vendor->email.
                " <br> Password: ".$password."<br>   <a href='".url('partner/login')."'
                 style='background:#333 !important;color:white !important;padding:8px 20px !important; border-radius:10px;display:inline-block; font-size:16px'>Login Now</a>";
            }else{
                $message="Your CleanBasket account is Approved and your Login details are here <br> Email: ".$vendor->email;
            }
            $template="account_status";
        }else{
            $title="Your CleanBasket account is Unapproved";
            $message="Your CleanBasket account is Unapproved. Contact to admin for further details";
            $template="account_status";
        }
        $data=[
            "title" => $title,
            "title_ar" => $title,
            "message" => $message,
            "message_ar" => $message,
            "mail" => [
                "template"=> $template
            ],
            "user" => $vendor
        ];

        try {
            Controller::sendNotifications($data,'vendor');
         } catch (\Exception $ex) {
            dd($ex->getMessage());
         }

        $this->dispatch('success', 'Partner Updated Successfully!');
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

    public function delVendor()
    {
        Vendor::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Parnter Deleted Successfully!');

    }
    public function exportData()
    {
        $data=Vendor::with('city','area')->orderBy('id','desc')->whereNull('deleted_at');

        if($this->search!==''){
            $data->where('business_name','LIKE', '%'.$this->search.'%')->orWhere('email','LIKE', '%'.$this->search.'%')
            ->orWhere('phone','LIKE', '%'.$this->search.'%');
        }

        if($this->daterange!==''){
            // dd($this->daterange);
            $date= explode(' to ',$this->daterange);
            $startDate=date('Y-m-d',strtotime($date[0]));
            if(isset($date[1])){
                $endDate=date('Y-m-d',strtotime($date[1]));
                $data->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
            }
        }
            $data=$data->get();
        // Define your data
        $columns = [ 'Business Name', 'Email','Phone','City','Area','Created at','Status'];

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        // Add headers to the CSV
        $csv->insertOne($columns);
        foreach ($data as $row) {
            $single=[
                $row->business_name,$row->email,$row->phone,
                ($row->city!=null)?$row->city->name:'N/A',($row->area!=null)?$row->area->name:'N/A',
                $row->created_at,($row->status==1)?'Active':'Inactive'
            ];
            $csv->insertOne($single);
        }
        $filename = 'vendors_' . date('Y-m-d')  . '.csv';
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

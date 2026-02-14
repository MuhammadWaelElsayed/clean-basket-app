<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserAddress;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class BasketRequests extends Component
{
    use WithPagination;

    public $search='';
    public $status='';
    public $daterange='';

    public $listeners = ['approve', 'reject'];

    public function mount()
    {
        abort_unless(auth()->user()->can('basket_requests'), 403);
        if(isset($_GET['status'])){
            $this->status=$_GET['status'];
        }
    }

    public function render()
    {

        $requests=UserAddress::with('user','vendor','driver')->whereNull('deleted_at')->latest();

        if($this->search!==''){
            $requests->whereHas('user', function($q){
                $q->where('first_name','LIKE', '%'.$this->search.'%')
                ->orWhere('phone','LIKE', '%'.$this->search.'%');
            });
        }
        if($this->status!==''){
                $requests->where('basket_status',$this->status);
        }

        if($this->daterange!==''){
            $date= explode(' to ',$this->daterange);
            $startDate=date('Y-m-d',strtotime($date[0]));
            if(isset($date[1])){
                $endDate=date('Y-m-d',strtotime($date[1]));
                $requests->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
            }
        }
        $requests= $requests->paginate(15);
        // dd($requests);
        return view('livewire.admin.requests.index',compact('requests'))
        ->layout('components.layouts.admin-dashboard');
    }

    public function clearFilter() {
        $this->search='';
        $this->status='';
        $this->daterange='';
    }

    public function markDelivered($id) {
        $address=UserAddress::with('user')->findOrFail($id);
        $address->update([
            "basket_status"=>"Delivered"
        ]);
        $data=[
            "title" => "Your basket request is confirmed and soon our team will deliver to you",
            "title_ar" => "سلتك تتجهز 👏🏻 قريبا الفريق بيوصلها لك ",
            "message" => "Your basket request is confirmed and soon our team will deliver to you",
            "message_ar" => "سلتك تتجهز 👏🏻 قريبا الفريق بيوصلها لك ",
            // "mail" => [
            //     "template"=>"basket_delivered"
            // ],
            "user" => $address->user
        ];
        try {
            Controller::sendNotifications($data,'user');
         } catch (\Exception $ex) {
            // dd($ex->getMessage());
         }
    }



}

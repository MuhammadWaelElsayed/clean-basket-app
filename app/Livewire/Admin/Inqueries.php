<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Contact;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Inqueries extends Component
{
    public $search='';
    public $daterange='';

    public $listeners = ['approve', 'reject'];

    public function mount(){
        abort_unless(auth()->user()->can('website_inquiry'), 403);
    }
    public function render()
    {

        $contacts=Contact::latest();

        if($this->search!==''){
            $contacts->where('name','LIKE', '%'.$this->search.'%');
        }

        if($this->daterange!==''){
            $date= explode(' to ',$this->daterange);
            $startDate=date('Y-m-d',strtotime($date[0]));
            if(isset($date[1])){
                $endDate=date('Y-m-d',strtotime($date[1]));
                $contacts->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
            }
        }
        $contacts= $contacts->paginate(15);

        return view('livewire.admin.inqueries.index',compact('contacts'))
        ->layout('components.layouts.admin-dashboard');
    }

    public function clearFilter() {
        $this->search='';
        $this->daterange='';
    }

    public function setSId($id) {
        // dd($id);
        $this->sId=$id;
    }



}

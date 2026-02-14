<?php

namespace App\Livewire\Admin\Service;

use Livewire\Component;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $aId='';
    public $dId='';
    public $status='';
    protected $services=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delService'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_service'), 403);
    }
    public function render()
    {
        $services=Service::orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $services->where('name','LIKE', '%'.$this->search.'%');
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $services->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                $categories=$services->get();
                $this->export($categories);
            }
        $this->services=$services->paginate(15);

        return view('livewire.admin.services.index',[
            'services' => $this->services
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {

        $services=Service::orderBy('id','desc')->whereNull('deleted_at')->paginate(15);

    }


    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';

    }


    public function submitActive()
    {
        $status=($this->status==1)?0:1;
        Service::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'Service Updated Successfully!');
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

    public function delService()
    {
        Service::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Service Deleted Successfully!');

    }


}

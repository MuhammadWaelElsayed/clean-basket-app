<?php

namespace App\Livewire\Admin\Onboard;

use Livewire\Component;
use App\Models\Onboard;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $dId='';
    public $aId='';
    public $status='';
    protected $onboard=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delOnboard'];

    public function mount(){
        abort_unless(auth()->user()->can('list_onboard'), 403);
    }
    public function render()
    {
        $onboard=Onboard::orderBy('id','desc');
            if($this->search!==''){
                $onboard->where('link_to','LIKE', '%'.$this->search.'%');
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $onboard->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                $categories=$onboard->get();
                $this->export($categories);
            }
        $this->onboard=$onboard->paginate(15);

        return view('livewire.admin.onboard.index',[
            'onboards' => $this->onboard
        ])->layout('components.layouts.admin-dashboard');
    }


    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';

    }




    public function submitActive()
    {
        $status=($this->status==1)?0:1;
       Onboard::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'Onboard Updated Successfully!');
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

    public function delOnboard()
    {
        Onboard::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Onboard Deleted Successfully!');

    }


}

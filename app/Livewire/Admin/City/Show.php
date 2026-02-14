<?php

namespace App\Livewire\Admin\City;

use Livewire\Component;
use App\Models\City;
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
    protected $cities=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delCity'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_city'), 403);
    }
    public function render()
    {
        $cities=City::orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $cities->where('name','LIKE', '%'.$this->search.'%');
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $cities->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                $categories=$cities->get();
                $this->export($categories);
            }
        $this->cities=$cities->paginate(15);

        return view('livewire.admin.cities.index',[
            'cities' => $this->cities
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {

        $cities=City::orderBy('id','desc')->whereNull('deleted_at')->paginate(15);

    }


    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';

    }



    public function submitActive()
    {
        $status=($this->status==1)?0:1;
        City::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'City Updated Successfully!');
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

    public function delCity()
    {
        City::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'City Deleted Successfully!');

    }


}

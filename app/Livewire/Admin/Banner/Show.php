<?php

namespace App\Livewire\Admin\Banner;

use Livewire\Component;
use App\Models\Banner;
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
    protected $banners=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delBanner'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_banner'), 403);
    }
    public function render()
    {
        $banners=Banner::orderBy('id','desc');
            if($this->search!==''){
                $banners->where('link_to','LIKE', '%'.$this->search.'%');
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $banners->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                $categories=$banners->get();
                $this->export($categories);
            }
        $this->banners=$banners->paginate(15);

        return view('livewire.admin.banners.index',[
            'banners' => $this->banners
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
       Banner::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'Banner Updated Successfully!');
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

    public function delBanner()
    {
        Banner::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Banner Deleted Successfully!');

    }


}

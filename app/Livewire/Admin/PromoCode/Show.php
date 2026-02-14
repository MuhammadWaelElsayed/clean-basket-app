<?php

namespace App\Livewire\Admin\PromoCode;

use Livewire\Component;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Hash;
use App\PromoCodes\FCMPromoCode;
use Carbon\Carbon;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public $aId='';
    public $dId='';
    public $status='';
    protected $codes=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delPromoCode'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_discount'), 403);
    }
    public function render()
    {
        $codes=PromoCode::orderBy('id','desc')->whereNull('deleted_at');
            if($this->search!==''){
                $codes->where(function($query) {
                    $query->where('title','LIKE', '%'.$this->search.'%')
                          ->orWhere('title_ar','LIKE', '%'.$this->search.'%')
                          ->orWhere('code','LIKE', '%'.$this->search.'%');
                });
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $codes->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                $categories=$codes->get();
                $this->export($categories);
            }
        $this->codes=$codes->paginate(15);

        return view('livewire.admin.codes.index',[
            'codes' => $this->codes
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        // إعادة تعيين الصفحة عندما يتم تحديث البحث
        if($field === 'search' || $field === 'daterange') {
            $this->resetPage();
        }
    }


    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';
        $this->resetPage(); // إعادة تعيين الصفحة للمرور الأول
    }


    public function submitActive()
    {
        $status=($this->status==1)?0:1;
        PromoCode::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'PromoCode Updated Successfully!');
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

    public function delPromoCode()
    {
        PromoCode::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'PromoCode Deleted Successfully!');

    }


}

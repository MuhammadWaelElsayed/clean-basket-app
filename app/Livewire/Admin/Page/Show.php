<?php

namespace App\Livewire\Admin\Page;

use Livewire\Component;
use App\Models\Page;
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
    protected $pages=[];
    public $search='';
    public $export=false;
    public $daterange='';

    public $listeners = ['submit-active' => 'submitActive', 'del-item'=>'delPage'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_page'), 403);
    }
    public function render()
    {
        $pages=Page::orderBy('id','desc');
            if($this->search!==''){
                $pages->where('title','LIKE', '%'.$this->search.'%');
            }
            if($this->daterange!==''){
                // dd($this->daterange);
                $date= explode(' to ',$this->daterange);
                $startDate=date('Y-m-d',strtotime($date[0]));
                if(isset($date[1])){
                    $endDate=date('Y-m-d',strtotime($date[1]));
                    $pages->whereDate('created_at','>=',$startDate)->whereDate('created_at','<=',$endDate);
                }
            }
            if($this->export==true){
                $categories=$pages->get();
                $this->export($categories);
            }
        $this->pages=$pages->paginate(15);

        return view('livewire.admin.pages.index',[
            'pages' => $this->pages
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {

        // $pages=Page::orderBy('id','desc')->whereNull('deleted_at')->paginate(15);

    }


    public function clearFilter()
    {
        $this->search='';
        $this->daterange='';

    }



    public function submitActive()
    {
        $status=($this->status==1)?0:1;
        Page::findOrFail($this->aId)->update(['status'=>$status]);
        $this->dispatch('success', 'Page Updated Successfully!');
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

    public function delPage()
    {
        Page::findOrFail($this->dId)->delete();
        $this->dispatch('success', 'Page Deleted Successfully!');

    }


}

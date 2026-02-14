<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Reviews extends Component
{
   public $vendor_id='';
   public $status='';
   public $sId;

    public $listeners = ['approve', 'reject'];
   
    public function render()
    {

        $vendors=Vendor::select('id','name')->where(['is_approved'=>1,'status'=>1,'deleted_at'=>null])->get();

        $reviews=Review::with('user','vendor')->latest('id');
        
        if ($this->vendor_id!=='') {
            $reviews->where('vendor_id',$this->vendor_id);
        }
        if ($this->status!=='') {
            $reviews->where('is_approved',$this->status);
        }

        $reviews= $reviews->paginate(15);
        
        return view('livewire.admin.reviews.index',compact('reviews','vendors'))
        ->layout('components.layouts.admin-dashboard');
    }

    public function clearFilters() {
        $this->vendor_id='';
        $this->status='';
    }

    public function setSId($id) {
        // dd($id);
        $this->sId=$id;
    }

    public function approve() {
        Review::find($this->sId)->update([
            "is_approved"=>1
        ]);
    }

    public function reject() {
        Review::find($this->sId)->update([
            "is_approved"=>2
        ]);
    }

    

}

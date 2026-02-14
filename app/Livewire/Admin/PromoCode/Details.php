<?php

namespace App\Livewire\Admin\PromoCode;

use Livewire\Component;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use Illuminate\Support\Facades\Hash;
use App\PromoCodes\FCMPromoCode;
use Carbon\Carbon;

class Details extends Component
{

    public $code;
    public $users='';
    public $tab='info';


    public function mount($id)
    {
        abort_unless(auth()->user()->can('view_discount'), 403);
        $this->code = PromoCode::find($id);
        $this->users= UserPromoCode::with('user')->whereHas('user')->where('code_id',$id)->get();

    }

    public function render()
    {
        return view('livewire.admin.codes.show')->layout('components.layouts.admin-dashboard');
    }



    public function setTab($tab) {
        $this->tab=$tab;
    }


}

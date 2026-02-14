<?php

namespace App\Livewire\Admin\PromoCode;

use Livewire\Component;
use App\Models\PromoCode;
use App\Models\UserPromoCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class Create extends Component
{
    use WithFileUploads;

    public $eId;

    public $title='';
    public $code='';
    public $min_order='';
    public $max_order='';
    public $promo_type='Amount';
    public $user_type='All';
    public $discounted_amount;
    public $discount_percentage;
    public $expiry='DATE';
    public $count;
    public $daterange;
    public $users;

    public function mount($id=null)
    {
        abort_unless(auth()->user()->can('create_discount'), 403);
        if($id > 0){
            $this->eId=$id;
            $code=PromoCode::find($this->eId);
            $this->title=$code->title;
            $this->code=$code->code;
            $this->min_order=$code->min_order;
            $this->max_order=$code->max_order;
            $this->promo_type=$code->promo_type;
            $this->user_type=$code->user_type;
            $this->discounted_amount=$code->discounted_amount;
            $this->discount_percentage=$code->discount_percentage;
            $this->expiry=$code->expiry;
            $this->count=$code->count;
            $this->daterange=$code->from_date.' to '.$code->to_date;
        }else{
            $imageUrl=asset('storage/uploads/blank.png');
        }
    }

    public function render()
    {
        $users_arr=User::whereNull('deleted_at')->where('status',1)->select(
            DB::raw("CONCAT(first_name, ' ', last_name, ' - ', phone) as label"),
            'id as value'
        )->get()->toArray();
        // dd($users);
        return view('livewire.admin.codes.create',compact('users_arr'))->layout('components.layouts.admin-dashboard');
    }


    public function store()
    {
       $data= $this->validate([
            "title"=>"required|max:250",
            "code"=> ["required",Rule::unique('promo_codes')->ignore($this->eId)->whereNull('deleted_at')],
            "min_order"=>"required",
            "max_order"=>"required",
            "expiry"=>"required",
            "promo_type"=>"required",
            "user_type"=>"required",
        ]);
        if ($this->eId < 1 && $this->user_type=="Selected") {
            // dd($this->users);
            $this->validate([
                'users'=> 'required'
            ]);
        }

        if($this->promo_type=='Amount'){
            $this->validate([
                "discounted_amount" => "required|numeric|min:1"
            ]);
            $data['discounted_amount']=$this->discounted_amount;
        }
        else{
            $this->validate([
                "discount_percentage" => "required|numeric|min:1"
            ]);
            $data['discount_percentage']=$this->discount_percentage;
        }
        if($this->expiry=='COUNT'){

            $data['count']=$this->count;
        }
        elseif($this->expiry=='DATE'){
            $this->validate([
                "daterange" => "required"
            ]);
            $date= explode(' to ',$this->daterange);
            if (!isset($date[1])) {
                $this->dispatch('error', 'Daterange is not proper!');
            }
            $data['from_date']=date('Y-m-d',strtotime($date[0]));
            $data['to_date']=date('Y-m-d',strtotime($date[1]));
        }
        // dd($data);

        if($this->eId > 0){
            $code=PromoCode::find($this->eId)->update($data);
            $this->dispatch('success', 'PromoCode updated Successfully!');
        }else{
            $code=PromoCode::create($data);
            $this->dispatch('success', 'PromoCode Added Successfully!');
        }

        if($code && $this->eId < 1){
            if ($this->user_type=="All") {
                $users_arr=User::whereNull('deleted_at')->where('status',1)->pluck('id')->toArray();
            }else{
                $users_arr=$this->users;
            }
             foreach ($users_arr as  $user) {
                UserPromoCode::create([
                "user_id"=>$user,
                "code_id"=>$code->id
                ]);
             }
        }

        return $this->redirectRoute('admin.codes', navigate: true);


    }


}

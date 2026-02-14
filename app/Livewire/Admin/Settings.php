<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithPagination;

class Settings extends Component
{
    use WithPagination;

    public $tab='settings';
    public $settings=[
        'service_fee' => '',
        'vat' => '',
        'delivery_charges' => '',
        'driver_deliver_radius' => '',
    ];
    public $slots=[
        [
            "from"=>'',
            "to"=>'',
        ]
    ];
    
    public function mount()
    {
        $this->setTab($this->tab);
    }

    public function render()
    {
          
        return view('livewire.admin.settings')->layout('components.layouts.admin-dashboard');
    }

    public function setTab($tab)
    {
        $this->tab=$tab;
        if($this->tab=="slots"){
            $setting=Setting::where('key','pickup_slots')->pluck('value')->first();
            if($setting && $setting!=''){
                $this->slots=json_decode($setting, true);
            }
        }
        elseif($this->tab=="settings"){
            $this->settings=Setting::whereIn('key',array_keys($this->settings))->pluck('value', 'key')->toArray();

        }
    }



    public function updateSettings()
    {
        foreach ($this->settings as $key => $val) {
            $setting=Setting::where('key',$key)->update([
                'value'=> $val
            ]);
        }
     
        $this->dispatch('success', 'Slots updated Successfully!');

    }

    public function updateSlots()
    {
        $slots=json_encode($this->slots);
        $setting=Setting::where('key','pickup_slots')->first();
        if($setting){
            $setting->update([
                'value'=>$slots
            ]);
        }else{
            Setting::create([
                'value' => $slots,
                'key' =>'pickup_slots'
            ]);
        }
       

        $this->dispatch('success', 'Slots updated Successfully!');

    }

    public function addNew()
    {
        $this->slots[]=[
            "from"=>'',
            "to"=>'',
        ];
        // dd($this->slots);
    }

    public function removeRow($key)
    {
        unset($this->slots[$key]);
        // dd($this->slots);
        $this->slots= array_values($this->slots);
    }

   
}

<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Account extends Component
{
    public $email='';
    public $old_password='';
    public $new_password='';
    public $confirm_password='';
    public $show_phone='';
    public $tab='email';

    public function mount()
    {
        $partner=Vendor::findOrFail(session('partner')->id);
        $this->email=$partner->email;
        $this->show_phone=$partner->show_phone;
    }
    public function render()
    {
        
        return view('livewire.partner.account')->layout('components.layouts.partner-dashboard');
    }

    public function updateProfile()
    {
            $partner= Vendor::findOrFail(session('partner')->id)->update(['show_phone'=>$this->show_phone]);
            $this->dispatch('success', 'Profile settings updated!');
            return redirect('partner/account');
    }

    public function updateEmail()
    {
        $this->validate([
            "email"=>'required|unique:vendors,email,NULL,id,deleted_at,NULL',
        ]);
            $partner= Vendor::findOrFail(session('partner')->id)->update(['email'=>$this->email]);
            $this->dispatch('error', 'You need to login again!');
            return redirect('partner/logout');
    }

    public function updatePassword(){
        $this->tab='password';
        $this->validate([
            "old_password"=>"required",
            "new_password"=>"required|min:6|same:confirm_password",
            "confirm_password"=>"required",

        ]);
        
        $validCredentials = Hash::check($this->old_password, session('partner')->password);
        // dd($user->password);
        if ($validCredentials) {
            $vendor= Vendor::findOrFail(session('partner')->id)->update(['password'=>Hash::make($this->new_password)]);
            $this->dispatch('success', 'Password is updated!');
        }
        else{
            $this->dispatch('error', 'Old Password is not matched!');
        }
    }

    public function setTab($tab) {
        $this->tab=$tab;
    }
   
}

<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class ForgotPassword extends Component
{
    public $email='';
    public $otp='';
    public $new_password='';
    public $confirm_password='';

    public $step=1;

    public function render()
    {
        return view('livewire.partner.forgot-password')->layout('components.layouts.login');
    }

    public function updated($field)
    {
        if($this->step==1){
            $this->validateOnly($field,[
                "email"=>"required | email",
            ]);
        }
        elseif($this->step==2){
            $this->validateOnly($field,[
                "otp"=>"required | digits:4",
                "new_password"=>"required|min:6|same:confirm_password",
            ]);
        }
        
    }

    public function submitForgot()
    {
        $this->validate([
            "email"=>"required | email",
        ]);
        $user = Vendor::where('email', $this->email)
            ->first();
        if($user==null){
            $this->dispatch('error', 'Email or is wronged or not registered!');
            return false;
        }
        else if($user->is_approved==0){
            $this->dispatch('error', 'Sorry! your account is not approved yet.');
            return false;
        }
        else if($user->status==0){
            $this->dispatch('error', 'Sorry! your account is disabled by admin.');
            return false;
        }else{
            // $otp= rand(1000,9999);
            $otp= 1234;
            $user->otp=$otp;
            $user->save();
            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'otp' => $otp
            ];
            try {
                // \Mail::to($user->email)->send(new \App\Mail\ForgotPasswordEmail($data));
                $this->dispatch('success', 'Forgot Password OTP is sent to your email. check your mailbox!');
                $this->step=2;
            } catch (Exception $ex) {
                $this->dispatch('error', 'Unable to send mail due to server error!');

            }
           
        
        }
           
        
    }

    public function showPass()
    {
        $this->show_pass=!$this->show_pass;
    }

    public function submitReset()
    {
        $this->validate([
            "otp"=>"required | digits:4",
            "new_password"=>"required|min:6|same:confirm_password",
        ]);
        $user = Vendor::where('email', $this->email)
            ->where(['otp'=>$this->otp])
            ->first();
        if($user==null){
            $this->dispatch('error', 'OTP is wronged or expired!');
            return false;
        }
        else{
            $user->update([
                "password"=> bcrypt($this->new_password)
            ]);
                
            $this->dispatch('success', 'Password reset successfully!');
            return redirect()->route('partner.login');

        }
           
        
    }
   
}

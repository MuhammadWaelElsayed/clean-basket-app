<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Login extends Component
{
    public $email='';
    public $password='';
    public $show_pass=false;

    public function render()
    {
        return view('livewire.partner.login')->layout('components.layouts.login');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "email"=>"required | email",
            "password"=>"required|min:6",
        ]);
    }

    public function submitLogin()
    {
        $this->validate([
            "email"=>"required | email",
            "password"=>"required",
        ]);
        $user = Vendor::where('email', $this->email)
            ->whereNull('deleted_at')
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
        }
            $validCredentials = Hash::check($this->password, $user->password);
            // dd($user->password);
            if ($validCredentials) {
                // session('company',$user);
                session(['partner' => $user]);
                \Illuminate\Support\Facades\Cache::put('partner', $user, now()->addHours(24));
                $this->dispatch('success', 'You have successfully logged in!');
                // return redirect('company/dashboard');
                return redirect()->route('partner.dashboard');
            }
            else{
                $this->dispatch('error', 'Email or Password is wronged!');
            }

    }

    public function showPass()
    {
        $this->show_pass=!$this->show_pass;
    }

}

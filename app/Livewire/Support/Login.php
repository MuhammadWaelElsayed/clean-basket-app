<?php

namespace App\Livewire\Support;

use Livewire\Component;
use App\Models\Admin;
use App\Models\AdminToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class Login extends Component
{
    public $email='';
    public $password='';
    public $web_token='';
    public $show_pass=false;

    public function render()
    {
        return view('livewire.support.login')->layout('components.layouts.login');
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
        $user = Admin::where('email', $this->email)
            ->where('accStatus', 1)
            ->first();

        if($user==null){
            $this->dispatch('error', 'Email is wrong or not registered!');
            return false;
        }
        $validCredentials = Hash::check($this->password, $user->password);
        if ($validCredentials) {
            $user->web_token=$this->web_token;
            Cache::put('admin', $user, now()->addHours(24));
            AdminToken::firstOrCreate([
                "token" => $this->web_token
            ]);
            $this->dispatch('success', 'You have successfully logged in!');
            return redirect()->to('support/tickets');
        }
        else{
            $this->dispatch('error', 'Email or Password is wrong!');
        }
    }

    public function showPass()
    {
        $this->show_pass=!$this->show_pass;
    }
}

<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin;
use App\Models\AdminToken;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $web_token = '';
    public $show_pass = false;

    public function render()
    {
        return view('livewire.admin.login')->layout('components.layouts.login');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            "email" => "required | email",
            "password" => "required|min:6",
        ]);
    }

    public function submitLogin()
    {
        session()->flush();

        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt([
            'email' => $this->email,
            'password' => $this->password,
        ], true)) {
            $user = Auth::guard('admin')->user();

            session()->put('admin', $user);

            if (!$user->accStatus) {
                \auth('admin')->logout();
                $this->dispatch('error', 'Account is not active!');
                return null;
            }

            $user->update(['webToken' => $this->web_token]);

            session()->regenerate();

            $this->dispatch('success', 'You have successfully logged in!');

            return redirect()->to('admin/dashboard');
        } else {
            $this->dispatch('error', 'Email or Password is incorrect!');
        }
    }

    public function showPass()
    {
        $this->show_pass = !$this->show_pass;
    }

}

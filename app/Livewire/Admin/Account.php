<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;

class Account extends Component
{
    public $email='';
    public $old_password='';
    public $new_password='';
    public $confirm_password='';
    public $tab='email';

    public function mount()
    {
        $admin= auth('admin')->user();

        $this->email=$admin->email;
    }
    public function render()
    {

        return view('livewire.admin.account')->layout('components.layouts.admin-dashboard');
    }

    // public function updated($field)
    // {
    //     $this->validateOnly($field,[
    //         "email"=>"required | email",
    //         "password"=>"required|min:6",
    //     ]);
    // }

    public function updateEmail()
    {
        $this->validate([
            "email"=>"required|email|unique:admin_users",
        ]);
            $admin= Admin::where('id',session('admin')->id)->update(['email'=>$this->email]);
            $this->dispatch('error', 'You need to login!');
            return redirect('admin/logout');
    }

    public function updatePassword(){
        $this->tab='password';
        $this->validate([
            "old_password"=>"required",
            "new_password"=>"required|min:6|same:confirm_password",
            "confirm_password"=>"required",
        ]);

        $validCredentials = Hash::check($this->old_password, session('admin')->password);
        // dd($user->password);
        if ($validCredentials) {
            $vendor= Admin::where('id',session('admin')->id)->update(['password'=>Hash::make($this->new_password)]);
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

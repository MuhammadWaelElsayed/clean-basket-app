<?php

namespace App\Livewire\Landing;

use Livewire\Component;
use App\Models\Vendor;
use App\Models\Contact;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class Home extends Component
{
    public $email='';
    public $name='';
    public $message='';
    public $phone='';
    public $phoneCode=null;

    public function render()
    {
        // AdminNotification::create([
        //     "title" => "test notification",
        //     "message" => "test message here anything else",
        //     "link"=> "/admin/cases"
        // ]);

        return view('livewire.landing.under-develop',)->layout('components.layouts.login');
    }

    public function updated($field)
    {
        // $this->validateOnly($field,[
        //     "name"=>"required",
        //     "email"=>"required | email",
        //     "message"=>"required",
        // ]);
    }

    public function submitContact()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ];
        $customMessages=[];
        if(app()->getLocale()=="ar"){
            $customMessages = [
                'name.required' => __('errors')['name']['required'],
                'email.required' => __('errors')['email']['required'],
                'email.email' => __('errors')['email']['email'],
                'message.required' => __('errors')['message']['required'],
            ];
        }
        
        $validator = Validator::make($this->all(), $rules, $customMessages);
        if ($validator->fails()) {
            $firstError = $validator->errors()->first();
            $this->dispatch('error', $firstError);
        }else{
            $user = Contact::create([
                "name"=> $this->name,
                "email"=> $this->email,
                "phone"=> str_replace("+", "", $this->phoneCode.$this->phone),
                "message"=> $this->message,
            ]);
            \Mail::to(env('ADMIN_EMAIL'))->send(new \App\Mail\ContactEmail($user));
            $this->clearInputs();
            $this->dispatch('success', __('contact_thanks'));
            return true;
        }
      
         
        
    }

    public function clearInputs()
    {
        $this->email='';
        $this->name='';
        $this->message='';
        $this->phone='';
    }
   
}

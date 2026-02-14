<?php

namespace App\Livewire\Landing;

use Livewire\Component;

class PrivacyPolicy extends Component
{
    public $page='Privacy Policy';

    public function render()
    {
        
        return view('livewire.landing.privacy-policy')->layout('components.layouts.landing');
    }

}

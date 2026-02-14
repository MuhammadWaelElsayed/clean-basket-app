<?php

namespace App\Livewire\Landing;

use Livewire\Component;

class TermsConditions extends Component
{
    public $page='Terms and Conditions';
 

    public function render()
    {
        return view('livewire.landing.privacy-policy')->layout('components.layouts.landing');
    }

}

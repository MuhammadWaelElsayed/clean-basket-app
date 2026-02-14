<?php

namespace App\Livewire\Landing;
use App\Models\Page;
use Livewire\Component;

class CommonPage extends Component
{
    public $slug='privacy-policy';
    public $lang='en';

    public function mount($page)
    {
        $this->slug=$page;
        if(isset($_GET['language']) && $_GET['language']=="ar"){
            $this->lang=="ar";
        }
    }

    public function render()
    {
        $title=($this->lang=="ar")?"title_ar as title":"title";
        $content=($this->lang=="ar")?"content_ar as content":"content";

        $page=Page::select('id',$title,$content)->where('slug',$this->slug)->firstOrFail();
       

        return view('livewire.landing.common_page',compact('page'))->layout('components.layouts.landing');
    }

}

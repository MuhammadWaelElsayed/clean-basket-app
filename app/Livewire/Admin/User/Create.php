<?php

namespace App\Livewire\Admin\User;

use Livewire\Component;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;

class Create extends Component
{
    use WithFileUploads;

    public $name='';
    public $price='';
    public $description='';
    public $image;
    public $service_id='';

    public function render()
    {

        return view('livewire.admin.cities.create')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "name"=>"required ",
        ]);
    }

    public function store()
    {
        $this->validate([
            "name"=>"required ",
        ]);
      
        $data=[
            "name" => $this->name,
        ];
      
        User::create($data);
        
        $this->dispatch('success', 'User Added Successfully!');
        return redirect('admin/cities');
        
    }

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

}

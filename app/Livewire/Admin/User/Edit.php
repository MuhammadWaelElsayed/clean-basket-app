<?php

namespace App\Livewire\Admin\User;

use Livewire\Component;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $name='';
    public $cityId;

    public function mount($id)
    {
        $this->cityId = $id;
        $city=User::find($this->cityId);
        $this->name = $city->name;

    }

    public function render()
    {
        $city=User::find($this->cityId)->first();

        return view('livewire.admin.cities.edit')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "name"=>"required ",
        ]);
       
    }

    public function updateData()
    {
        $this->validate([
            "name"=>"required ",
        ]);
      
        $data=[
            "name" => $this->name,
        ];
       
        User::findOrFail($this->cityId)->update($data);
        
        $this->dispatch('success', 'User Updated Successfully!');
        return redirect('admin/cities');
        
    }

    


}

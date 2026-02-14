<?php

namespace App\Livewire\Admin\City;

use Livewire\Component;
use App\Models\Service;
use App\Models\City;
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
    public $name_ar='';
    public $eId;

    public function mount($id=null)
    {
        abort_unless(auth()->user()->can('create_city'), 403);
        if($id > 0){
            $this->eId=$id;
            $service=City::findOrFail($this->eId);
            $this->name=$service->name;
            $this->name_ar=$service->name_ar;
        }
    }

    public function render()
    {

        return view('livewire.admin.cities.create')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "name"=>"required |max:240",
            "name_ar"=>"required|max:240 ",
        ]);
    }

    public function store()
    {
        $this->validate([
            "name"=>"required |max:240 ",
            "name_ar"=>"required |max:240 ",

        ]);

        $data=[
            "name" => $this->name,
            "name_ar" => $this->name_ar,
        ];

        if($this->eId > 0){
            $service=City::find($this->eId)->update($data);
            $this->dispatch('success', 'City updated Successfully!');
        }else{
            $service=City::create($data);
            $this->dispatch('success', 'City Added Successfully!');
        }
        return $this->redirectRoute('admin.cities', navigate: true);

    }

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

}

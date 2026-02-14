<?php

namespace App\Livewire\Admin\Service;

use Livewire\Component;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $name='';
    public $price='';
    public $description='';
    public $serviceImage;
    public $image;
    public $imageUrl;
    public $service_id='';


    public $serviceId;

    public function mount($id)
    {
        $this->serviceId = $id;
        $service=Service::find($this->serviceId);
        $this->name = $service->name;
        $this->serviceImage = $service->image;
        $this->imageUrl = $service->image;

    }

    public function render()
    {
        $service=Service::find($this->serviceId)->first();

        return view('livewire.admin.services.edit')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "name"=>"required ",
            "image"=>"required|image|mimes:jpeg,png,jpg,gif|max:5248",
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
        $image = $this->image;
        if($this->image){
            $imageName = date('ymdhis')."_service." . $this->image->getClientOriginalExtension();
            // $this->image->move(public_path('uploads'), $imageName);
            $this->image->storeAs('public/uploads', $imageName);
            $data['image']=$imageName;  
        }
        
        Service::findOrFail($this->serviceId)->update($data);
        
        $this->dispatch('success', 'Service Updated Successfully!');
        return $this->redirectRoute('admin.services', navigate: true);

        
    }

    


}

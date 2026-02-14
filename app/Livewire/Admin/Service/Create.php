<?php

namespace App\Livewire\Admin\Service;

use Livewire\Component;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;

class Create extends Component
{
    use WithFileUploads;

    public $eId;

    public $name='';
    public $name_ar='';
    public $price='';
    public $description='';
    public $image;
    public $service_id='';
    public $imageUrl;

    public function mount($id=null)
    {
        abort_unless(auth()->user()->can('create_service'), 403);
        if($id > 0){
            $this->eId=$id;
            $service=Service::find($this->eId);
            $this->name=$service->name;
            $this->name_ar=$service->name_ar;
            $this->imageUrl=$service->image;
        }else{
            $imageUrl=asset('storage/uploads/blank.png');
        }
    }

    public function render()
    {

        return view('livewire.admin.services.create')->layout('components.layouts.admin-dashboard');
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
            "name"=>"required |max:240",
            "name_ar"=>"required|max:240 ",
        ]);
        if($this->image){
            $this->validate([
                "image"=>"required|image|mimes:jpeg,png,jpg,gif|max:2048",
            ]);
        }

        $data=[
            "name" => $this->name,
            "name_ar" => $this->name_ar,
        ];

        if($this->image){
            $imageName = date('ymdhis')."_item." . $this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('public/uploads', $imageName);
            $data['image']=$imageName;
        }
        if($this->eId > 0){
            $service=Service::find($this->eId)->update($data);
            $this->dispatch('success', 'Service updated Successfully!');
        }else{
            $service=Service::create($data);
            $this->dispatch('success', 'Service Added Successfully!');
        }

        return $this->redirectRoute('admin.services', navigate: true);


    }

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

}

<?php

namespace App\Livewire\Admin\Onboard;

use Livewire\Component;
use App\Models\Service;
use App\Models\Onboard;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;

class Create extends Component
{
    use WithFileUploads;

    public $type='banner';
    public $media;
    public $mediaUrl;
    public $eId;

    public function mount($id = null)
    {
        if($id){
            $this->eId=$id;
            $onboard=Onboard::findOrFail($id);
            $this->mediaUrl=$onboard->media;
            $this->type=$onboard->type;
        }else{

            $this->mediaUrl= ($this->type=="banner")? asset('uploads/blank2.jpg')  :'Upload Video';
        }
        return view('livewire.admin.onboard.create')->layout('components.layouts.admin-dashboard');
    }

    public function render()
    {

        return view('livewire.admin.onboard.create')->layout('components.layouts.admin-dashboard');
    }

    public function store()
    {
        if ($this->type=="banner") {
            $rules=[
                "media"=>"required|image|mimes:jpeg,png,jpg,gif|max:2048",
            ];
        }else{
            $rules=[
                "media"=>"required|mimes:mp4,avi,mov,webm|max:10048", //10mb
            ];
        }
        $this->validate($rules);
      
        $data=[
            "type"=> $this->type
        ];
        
        if($this->media){
            $imageName = date('ymdhis')."_item." . $this->media->getClientOriginalExtension();
            $path = $this->media->storeAs('public/uploads', $imageName);
            $data['media']=$imageName;  
        }
        if($this->eId){
            Onboard::findOrFail($this->eId)->update($data);
            $this->dispatch('success', 'Onboard updated Successfully!');
        }else{
            Onboard::create($data);
            $this->dispatch('success', 'Onboard Added Successfully!');
        }
        
        return $this->redirectRoute('admin.onboard', navigate: true);
        
    }

  

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->media = $file;
    }

 

}

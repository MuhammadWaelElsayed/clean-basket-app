<?php

namespace App\Livewire\Admin\Banner;

use Livewire\Component;
use App\Models\Service;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile as SupportFileUploadsTemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;

class Create extends Component
{
    use WithFileUploads;

    public $name='';
    public $link_to='url';
    public $link_id='';
    public $image;
    public $options=[];

    public function mount()
    {
        abort_unless(auth()->user()->can('create_banner'), 403);
    }
    public function render()
    {

        return view('livewire.admin.banners.create')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
             "image"=>"required|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);
    }

    public function store()
    {
        $this->validate([
             "image"=>"required|image|mimes:jpeg,png,jpg,gif|max:2048",
        ]);

        $image = $this->image;
        $data=[];

        if($this->image){
            $imageName = date('ymdhis')."_item." . $this->image->getClientOriginalExtension();
            $this->image->storeAs('', $imageName, 'uploads');
            $data['image']=$imageName;
        }

        Banner::create($data);

        $this->dispatch('success', 'Banner Added Successfully!');
        return $this->redirectRoute('admin.banners', navigate: true);

    }

    public function updatedFile(SupportFileUploadsTemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

    public function getOptions()
    {
        if($this->link_to=="category"){
            $this->options=Category::select(['id','name'])->where('status',1)->whereNull('deleted_at')->get();
        }
        else if($this->link_to=="company"){
            $this->options=Vendor::select(['id','name'])->where(['status'=>1,'is_approved'=>1,'is_company'=>1])->whereNull('deleted_at')->get();
        }
        else if($this->link_to=="vendor"){
            $this->options=Vendor::select(['id','name'])->where(['status'=>1,'is_approved'=>1,'is_company'=>0])->whereNull('deleted_at')->get();
        }
    }

}

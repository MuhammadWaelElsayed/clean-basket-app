<?php

namespace App\Livewire\Admin\Banner;

use Livewire\Component;
use App\Models\Service;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $link_to='';
    public $link_id='';
    public $bannerId;
    public $image;
    public $imageUrl;
    public $options=[];

    public function mount($id)
    {
        abort_unless(auth()->user()->can('update_banner'), 403);
        $this->bannerId = $id;
        $item=Banner::find($this->bannerId);
        $this->itemImage = $item->image;
        $this->imageUrl = $item->image;
        $this->link_to = $item->link_to;
        $this->getOptions();
        $this->link_id = $item->link_id;
    }

    public function render()
    {
        $banner=Banner::find($this->bannerId)->first();

        return view('livewire.admin.banners.edit')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        // $this->validateOnly($field,[
        //     "image"=>"image|mimes:jpeg,png,jpg,gif|max:5248",
        // ]);
    }

    public function updateData()
    {

        $data=[];
        $image = $this->image;
        if($this->image){
            $imageName = date('ymdhis')."_item." . $this->image->getClientOriginalExtension();
            $this->image->storeAs('', $imageName, 'uploads');
            $data['image']=$imageName;
        }
        Banner::findOrFail($this->bannerId)->update($data);

        $this->dispatch('success', 'Banner Updated Successfully!');
        return $this->redirectRoute('admin.banners', navigate: true);

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
            $this->options=Vendor::select(['id','name'])->where('status',1)->whereNull('deleted_at')->get();
        }
    }


}

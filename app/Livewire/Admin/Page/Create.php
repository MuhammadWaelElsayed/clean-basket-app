<?php

namespace App\Livewire\Admin\Page;

use Livewire\Component;
use App\Models\Service;
use App\Models\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;
use Livewire\TemporaryUploadedFile;

class Create extends Component
{
    use WithFileUploads;

    public $title='';
    public $title_ar='';
    public $content='';
    public $content_ar='';
    public $eId;

    public function mount($id=null)
    {
        abort_unless(auth()->user()->can('create_page'), 403);

        if($id > 0){
            $this->eId=$id;
            $page=Page::findOrFail($this->eId);
            $this->title=$page->title;
            $this->title_ar=$page->title_ar;
            $this->content=$page->content;
            $this->content_ar=$page->content_ar;
        }
    }

    public function render()
    {

        return view('livewire.admin.pages.create')->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field,[
            "title"=>"required ",
            "title_ar"=>"required ",
        ]);
    }

    public function store()
    {
        $this->validate([
            "title"=>"required ",
            "title_ar"=>"required ",
        ]);

        $data=[
            "title" => $this->title,
            "title_ar" => $this->title_ar,
            "content" => $this->content,
            "content_ar" => $this->content_ar,
        ];

        if($this->eId > 0){
            $page=Page::find($this->eId)->update($data);
            $this->dispatch('success', 'Page updated Successfully!');
        }else{
            $page=Page::create($data);
            $this->dispatch('success', 'Page Added Successfully!');
        }
        return $this->redirectRoute('admin.pages', navigate: true);

    }

    public function updatedFile(TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }

}

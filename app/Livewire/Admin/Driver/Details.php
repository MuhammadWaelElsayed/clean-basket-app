<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use App\Models\Service;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithFileUploads;

class Details extends Component
{

    public $name='';
    public $itemId;
    public $item;

    public function mount($id)
    {
        abort_unless(auth()->user()->can('view_driver'), 403);

        $this->itemId = $id;
        $this->item=Driver::with(['city','area'])->find($this->itemId);

    }

    public function render()
    {

        return view('livewire.admin.drivers.show')->layout('components.layouts.admin-dashboard');
    }




}

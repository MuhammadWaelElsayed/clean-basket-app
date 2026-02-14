<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use App\Models\Driver;
use App\Models\Vendor;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $eId = null;

    // Form Fields
    public $name = '';
    public $role = null;
    public $phone = null;
    public $partners = []; // Changed from $partner to $partners (array)
    public $image = null;
    public $imageUrl = null;
    public $password = null;
    public $rules = null;

    public function mount($id = null)
    {
        abort_unless(auth()->user()->can('create_driver'), 403);

        $this->imageUrl = asset('uploads/blank.png');
        $this->rules = [
            "phone" => 'regex:/^966\d{7,10}$/|required|unique:drivers,phone,' . $id . ',id,deleted_at,NULL',
            "name" => 'required',
            "partners" => 'required|array|min:1', // Changed validation
            "partners.*" => 'exists:vendors,id',
            'role' => 'required',
        ];

        // Edit Form
        if ($id > 0) {
            $this->eId = $id;
            $driver = Driver::findOrFail($this->eId);
            $this->name = $driver->name;
            $this->phone = $driver->phone;
            $this->partners = $driver->vendors()->pluck('vendors.id')->toArray(); // Get related vendor IDs
            $this->role = $driver->role;
            $this->imageUrl = $driver->picture;
        } else {
            $this->rules['password'] = "required|min:6";
        }
    }

    public function render()
    {
        $vendors = Vendor::select('id as value', 'business_name as label')
            ->where(['status' => 1, 'is_approved' => 1])
            ->whereNull('deleted_at')
            ->get()
            ->toArray();
        $vendors = json_encode($vendors);

        return view('livewire.admin.drivers.create', compact('vendors'))
            ->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field, $this->rules);
    }

    public function store()
    {
        $this->validate($this->rules);

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'role' => $this->role,
        ];

        if ($this->image) {
            $imageName = date('ymdhis') . "_driver." . $this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('public/uploads', $imageName);
            $data['picture'] = $imageName;
        }

        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }

        if ($this->eId > 0) {
            $driver = Driver::findOrFail($this->eId);
            $driver->update($data);

            // Sync the partners (vendors)
            $driver->vendors()->sync($this->partners);

            $this->dispatch('success', 'Driver updated successfully!');
        } else {
            $driver = Driver::create($data);

            // Attach the partners (vendors)
            $driver->vendors()->attach($this->partners);

            $this->dispatch('success', 'Driver registered successfully!');
        }

        return $this->redirectRoute('admin.drivers', navigate: true);
    }

    public function updatedFile(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file)
    {
        $this->image = $file;
    }
}

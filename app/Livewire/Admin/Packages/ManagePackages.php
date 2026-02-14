<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use Livewire\Component;

class ManagePackages extends Component
{
    public $packages;
    public $editingPackage = null;
    public $total_vat = 0.15;
    // حقول التعديل
    public $name;
    public $name_en;
    public $vat;
    public $price;
    public $total_price;
    public $cashback_amount;
    public $delivery_fee;
    public $duration_days;
    public $has_priority = false;

    protected $rules = [
        'name'             => 'required|string|max:255',
        'name_en'          => 'nullable|string|max:255',
        'vat'              => 'required|numeric|min:0',
        'price'            => 'required|numeric|min:0',
        'cashback_amount'  => 'required|numeric|min:0',
        'delivery_fee'     => 'required|numeric|min:0',
        'duration_days'    => 'nullable|integer|min:0',
        'has_priority'     => 'boolean',
    ];

    public function updatedPrice()
    {
        $this->calculateTotalPrice();
    }

    public function updatedVat()
    {
        $this->calculateTotalPrice();
    }

    public function calculateTotalPrice()
    {
        if (is_numeric($this->price) && is_numeric($this->vat)) {
            $this->total_price = round($this->price + ($this->price * $this->vat), 2);
            $this->vat = $this->total_vat * $this->price;
        }
    }

    public function mount()
    {
        abort_unless(auth()->user()->can('manage_packages'), 403);
        $this->loadPackages();
    }

    public function loadPackages()
    {
        $this->packages = Package::orderBy('id')->get();
    }

    public function render()
    {
        return view('livewire.admin.packages.manage-packages')
            ->layout('components.layouts.admin-dashboard');
    }
}

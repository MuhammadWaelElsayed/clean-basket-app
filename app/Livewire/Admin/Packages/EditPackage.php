<?php

namespace App\Livewire\Admin\Packages;

use App\Models\Package;
use Livewire\Component;

class EditPackage extends Component
{
    public $package;
    public $name, $name_en, $price, $vat, $cashback_amount, $delivery_fee, $duration_days, $has_priority;
    public $total_vat = 0.15;
    public function mount(Package $package)
    {
        abort_unless(auth()->user()->can('manage_packages'), 403);
        $this->package = $package;
        $this->name = $package->name;
        $this->name_en = $package->name_en;
        $this->price = $package->price;
        $this->vat = $this->total_vat * $this->price;
        $this->cashback_amount = $package->cashback_amount;
        $this->delivery_fee = $package->delivery_fee;
        $this->duration_days = $package->duration_days;
        $this->has_priority = $package->has_priority;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'vat' => 'required|numeric|min:0',
            'cashback_amount' => 'required|numeric|min:0',
            'delivery_fee' => 'required|numeric|min:0',
             'has_priority' => 'boolean',
        ]);

        $rules['duration_days'] = 'nullable';
        // if ($this->name_en === 'Basic') {
        //     $rules['duration_days'] = 'required|integer|min:1';
        // } else {
        //     $rules['duration_days'] = 'nullable';
        // }

        $this->validate($rules);



        $this->package->update([
            'name' => $this->name,
            'name_en' => $this->name_en,
            'price' => $this->price,
            'vat' => $this->total_vat * $this->price,
            'total_price' => $this->price + ($this->total_vat * $this->price),
            'cashback_amount' => $this->cashback_amount,
            'delivery_fee' => $this->delivery_fee,
            // 'duration_days' => $this->name_en === 'Basic' ? $this->duration_days : null,
            'duration_days' =>   null,
            'has_priority' => $this->has_priority,
        ]);

        session()->flash('success', 'Package updated successfully.');
        return redirect()->route('admin.packages');
    }

    public function render()
    {
        return view('livewire.admin.packages.edit-package')
            ->layout('components.layouts.admin-dashboard');
    }
}

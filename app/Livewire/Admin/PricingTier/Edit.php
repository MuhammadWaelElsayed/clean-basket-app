<?php

namespace App\Livewire\Admin\PricingTier;

use Livewire\Component;
use App\Models\B2bPricingTier;

class Edit extends Component
{
    public $tierId;
    public $tier;

    public $name = '';
    public $name_ar = '';
    public $description = '';
    public $description_ar = '';
    public $discount_percentage = 0;
    public $priority = 0, $min = 0, $max = 0;
    public $is_active = true;
    public $type = 'dynamic';
    public function mount($id)
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);

        $this->tierId = $id;
        $this->tier = B2bPricingTier::findOrFail($id);

        $this->name = $this->tier->name;
        $this->name_ar = $this->tier->name_ar;
        $this->description = $this->tier->description;
        $this->description_ar = $this->tier->description_ar;
        $this->discount_percentage = $this->tier->discount_percentage;
        $this->priority = $this->tier->priority;
        $this->min = $this->tier->min;
        $this->max = $this->tier->max;
        $this->is_active = $this->tier->is_active;
        $this->type = $this->tier->type;
    }

    public function render()
    {
        return view('livewire.admin.pricing-tier.edit')
            ->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'priority' => 'required|integer|min:0',
            'type' => 'required|in:dynamic,fixed',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:0|gt:min',
            'is_active' => 'boolean',
        ]);
    }

    public function update()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'priority' => 'required|integer|min:0',
            'type' => 'required|in:dynamic,fixed',
            'min' => 'required|integer|min:0',
            'max' => 'required|integer|min:0|gt:min',
            'is_active' => 'boolean',
        ]);

        $this->tier->update($validated);

        $this->dispatch('success', 'Pricing tier updated successfully!');
        return $this->redirectRoute('pricing-tiers.index', navigate: true);
    }
}

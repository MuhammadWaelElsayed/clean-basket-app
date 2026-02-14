<?php

namespace App\Livewire\Admin\PricingTier;

use Livewire\Component;
use App\Models\B2bPricingTier;

class Create extends Component
{
    public $name = '';
    public $name_ar = '';
    public $description = '';
    public $description_ar = '';
    public $discount_percentage = 0;
    public $priority = 0, $min = 0, $max = 0;
    public $is_active = true;
    public $type = 'dynamic';
    public function mount()
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);
    }

    public function render()
    {
        return view('livewire.admin.pricing-tier.create')
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

    public function store()
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

        B2bPricingTier::create($validated);

        $this->dispatch('success', 'Pricing tier created successfully!');
        return $this->redirectRoute('pricing-tiers.index', navigate: true);
    }
}

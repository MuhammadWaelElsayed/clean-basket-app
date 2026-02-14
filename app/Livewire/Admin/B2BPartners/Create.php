<?php

namespace App\Livewire\Admin\B2BPartners;

use App\Models\B2BPartner;
use App\Models\B2BPartnerSecret;
use Livewire\Component;
use Illuminate\Support\Str;

class Create extends Component
{
    public $name;
    public $service_fees;
    public $delivery_fees;
    public $active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'service_fees' => 'required|numeric|min:0',
        'delivery_fees' => 'required|numeric|min:0',
        'active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Partner name is required',
        'service_fees.required' => 'Service fees are required',
        'service_fees.numeric' => 'Service fees must be a number',
        'service_fees.min' => 'Service fees cannot be negative',
        'delivery_fees.required' => 'Delivery fees are required',
        'delivery_fees.numeric' => 'Delivery fees must be a number',
        'delivery_fees.min' => 'Delivery fees cannot be negative',
    ];

    public function mount()
    {
        // abort_unless(auth()->user()->can('manage_b2b_partners'), 403);
    }

    public function render()
    {
        return view('livewire.admin.b2b-partners.create')
            ->layout('components.layouts.admin-dashboard');
    }

    public function save()
    {
        $this->validate();

        try {
            // Create partner
            $partner = B2BPartner::create([
                'name' => $this->name,
                'service_fees' => $this->service_fees,
                'delivery_fees' => $this->delivery_fees,
                'active' => $this->active,
            ]);

            // Generate initial secret
            B2BPartnerSecret::create([
                'b2b_partner_id' => $partner->id,
                'secret' => Str::random(64),
                'active' => true,
            ]);

            session()->flash('success', 'Partner created successfully with initial API secret!');

            return redirect()->route('b2b.partners.edit', $partner->id);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create partner. Please try again.'. $e->getMessage());
            \Log::error('B2B Partner creation failed: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('b2b.partners');
    }
}

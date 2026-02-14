<?php

namespace App\Livewire\Admin\B2bClient;

use Livewire\Component;
use App\Models\B2bClient;
use App\Models\B2bPricingTier;
use App\Models\Vendor;
use App\Models\Driver;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $clientId;
    public $client;

    public $company_name = '';
    public $contact_person = '';
    public $email = '';
    public $phone = '';
    public $tax_number = '';
    public $address = '';
    public $service_fees = null;
    public $delivery_fees = null;
    public $pricing_tier_id = '';
    public $vendor_id = '';
    public $driver_id = '';
    public $is_active = 1;

    public function mount($id)
    {
        abort_unless(auth()->user()->can('manage_b2b_clients'), 403);

        $this->clientId = $id;
        $this->client = B2bClient::findOrFail($id);

        $this->company_name = $this->client->company_name;
        $this->contact_person = $this->client->contact_person;
        $this->email = $this->client->email;
        $this->phone = $this->client->phone;
        $this->tax_number = $this->client->tax_number;
        $this->address = $this->client->address;
        $this->service_fees = $this->client->service_fees;
        $this->delivery_fees = $this->client->delivery_fees;
        $this->pricing_tier_id = $this->client->pricing_tier_id;
        $this->vendor_id = $this->client->vendor_id;
        $this->driver_id = $this->client->driver_id;
        $this->is_active = $this->client->is_active;
    }

    public function render()
    {
        $pricingTiers = B2bPricingTier::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        $vendors = Vendor::where(['status' => 1, 'is_approved' => 1, 'deleted_at' => null])
            ->select('business_name', 'id')
            ->get();

        $drivers = Driver::where('status', 1)
            ->select('name', 'id')
            ->get();

        return view('livewire.admin.b2b-client.edit', [
            'pricingTiers' => $pricingTiers,
            'vendors' => $vendors,
            'drivers' => $drivers,
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('b2b_clients', 'email')->ignore($this->clientId)],
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'service_fees' => 'required|numeric',
            'delivery_fees' => 'required|numeric',
            'pricing_tier_id' => 'nullable|exists:b2b_pricing_tiers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'is_active' => 'required',
        ]);
    }

    public function update()
    {
        $validated = $this->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('b2b_clients', 'email')->ignore($this->clientId)],
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'service_fees' => 'required|numeric',
            'delivery_fees' => 'required|numeric',
            'pricing_tier_id' => 'nullable|exists:b2b_pricing_tiers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'is_active' => 'required',
        ]);

        $this->client->update($validated);

        $this->dispatch('success', 'B2B Client updated successfully!');
        return $this->redirectRoute('b2b-clients.index', navigate: true);
    }
}

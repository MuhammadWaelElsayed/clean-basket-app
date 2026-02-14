<?php
namespace App\Livewire\Admin\B2bClient;

use Livewire\Component;
use App\Models\B2bClient;
use App\Models\B2bPricingTier;
use App\Models\Vendor;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;

class Create extends Component
{
    public $company_name = '';
    public $contact_person = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $phone = '';
    public $tax_number = '';
    public $address = '';
    public $service_fees = null;
    public $delivery_fees = null;

    public $pricing_tier_id = '';
    public $vendor_id = '';
    public $driver_id = '';
    public $is_active = 1;

    public function mount()
    {
        abort_unless(auth()->user()->can('manage_b2b_clients'), 403);
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

        return view('livewire.admin.b2b-client.create', [
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
            'email' => 'required|email|unique:b2b_clients,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'service_fees' => 'required|numeric',
            'delivery_fees' => 'required|numeric',
            'pricing_tier_id' => 'nullable|exists:b2b_pricing_tiers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'driver_id' => 'nullable|exists:drivers,id',
        ]);
    }

    public function store()
    {
        $validated = $this->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|unique:b2b_clients,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'service_fees' => 'required|numeric',
            'delivery_fees' => 'required|numeric',
            'pricing_tier_id' => 'required|exists:b2b_pricing_tiers,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'is_active' => 'required',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        B2bClient::create($validated);

        $this->dispatch('success', 'B2B Client created successfully!');
        return $this->redirectRoute('b2b-clients.index', navigate: true);
    }
}

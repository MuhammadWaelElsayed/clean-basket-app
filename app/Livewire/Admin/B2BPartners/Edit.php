<?php

namespace App\Livewire\Admin\B2BPartners;

use App\Models\B2BPartner;
use App\Models\B2BPartnerSecret;
use Livewire\Component;
use Illuminate\Support\Str;

class Edit extends Component
{
    public B2BPartner $partner;
    public $name;
    public $service_fees;
    public $delivery_fees;
    public $active;

    // Secret management
    public $showSecretModal = false;
    public $newSecret;
    public $secrets;

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

    public function mount($id)
    {
        // abort_unless(auth()->user()->can('manage_b2b_partners'), 403);

        $partner = B2BPartner::findOrFail($id);
        $this->partner = $partner;
        $this->name = $partner->name;
        $this->service_fees = $partner->service_fees;
        $this->delivery_fees = $partner->delivery_fees;
        $this->active = $partner->active;

        $this->loadSecrets();
    }

    public function render()
    {
        return view('livewire.admin.b2b-partners.edit')
            ->layout('components.layouts.admin-dashboard');
    }

    public function loadSecrets()
    {
        $this->secrets = B2BPartnerSecret::where('b2b_partner_id', $this->partner->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function update()
    {
        $this->validate();

        try {
            $this->partner->update([
                'name' => $this->name,
                'service_fees' => $this->service_fees,
                'delivery_fees' => $this->delivery_fees,
                'active' => $this->active,
            ]);

            session()->flash('success', 'Partner updated successfully!');

            $this->dispatch('partner-updated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update partner. Please try again.');
            \Log::error('B2B Partner update failed: ' . $e->getMessage());
        }
    }

    public function openSecretModal()
    {
        $this->showSecretModal = true;
        $this->newSecret = null;
    }

    public function closeSecretModal()
    {
        $this->showSecretModal = false;
        $this->newSecret = null;
    }

    public function generateSecret()
    {
        try {
            // Deactivate all existing secrets for this partner
            B2BPartnerSecret::where('b2b_partner_id', $this->partner->id)
                ->update(['active' => false]);

            // Create new active secret
            $secret = B2BPartnerSecret::create([
                'b2b_partner_id' => $this->partner->id,
                'secret' => Str::random(64),
                'active' => true,
            ]);

            $this->newSecret = $secret->secret;
            $this->loadSecrets();

            $this->dispatch('secret-generated');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate secret. Please try again.');
            \Log::error('Secret generation failed: ' . $e->getMessage());
        }
    }

    public function deactivateSecret($secretId)
    {
        try {
            $secret = B2BPartnerSecret::findOrFail($secretId);

            if ($secret->b2b_partner_id !== $this->partner->id) {
                session()->flash('error', 'Unauthorized action.');
                return;
            }

            $secret->update(['active' => false]);
            $this->loadSecrets();

            session()->flash('success', 'Secret deactivated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to deactivate secret.');
            \Log::error('Secret deactivation failed: ' . $e->getMessage());
        }
    }

    public function activateSecret($secretId)
    {
        try {
            $secret = B2BPartnerSecret::findOrFail($secretId);

            if ($secret->b2b_partner_id !== $this->partner->id) {
                session()->flash('error', 'Unauthorized action.');
                return;
            }

            // Deactivate all other secrets
            B2BPartnerSecret::where('b2b_partner_id', $this->partner->id)
                ->where('id', '!=', $secretId)
                ->update(['active' => false]);

            // Activate this secret
            $secret->update(['active' => true]);
            $this->loadSecrets();

            session()->flash('success', 'Secret activated successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to activate secret.');
            \Log::error('Secret activation failed: ' . $e->getMessage());
        }
    }

    public function deleteSecret($secretId)
    {
        try {
            $secret = B2BPartnerSecret::findOrFail($secretId);

            if ($secret->b2b_partner_id !== $this->partner->id) {
                session()->flash('error', 'Unauthorized action.');
                return;
            }

            // Prevent deletion if it's the only secret
            if ($this->secrets->count() <= 1) {
                session()->flash('error', 'Cannot delete the last secret. Generate a new one first.');
                return;
            }

            // Prevent deletion if it's the active secret
            if ($secret->active) {
                session()->flash('error', 'Cannot delete the active secret. Activate another secret first.');
                return;
            }

            $secret->delete();
            $this->loadSecrets();

            session()->flash('success', 'Secret deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete secret.');
            \Log::error('Secret deletion failed: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('b2b.partners');
    }

    public function deletePartner()
    {
        try {
            $this->partner->delete();

            session()->flash('success', 'Partner deleted successfully!');
            return redirect()->route('b2b.partners');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete partner.');
            \Log::error('Partner deletion failed: ' . $e->getMessage());
        }
    }
}

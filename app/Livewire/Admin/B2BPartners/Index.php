<?php

namespace App\Livewire\Admin\B2BPartners;

use App\Models\B2BPartner;
use App\Models\B2BPartnerSecret;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $active;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Modal properties
    public $showModal = false;
    public $editMode = false;
    public $partnerId;
    public $name;
    public $service_fees;
    public $delivery_fees;
    public $partner_active = true;

    // Secret management
    public $showSecretModal = false;
    public $selectedPartnerId;
    public $newSecret;

    protected $queryString = [
        'search' => ['except' => ''],
        'active' => ['except' => ''],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'service_fees' => 'required|numeric|min:0',
        'delivery_fees' => 'required|numeric|min:0',
        'partner_active' => 'boolean',
    ];

    public function mount()
    {
        // abort_unless(auth()->user()->can('manage_b2b_partners'), 403);
    }

    public function render()
    {
        $partners = B2BPartner::query()
            ->withCount(['secrets', 'secrets as active_secrets_count' => function ($query) {
                $query->where('active', true);
            }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when(filled($this->active), function ($query) {
                $query->where('active', $this->active);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.b2b-partners.index', [
            'partners' => $partners,
        ])->layout('components.layouts.admin-dashboard');
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingActive()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($partnerId)
    {
        $partner = B2BPartner::findOrFail($partnerId);

        $this->partnerId = $partner->id;
        $this->name = $partner->name;
        $this->service_fees = $partner->service_fees;
        $this->delivery_fees = $partner->delivery_fees;
        $this->partner_active = $partner->active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            $partner = B2BPartner::findOrFail($this->partnerId);
            $partner->update([
                'name' => $this->name,
                'service_fees' => $this->service_fees,
                'delivery_fees' => $this->delivery_fees,
                'active' => $this->partner_active,
            ]);

            $message = 'Partner updated successfully';
        } else {
            $partner = B2BPartner::create([
                'name' => $this->name,
                'service_fees' => $this->service_fees,
                'delivery_fees' => $this->delivery_fees,
                'active' => $this->partner_active,
            ]);

            // Generate first secret for new partner
            B2BPartnerSecret::create([
                'b2b_partner_id' => $partner->id,
                'secret' => Str::random(64),
                'active' => true,
            ]);

            $message = 'Partner created successfully with initial secret';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('partner-saved', ['message' => $message]);
    }

    public function toggleActive($partnerId)
    {
        $partner = B2BPartner::findOrFail($partnerId);
        $partner->update(['active' => !$partner->active]);

        $this->dispatch('partner-status-toggled', [
            'message' => $partner->active ? 'Partner activated' : 'Partner deactivated'
        ]);
    }

    public function deletePartner($partnerId)
    {
        $partner = B2BPartner::findOrFail($partnerId);
        $partner->delete();

        $this->dispatch('partner-deleted');
    }

    public function openSecretModal($partnerId)
    {
        $this->selectedPartnerId = $partnerId;
        $this->showSecretModal = true;
    }

    public function generateSecret()
    {
        $this->validate([
            'selectedPartnerId' => 'required|exists:b2b_partners,id'
        ]);

        // Deactivate all existing secrets for this partner
        B2BPartnerSecret::where('b2b_partner_id', $this->selectedPartnerId)
            ->update(['active' => false]);

        // Create new active secret
        $secret = B2BPartnerSecret::create([
            'b2b_partner_id' => $this->selectedPartnerId,
            'secret' => Str::random(64),
            'active' => true,
        ]);

        $this->newSecret = $secret->secret;

        $this->dispatch('secret-generated');
    }

    public function closeSecretModal()
    {
        $this->showSecretModal = false;
        $this->selectedPartnerId = null;
        $this->newSecret = null;
    }

    public function resetForm()
    {
        $this->partnerId = null;
        $this->name = '';
        $this->service_fees = '';
        $this->delivery_fees = '';
        $this->partner_active = true;
        $this->resetValidation();
    }
}

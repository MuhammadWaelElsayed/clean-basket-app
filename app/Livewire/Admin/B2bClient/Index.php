<?php
// app/Livewire/Admin/B2bClient/Index.php

namespace App\Livewire\Admin\B2bClient;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\B2bClient;
use App\Models\B2bPricingTier;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $is_active = '';
    public $pricing_tier_id = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public $deleteId;
    public $clientToDelete;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'pricing_tier_id' => ['except' => ''],
    ];

    public function mount()
    {
        abort_unless(auth()->user()->can('manage_b2b_clients'), 403);
    }

    public function render()
    {
        $clients = B2bClient::query()
            ->with(['pricingTier', 'vendor', 'driver'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('company_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->is_active !== '', function ($query) {
                $query->where('is_active', $this->is_active);
            })
            ->when($this->pricing_tier_id, function ($query) {
                $query->where('pricing_tier_id', $this->pricing_tier_id);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $pricingTiers = B2bPricingTier::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        return view('livewire.admin.b2b-client.index', [
            'clients' => $clients,
            'pricingTiers' => $pricingTiers,
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

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPricingTierId()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->clientToDelete = B2bClient::find($id);
        $this->dispatch('show-delete-modal');
    }

    public function delete()
    {
        $client = B2bClient::find($this->deleteId);

        if ($client) {
            // Revoke all tokens
            $client->tokens()->delete();

            // Delete client
            $client->delete();

            $this->dispatch('success', 'B2B Client deleted successfully!');
            $this->dispatch('hide-delete-modal');
            $this->resetPage();
        }
    }

    public function toggleStatus($id)
    {
        $client = B2bClient::find($id);

        if ($client) {
            $client->update([
                'is_active' => !$client->is_active,
            ]);

            $this->dispatch('success', 'Status updated successfully!');
        }
    }
}

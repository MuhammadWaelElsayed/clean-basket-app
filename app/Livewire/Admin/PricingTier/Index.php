<?php
// app/Livewire/Admin/PricingTier/Index.php

namespace App\Livewire\Admin\PricingTier;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\B2bPricingTier;
use App\Models\Item;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $type = '';
    public $perPage = 10;
    public $sortField = 'priority';
    public $sortDirection = 'desc';

    public $deleteId;
    public $tierToDelete;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function mount()
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);
    }

    public function render()
    {
        // Get total active items count
        $totalItems = Item::where('status', true)->count();

        $tiers = B2bPricingTier::query()
            ->withCount([
                'clients',
                'itemPrices' // Count of items with custom prices
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('name_ar', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('is_active', $this->status);
            })
            ->when($this->type !== '', function ($query) {
                $query->where('type', $this->type);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.pricing-tier.index', [
            'tiers' => $tiers,
            'totalItems' => $totalItems,
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

    public function confirmDelete($id)
    {
        $tier = B2bPricingTier::withCount('clients')->find($id);

        if ($tier && $tier->clients_count > 0) {
            $this->dispatch('error', 'Cannot delete tier with active clients!');
            return;
        }

        $this->deleteId = $id;
        $this->tierToDelete = $tier;
        $this->dispatch('show-delete-modal');
    }

    public function delete()
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);

        $tier = B2bPricingTier::find($this->deleteId);

        if ($tier) {
            $tier->delete();

            $this->dispatch('success', 'Pricing tier deleted successfully!');
            $this->dispatch('hide-delete-modal');
            $this->resetPage();
        }
    }

    public function toggleStatus($id)
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);

        $tier = B2bPricingTier::find($id);

        if ($tier) {
            $tier->update([
                'is_active' => !$tier->is_active
            ]);

            $this->dispatch('success', 'Status updated successfully!');
        }
    }
}

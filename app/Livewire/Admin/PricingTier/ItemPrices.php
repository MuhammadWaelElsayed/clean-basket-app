<?php

namespace App\Livewire\Admin\PricingTier;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\B2bPricingTier;
use App\Models\Item;
use App\Models\B2bItemPrice;
use App\Models\Service;

class ItemPrices extends Component
{
    use WithPagination;

    public $tierId;
    public $tier;
    public $search = '';
    public $service_id = '';
    public $perPage = 20;

    // Filter by pricing status
    public $pricing_status = ''; // all, priced, default

    // Modal properties
    public $showModal = false;
    public $selectedItemId = null;
    public $selectedItem = null;
    public $custom_price = null;
    public $discount_percentage = null;
    public $effective_from = null;
    public $effective_until = null;
    public $is_active = true;
    public $editingPriceId = null;

    public function mount($id)
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);

        $this->tierId = $id;
        $this->tier = B2bPricingTier::findOrFail($id);
    }

    public function render()
    {
        // Get total items count
        $totalItems = Item::where('status', true)->count();

        // Get priced items count for this tier
        $pricedItemsCount = B2bItemPrice::where('pricing_tier_id', $this->tierId)->count();

        // Calculate statistics
        $unpricedItemsCount = $totalItems - $pricedItemsCount;
        $pricedPercentage = $totalItems > 0 ? round(($pricedItemsCount / $totalItems) * 100, 1) : 0;

        $items = Item::query()
            ->with(['service', 'tierPrices' => function ($query) {
                $query->where('pricing_tier_id', $this->tierId);
            }])
            ->where('status', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('name_ar', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->service_id, function ($query) {
                $query->where('service_id', $this->service_id);
            })
            ->when($this->pricing_status === 'priced', function ($query) {
                // Only items with custom prices
                $query->whereHas('tierPrices', function ($q) {
                    $q->where('pricing_tier_id', $this->tierId);
                });
            })
            ->when($this->pricing_status === 'default', function ($query) {
                // Only items without custom prices
                $query->whereDoesntHave('tierPrices', function ($q) {
                    $q->where('pricing_tier_id', $this->tierId);
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $services = Service::select('id', 'name', 'name_ar')->get();

        return view('livewire.admin.pricing-tier.item-prices', [
            'items' => $items,
            'services' => $services,
            'totalItems' => $totalItems,
            'pricedItemsCount' => $pricedItemsCount,
            'unpricedItemsCount' => $unpricedItemsCount,
            'pricedPercentage' => $pricedPercentage,
        ])->layout('components.layouts.admin-dashboard');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingServiceId()
    {
        $this->resetPage();
    }

    public function updatingPricingStatus()
    {
        $this->resetPage();
    }

    public function openModal($itemId = null, $priceId = null)
    {
        $this->resetModal();

        if ($priceId) {
            // Editing existing price
            $price = B2bItemPrice::findOrFail($priceId);
            $this->editingPriceId = $priceId;
            $this->selectedItemId = $price->item_id;
            $this->selectedItem = $price->item;
            $this->custom_price = $price->custom_price;
            $this->discount_percentage = $price->discount_percentage;
            $this->effective_from = $price->effective_from?->format('Y-m-d');
            $this->effective_until = $price->effective_until?->format('Y-m-d');
            $this->is_active = $price->is_active;
        } else {
            // Adding new price
            $this->selectedItemId = $itemId;
            $this->selectedItem = Item::findOrFail($itemId);
            $this->discount_percentage = $this->tier->discount_percentage;
        }

        $this->showModal = true;
    }

    public function resetModal()
    {
        $this->editingPriceId = null;
        $this->selectedItemId = null;
        $this->selectedItem = null;
        $this->custom_price = null;
        $this->discount_percentage = null;
        $this->effective_from = null;
        $this->effective_until = null;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetModal();
    }

    public function savePrice()
    {
        $validated = $this->validate([
            'selectedItemId' => 'required|exists:items,id',
            'custom_price' => 'nullable|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean',
        ]);

        // Ensure at least one pricing method is provided
        if (!$this->custom_price || !is_numeric($this->custom_price) || $this->custom_price <= 1) {
            $this->addError('custom_price', 'Custom price must be provided.');
            return;
        }

        $data = [
            'item_id' => $this->selectedItemId,
            'pricing_tier_id' => $this->tierId,
            'custom_price' => $this->custom_price,
            'discount_percentage' => blank($this->discount_percentage) ? 0 : $this->discount_percentage,
            'effective_from' => $this->effective_from,
            'effective_until' => $this->effective_until,
            'is_active' => $this->is_active,
        ];

        if ($this->editingPriceId) {
            // Update existing price
            $price = B2bItemPrice::findOrFail($this->editingPriceId);
            $price->update($data);
            $message = 'Item price updated successfully!';
        } else {
            // Create new price
            B2bItemPrice::updateOrCreate(
                [
                    'item_id' => $this->selectedItemId,
                    'pricing_tier_id' => $this->tierId,
                ],
                $data
            );
            $message = 'Item price added successfully!';
        }

        $this->dispatch('success', $message);
        $this->closeModal();
    }

    public function deletePrice($priceId)
    {
        abort_unless(auth()->user()->can('manage_b2b_pricing_tiers'), 403);

        $price = B2bItemPrice::findOrFail($priceId);
        $price->delete();

        $this->dispatch('success', 'Item price deleted successfully!');
    }

    public function applyGlobalDiscount()
    {
        $items = Item::where('status', true)->get();

        $count = 0;
        foreach ($items as $item) {
            B2bItemPrice::updateOrCreate(
                [
                    'item_id' => $item->id,
                    'pricing_tier_id' => $this->tierId,
                ],
                [
                    'discount_percentage' => $this->tier->discount_percentage,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $this->dispatch('success', "Global discount applied to {$count} items!");
    }

    public function clearAllPrices()
    {
        $count = B2bItemPrice::where('pricing_tier_id', $this->tierId)->count();
        B2bItemPrice::where('pricing_tier_id', $this->tierId)->delete();

        $this->dispatch('success', "Removed {$count} custom prices!");
    }
}

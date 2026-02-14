<?php

namespace App\Livewire\Admin\Items;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Item;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class BulkAssignServices extends Component
{
    use WithPagination;

    public $selectedItems = [];
    public $selectedServices = [];
    public $servicePrices = [];
    public $serviceDiscountPrices = [];
    public $categoryFilter = '';
    public $searchTerm = '';
    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        abort_unless(auth()->user()->can('bulk_assign_services'), 403);

        $query = Item::with(['category', 'services']);

        // فلترة حسب التصنيف
        if (!empty($this->categoryFilter)) {
            $query->where('service_id', $this->categoryFilter);
        }

        // بحث في الأسماء
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $items = $query->paginate($this->perPage);
        $serviceTypes = ServiceType::all();
        $categories = \App\Models\Service::whereNull('deleted_at')->get();

        return view('livewire.admin.items.bulk-assign-services', compact('items', 'serviceTypes', 'categories'))
            ->layout('components.layouts.admin-dashboard');
    }

    public function updatedSelectedServices()
    {
        // إعادة تعيين الأسعار عند تغيير الخدمات المختارة
        $this->servicePrices = [];
        $this->serviceDiscountPrices = [];

        foreach ($this->selectedServices as $serviceId) {
            if (!isset($this->servicePrices[$serviceId])) {
                $this->servicePrices[$serviceId] = '';
            }
            if (!isset($this->serviceDiscountPrices[$serviceId])) {
                $this->serviceDiscountPrices[$serviceId] = '';
            }
        }
    }

    public function selectAllItems()
    {
        $query = Item::query();

        if (!empty($this->categoryFilter)) {
            $query->where('service_id', $this->categoryFilter);
        }

        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Get all items matching the current filters (not just current page)
        $allItemIds = $query->pluck('id')->toArray();

        // Merge with existing selected items to avoid duplicates
        $this->selectedItems = array_unique(array_merge($this->selectedItems, $allItemIds));
    }

    public function deselectAllItems()
    {
        $this->selectedItems = [];
    }

    public function bulkAssignServices()
    {
        $this->validate([
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*' => 'exists:items,id',
            'selectedServices' => 'required|array|min:1',
            'selectedServices.*' => 'exists:service_types,id',
            'servicePrices.*' => 'required|numeric|min:0',
            'serviceDiscountPrices.*' => 'nullable|numeric|min:0',
        ]);

        // التحقق من وجود أسعار للخدمات المختارة
        $hasValidPrices = false;
        foreach ($this->selectedServices as $serviceId) {
            if (!empty($this->servicePrices[$serviceId])) {
                $hasValidPrices = true;
                break;
            }
        }

        if (!$hasValidPrices) {
            $this->dispatch('error', 'At least one service must have a price');
            return;
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $items = Item::whereIn('id', $this->selectedItems)->get();

            foreach ($items as $item) {
                // إضافة الخدمات الجديدة (بدون مسح القديمة)
                foreach ($this->selectedServices as $serviceId) {
                    if (!empty($this->servicePrices[$serviceId])) {
                        // التحقق من عدم وجود الخدمة مسبقاً
                        if (!$item->services()->where('service_type_id', $serviceId)->exists()) {
                            $pivotData = [
                                'price' => $this->servicePrices[$serviceId],
                                'discount_price' => !empty($this->serviceDiscountPrices[$serviceId]) ? $this->serviceDiscountPrices[$serviceId] : null,
                            ];

                            $item->services()->attach($serviceId, $pivotData);
                            $updatedCount++;
                        }
                    }
                }
            }

            DB::commit();

            $this->dispatch('success', "Services assigned successfully to {$updatedCount} items!");
            $this->selectedItems = []; // إعادة تعيين الأصناف المختارة

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Error assigning services: ' . $e->getMessage());
        }
    }

    public function bulkReplaceServices()
    {
        $this->validate([
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*' => 'exists:items,id',
            'selectedServices' => 'required|array|min:1',
            'selectedServices.*' => 'exists:service_types,id',
            'servicePrices.*' => 'required|numeric|min:0',
            'serviceDiscountPrices.*' => 'nullable|numeric|min:0',
        ]);

        // التحقق من وجود أسعار للخدمات المختارة
        $hasValidPrices = false;
        foreach ($this->selectedServices as $serviceId) {
            if (!empty($this->servicePrices[$serviceId])) {
                $hasValidPrices = true;
                break;
            }
        }

        if (!$hasValidPrices) {
            $this->dispatch('error', 'At least one service must have a price');
            return;
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $items = Item::whereIn('id', $this->selectedItems)->get();

            foreach ($items as $item) {
                // مسح جميع الخدمات القديمة
                $item->services()->detach();

                // إضافة الخدمات الجديدة
                foreach ($this->selectedServices as $serviceId) {
                    if (!empty($this->servicePrices[$serviceId])) {
                        $pivotData = [
                            'price' => $this->servicePrices[$serviceId],
                            'discount_price' => !empty($this->serviceDiscountPrices[$serviceId]) ? $this->serviceDiscountPrices[$serviceId] : null,
                        ];

                        $item->services()->attach($serviceId, $pivotData);
                    }
                }
                $updatedCount++;
            }

            DB::commit();

            $this->dispatch('success', "Services replaced successfully for {$updatedCount} items!");
            $this->selectedItems = []; // إعادة تعيين الأصناف المختارة

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Error replacing services: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->selectedItems = [];
        $this->selectedServices = [];
        $this->servicePrices = [];
        $this->serviceDiscountPrices = [];
        $this->categoryFilter = '';
        $this->searchTerm = '';
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
        $this->selectedItems = [];
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->selectedItems = [];
    }

    public function updatedPerPage()
    {
        $this->resetPage();
        $this->selectedItems = [];
    }
}

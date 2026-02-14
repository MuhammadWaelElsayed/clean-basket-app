<?php

namespace App\Livewire\Admin\Items;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Item;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;

class ManageServices extends Component
{
    use WithPagination;

    public $itemId;
    public $item;
    public $selectedServices = [];
    public $servicePrices = [];
    public $serviceDiscountPrices = [];
    public $showForm = false;
    public $perPage = 10;
    public $searchTerm = '';

    protected $paginationTheme = 'bootstrap';

    public function mount($itemId = null)
    {
        abort_unless(auth()->user()->can('update_service'), 403);

        if ($itemId) {
            $this->itemId = $itemId;
            $this->loadItemServices();
        }
    }

    public function loadItemServices()
    {
        $this->item = Item::with('services')->find($this->itemId);

        if ($this->item) {
            $this->selectedServices = $this->item->services->pluck('id')->toArray();

            foreach ($this->item->services as $service) {
                $this->servicePrices[$service->id] = $service->pivot->price;
                $this->serviceDiscountPrices[$service->id] = $service->pivot->discount_price;
            }
        }
    }

    public function render()
    {
        $query = Item::with(['category', 'services']);

        // بحث في الأسماء
        if (!empty($this->searchTerm)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('name_ar', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $items = $query->paginate($this->perPage);
        $serviceTypes = ServiceType::all();

        return view('livewire.admin.items.manage-services', compact('items', 'serviceTypes'))
            ->layout('components.layouts.admin-dashboard');
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function selectItem($itemId)
    {
        $this->itemId = $itemId;
        $this->loadItemServices();
        $this->showForm = true;
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

    public function assignServices()
    {
        $this->validate([
            'itemId' => 'required|exists:items,id',
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

            $item = Item::find($this->itemId);

            // مسح الربط القديم
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

            DB::commit();

            $this->dispatch('success', 'Services assigned successfully!');
            $this->loadItemServices(); // إعادة تحميل البيانات

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('error', 'Error assigning services: ' . $e->getMessage());
        }
    }

    public function removeService($serviceId)
    {
        try {
            $this->item->services()->detach($serviceId);
            $this->loadItemServices();
            $this->dispatch('success', 'Service removed successfully!');
        } catch (\Exception $e) {
            $this->dispatch('error', 'Error removing service: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->selectedServices = [];
        $this->servicePrices = [];
        $this->serviceDiscountPrices = [];
        $this->showForm = false;
        $this->itemId = null;
        $this->item = null;
    }
}

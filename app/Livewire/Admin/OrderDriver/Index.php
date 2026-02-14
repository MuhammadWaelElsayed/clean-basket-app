<?php

namespace App\Livewire\Admin\OrderDriver;

use App\Models\OrderDriver;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $providerFilter = '';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'providerFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingProviderFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = OrderDriver::with(['driver', 'order', 'vendor'])
            ->orderBy('created_at', 'desc');

        // البحث
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('external_ride_id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('order', function ($orderQuery) {
                      $orderQuery->where('order_code', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('driver', function ($driverQuery) {
                      $driverQuery->where('name', 'like', '%' . $this->search . '%')
                                  ->orWhere('phone', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // فلتر الحالة
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // فلتر المزود
        if ($this->providerFilter) {
            $query->where('provider', $this->providerFilter);
        }

        $orderDrivers = $query->paginate($this->perPage);

        // الحصول على الحالات المتاحة للفلتر
        $statuses = OrderDriver::distinct()->pluck('status')->filter()->sort()->values();
        
        // الحصول على المزودين المتاحين للفلتر
        $providers = OrderDriver::distinct()->pluck('provider')->filter()->sort()->values();

        return view('livewire.admin.order-driver.index', [
            'orderDrivers' => $orderDrivers,
            'statuses' => $statuses,
            'providers' => $providers,
        ])->layout('components.layouts.admin-dashboard');
    }
}

<?php

namespace App\Livewire\Admin\Trips;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Services\DistanceCalculatorService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $status, $type, $driver_id, $client_id, $order_id, $is_picked_up;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'type' => ['except' => ''],
        'driver_id' => ['except' => ''],
        'order_id' => ['except' => ''],
        'client_id' => ['except' => ''],
        'is_picked_up' => ['except' => ''],
    ];

    public function mount()
    {
        // abort_unless(auth()->user()->can('manage_trips'), 403);
    }

    public function render()
    {
        $trips = Trip::query()
            ->with(['client', 'order.vendor', 'driver'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('order', function ($orderQuery) {
                        $orderQuery->where('order_code', 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('client', function ($clientQuery) {
                            $clientQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('driver', function ($driverQuery) {
                            $driverQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('provider', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })
            ->when($this->driver_id, function ($query) {
                $query->where('driver_id', $this->driver_id);
            })
            ->when($this->order_id, function ($query) {
                $query->where('order_id', $this->order_id);
            })
            ->when($this->client_id, function ($query) {
                $query->where('client_id', $this->client_id);
            })
            ->when(filled($this->is_picked_up), function ($query) {
                $query->where('is_picked_up', $this->is_picked_up);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $clients = User::whereNull('deleted_at')
            ->where('status', 1)
            ->get();

        $drivers = Driver::whereNull('deleted_at')
            ->where('status', 1)
            ->get();

        return view('livewire.admin.trips.index', [
            'trips' => $trips,
            'drivers' => $drivers,
            'clients' => $clients,
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

    public function updatingType()
    {
        $this->resetPage();
    }

    public function updatingDriverId()
    {
        $this->resetPage();
    }

    public function updatingClientId()
    {
        $this->resetPage();
    }

    public function updatingIsPickedUp()
    {
        $this->resetPage();
    }

    public function togglePickedUp($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $trip->update(['is_picked_up' => !$trip->is_picked_up]);

        $this->dispatch('picked-up-toggled', [
            'message' => $trip->is_picked_up ? 'Marked as picked up' : 'Marked as not picked up'
        ]);
    }

    public function scheduleTrip($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $trip->update(['status' => 'scheduled']);

        $this->dispatch('trip-scheduled');
    }

    public function assignDriver($tripId)
    {
        // This would open a modal to select a driver
        $this->dispatch('open-assign-driver-modal', ['tripId' => $tripId]);
    }

    public function startTrip($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $trip->update([
            'status' => 'in-progress',
            'started_at' => now()
        ]);

        $this->dispatch('trip-started');
    }

    public function markAsCompleted($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $trip->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        $this->dispatch('trip-completed');
    }

    public function cancelTrip($tripId)
    {
        $trip = Trip::findOrFail($tripId);
        $trip->update([
            'status' => 'cancelled',
            'completed_at' => now()
        ]);

        // Free up driver if assigned
        if ($trip->driver_id) {
            $trip->driver->update(['is_free' => 1]);
        }

        $this->dispatch('trip-cancelled');
    }
}

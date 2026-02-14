<?php

namespace App\Livewire\Admin\Driver;

use Livewire\Component;
use App\Models\Order;
use App\Models\DriverRequest;
use App\Services\DriverRequestService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrderDriverMonitor extends Component
{
    public $orderId;
    public $order;
    public $requestType = 'ALL'; // ALL, PICKUP, DELIVERY
    public $autoRefresh = true;
    public $refreshInterval = 5; // seconds

    // Statistics
    public $stats = [];
    public $driverRequests = [];
    public $activeJobs = [];
    public $upcomingJobs = [];
    public $radiusHistory = [];

    // Cutoff date for statistics
    const STATS_CUTOFF_DATE = '2026-01-17';

    protected $listeners = ['refreshData' => '$refresh'];

    public function mount($orderId = null)
    {
        $this->orderId = $orderId;
        $this->loadData();
    }

    public function loadData()
    {
        if ($this->orderId) {
            $this->order = Order::with(['user', 'address', 'vendor', 'pickupDriver', 'deliveryDriver'])
                ->find($this->orderId);
        }

        $this->loadStatistics();
        $this->loadDriverRequests();
        $this->loadActiveJobs();
        $this->loadUpcomingJobs();
        $this->loadRadiusHistory();
    }

    public function loadStatistics()
    {
        if (!$this->orderId) {
            // Define the cutoff date
            $cutoffDate = Carbon::parse(self::STATS_CUTOFF_DATE)->startOfDay();

            // Global statistics - FROM CUTOFF DATE ONWARDS
            $ordersQuery = Order::where('created_at', '>=', $cutoffDate);

            $this->stats = [
                // Total active orders created from cutoff date onwards
                'total_orders' => (clone $ordersQuery)
                    ->whereIn('status', ['PLACED', 'PICKED_UP', 'IN_TRANSIT', 'CONFIRMED'])
                    ->count(),

                // Pending pickup (created from cutoff date onwards, no pickup driver assigned)
                'pending_pickup' => (clone $ordersQuery)
                    ->whereNull('pickup_driver_id')
                    ->count(),

                // Pending delivery (created from cutoff date onwards, has pickup driver but no delivery driver)
                'pending_delivery' => (clone $ordersQuery)
                    ->whereNull('delivery_driver_id')
                    ->whereNotNull('pickup_driver_id')
                    ->count(),

                // Total driver requests sent today
                'total_requests_today' => DriverRequest::whereDate('created_at', today())->count(),

                // Acceptance rate (today only)
                'acceptance_rate' => $this->calculateAcceptanceRate(),

                // Optional: Show cutoff date in stats
                'cutoff_date' => $cutoffDate->format('M d, Y'),
            ];
        } else {
            // Order-specific statistics
            $pickupStats = $this->getRequestStats('PICKUP');
            $deliveryStats = $this->getRequestStats('DELIVERY');

            $this->stats = [
                'pickup' => $pickupStats,
                'delivery' => $deliveryStats,
                'order_status' => $this->order->status ?? 'N/A',
                'pickup_driver' => $this->order->pickupDriver->name ?? 'Not Assigned',
                'delivery_driver' => $this->order->deliveryDriver->name ?? 'Not Assigned',
            ];
        }
    }

    protected function getRequestStats($requestType)
    {
        $requests = DriverRequest::where('order_id', $this->orderId)
            ->where('request_type', $requestType)
            ->get();

        return [
            'total' => $requests->count(),
            'pending' => $requests->where('status', 'PENDING')->count(),
            'accepted' => $requests->where('status', 'ACCEPTED')->count(),
            'rejected' => $requests->where('status', 'REJECTED')->count(),
            'expired' => $requests->where('status', 'EXPIRED')->count(),
            'last_sent' => $requests->max('created_at'),
        ];
    }

    protected function calculateAcceptanceRate()
    {
        $total = DriverRequest::whereDate('created_at', today())->count();
        $accepted = DriverRequest::whereDate('created_at', today())
            ->where('status', 'ACCEPTED')
            ->count();

        return $total > 0 ? round(($accepted / $total) * 100, 2) : 0;
    }

    public function loadDriverRequests()
    {
        $query = DriverRequest::with(['driver', 'order.user'])
            ->orderBy('created_at', 'desc');

        if ($this->orderId) {
            $query->where('order_id', $this->orderId);
        } else {
            // For dashboard view, show requests from cutoff date onwards
            $cutoffDate = Carbon::parse(self::STATS_CUTOFF_DATE)->startOfDay();
            $query->whereHas('order', function($q) use ($cutoffDate) {
                $q->where('created_at', '>=', $cutoffDate);
            });
        }

        if ($this->requestType !== 'ALL') {
            $query->where('request_type', $this->requestType);
        }

        $this->driverRequests = $query->limit(50)->get()->map(function ($request) {
            return [
                'id' => $request->id,
                'order_code' => $request->order->order_code ?? 'N/A',
                'driver_name' => $request->driver->name ?? 'Unknown',
                'driver_phone' => $request->driver->phone ?? 'N/A',
                'driver_id' => $request->driver->id ?? null,
                'request_type' => $request->request_type,
                'status' => $request->status,
                'created_at' => $request->created_at,
                'responded_at' => $request->responded_at,
                'expires_at' => $request->expires_at,
                'rejection_reason' => $request->rejection_reason,
                'time_to_respond' => $request->responded_at
                    ? $request->created_at->diffInSeconds($request->responded_at)
                    : null,
            ];
        })->toArray();
    }

    public function loadActiveJobs()
    {
        if ($this->orderId) {
            // For specific order, check cache for active jobs
            $this->activeJobs = [];

            // Check cache for PICKUP job
            $pickupJob = Cache::get("driver_search:{$this->orderId}:PICKUP");
            if ($pickupJob && in_array($pickupJob['status'], ['searching', 'waiting', 'expanding'])) {
                $this->activeJobs[] = $pickupJob;
            }

            // Check cache for DELIVERY job
            $deliveryJob = Cache::get("driver_search:{$this->orderId}:DELIVERY");
            if ($deliveryJob && in_array($deliveryJob['status'], ['searching', 'waiting', 'expanding'])) {
                $this->activeJobs[] = $deliveryJob;
            }
        } else {
            // For dashboard, get all active jobs
            $this->activeJobs = $this->getAllActiveJobs();
        }
    }

    protected function getAllActiveJobs()
    {
        // Get recent orders with pending requests
        $cutoffDate = Carbon::parse(self::STATS_CUTOFF_DATE)->startOfDay();

        $ordersWithPendingRequests = Order::where('created_at', '>=', $cutoffDate)
            ->where(function($query) {
                $query->whereNull('pickup_driver_id')
                    ->orWhereNull('delivery_driver_id');
            })
            ->pluck('id');

        $activeJobs = [];

        foreach ($ordersWithPendingRequests as $orderId) {
            // Check PICKUP
            $pickupJob = Cache::get("driver_search:{$orderId}:PICKUP");
            if ($pickupJob && in_array($pickupJob['status'], ['searching', 'waiting', 'expanding'])) {
                $activeJobs[] = $pickupJob;
            }

            // Check DELIVERY
            $deliveryJob = Cache::get("driver_search:{$orderId}:DELIVERY");
            if ($deliveryJob && in_array($deliveryJob['status'], ['searching', 'waiting', 'expanding'])) {
                $activeJobs[] = $deliveryJob;
            }
        }

        return $activeJobs;
    }

    public function loadUpcomingJobs()
    {
        $service = new DriverRequestService();

        if ($this->orderId) {
            // Load upcoming jobs for specific order
            $this->upcomingJobs = $service->getUpcomingJobs($this->orderId);
        } else {
            // Load all upcoming jobs for dashboard
            $this->upcomingJobs = $service->getUpcomingJobs();
        }
    }

    public function loadRadiusHistory()
    {
        if (!$this->orderId) return;

        // Group requests by creation time to infer radius expansion
        $requests = DriverRequest::where('order_id', $this->orderId)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d H:i');
            });

        $this->radiusHistory = $requests->map(function ($group, $time) {
            return [
                'time' => $time,
                'count' => $group->count(),
                'estimated_radius' => $this->estimateCurrentRadius($group),
            ];
        })->values()->toArray();
    }

    protected function estimateCurrentRadius($requests)
    {
        // Estimate radius based on number of attempts and timing
        $count = is_countable($requests) ? count($requests) : $requests->count();
        $baseRadius = 2; // Starting radius
        $increment = 2;

        // Rough estimation
        $estimatedExpansions = floor($count / 10); // Assuming ~10 drivers per radius

        return min($baseRadius + ($estimatedExpansions * $increment), 30);
    }

    public function retryDriverSearch($requestType)
    {
        if (!$this->order) return;

        $service = new DriverRequestService();
        $result = $service->resendRequestsToDrivers($this->order, $requestType);

        if ($result['success']) {
            session()->flash('message', 'Driver search restarted successfully!');
        } else {
            session()->flash('error', $result['message']);
        }

        $this->loadData();
    }

    public function manualAssign($driverId, $requestType)
    {
        if (!$this->order) return;

        $driver = \App\Models\Driver::find($driverId);
        if (!$driver) {
            session()->flash('error', 'Driver not found');
            return;
        }

        $service = new DriverRequestService();
        $result = $service->manuallyAssignDriver($this->order, $driver, $requestType);

        if ($result['success']) {
            session()->flash('message', 'Driver assigned successfully!');

            // Clear cache for this job
            Cache::forget("driver_search:{$this->order->id}:{$requestType}");
        } else {
            session()->flash('error', $result['message']);
        }

        $this->loadData();
    }

    public function cancelRequests($requestType)
    {
        if (!$this->order) return;

        $service = new DriverRequestService();
        $cancelled = $service->cancelPendingRequests($this->order, $requestType);

        // Clear cache for this job
        Cache::forget("driver_search:{$this->order->id}:{$requestType}");

        session()->flash('message', "Cancelled {$cancelled} pending requests");
        $this->loadData();
    }

    public function cancelUpcomingJob($requestType)
    {
        if (!$this->order) {
            session()->flash('error', 'No order selected');
            return;
        }

        $service = new DriverRequestService();
        $result = $service->cancelUpcomingJob($this->order, $requestType);

        if ($result['success']) {
            session()->flash('message', $result['message']);
        } else {
            session()->flash('error', $result['message']);
        }

        $this->loadData();
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
    }

    public function render()
    {
        return view('livewire.admin.drivers.order-driver-monitor')
            ->layout('components.layouts.admin-dashboard');
    }

    // Auto-refresh every X seconds if enabled
    public function getListeners()
    {
        return $this->autoRefresh
            ? ['refreshData' => '$refresh', 'echo:orders,OrderUpdated' => 'loadData']
            : ['refreshData' => '$refresh'];
    }
}

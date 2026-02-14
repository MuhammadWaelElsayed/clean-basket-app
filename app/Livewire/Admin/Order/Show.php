<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use App\Services\FCMService;
use Carbon\Carbon;
use Livewire\WithPagination;
use League\Csv\Writer;
use App\Models\Notification;
use App\Http\Controllers\Controller;
use App\Services\OrderCancellationService;
use Illuminate\Support\Facades\Log;

class Show extends Component
{
    use WithPagination;

    public $aId = '';
    public $statusId;
    public $dId = '';
    public $status = '';
    protected $orders = [];
    public $search = '';
    public $export = false;
    public $daterange = '';
    public $from_date = '';
    public $to_date = '';

    public $statuses = [];

    public $listeners = ['submit-active' => 'submitActive', 'delOrder' => 'delOrder'];

    public function mount()
    {
        abort_unless(auth()->user()->can('list_order'), 403);
    }

// ... existing code ...
    public function render()
    {
        $this->statuses = config('order_status');

        $query = Order::with(['user', 'orderItems.item', 'driver', 'pickupDriver', 'deliveryDriver'])
            ->orderBy('id', 'desc')
            ->where(function ($q) {
                $q->whereNull('type')
                    ->orWhereIn('type', ['individual', 'partner']);
            });

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $this->applySearchFilter($query);
        $this->applyDateFilter($query);

        if ($this->export) {
            $this->export($query->get());
        }

        $this->orders = $query->paginate(15);

        return view('livewire.admin.orders.index', [
            'orders' => $this->orders
        ])->layout('components.layouts.admin-dashboard');
    }

    protected function applySearchFilter($query)
    {
        if ($this->search === '') {
            return;
        }

        $query->where(function ($q) {
            $searchTerm = '%' . $this->search . '%';
            $q->where('order_code', 'LIKE', $searchTerm)
                ->orWhere('id', 'LIKE', $searchTerm)
                ->orWhere('status', 'LIKE', $searchTerm)
                ->orWhereHas('user', fn($sub) => $sub->where('first_name', 'LIKE', $searchTerm))
                ->orWhereHas('vendor', fn($sub) => $sub->where('business_name', 'LIKE', $searchTerm))
                ->orWhereHas('driver', fn($sub) => $sub->where('name', 'LIKE', $searchTerm));
        });
    }

    protected function applyDateFilter($query)
    {
        if ($this->daterange === '') {
            return;
        }

        $dateParts = explode(' to ', $this->daterange);
        $startDate = date('Y-m-d', strtotime($dateParts[0]));

        if (isset($dateParts[1])) {
            $endDate = date('Y-m-d', strtotime($dateParts[1]));
            $query->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
        }
    }

// ... existing code ...
    public function updated($field)
    {
        $orders = Order::orderBy('id', 'desc')->whereNull('deleted_at')->paginate(15);
    }


    public function clearFilter()
    {
        $this->search = '';
        $this->daterange = '';
        $this->status = '';
    }

    public function exportData()
    {
        try {
            $orders = Order::with(['user', 'vendor', 'driver', 'orderItems'])->orderBy('id', 'desc')
                ->whereNull('deleted_at')
                ->where('type', '!=', 'b2b');

            // Apply status filter first
            if ($this->status !== '') {
                $orders->where('status', $this->status);
            }

            // Apply search filter within the status filter
            if ($this->search !== '') {
                $orders->where(function ($query) {
                    $query->where('order_code', 'LIKE', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($q) {
                            $q->where('first_name', 'LIKE', '%' . $this->search . '%');
                        })
                        ->orWhereHas('vendor', function ($q) {
                            $q->where('business_name', 'LIKE', '%' . $this->search . '%');
                        })
                        ->orWhereHas('driver', function ($q) {
                            $q->where('name', 'LIKE', '%' . $this->search . '%');
                        })
                        ->orWhere('status', 'LIKE', '%' . $this->search . '%');
                });
            }

            // Apply date range filter (this must work correctly)
            if ($this->daterange !== '') {
                $date = explode(' to ', $this->daterange);
                $startDate = date('Y-m-d', strtotime($date[0]));
                if (isset($date[1])) {
                    $endDate = date('Y-m-d', strtotime($date[1]));
                    $orders->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
                } else {
                    // if there is only one date, search for that day
                    $orders->whereDate('created_at', $startDate);
                }
            }
            // Limit results to prevent memory issues
            $data = $orders->limit(5000)->get();

            // Define your data
            $columns = ['Order#', 'User', 'Phone', 'Pickup', 'Pickup Time', 'Delivery Fee', 'Order Amount', 'Order Time', 'Status', 'Carpet', 'Instructions'];

            $csv = Writer::createFromFileObject(new \SplTempFileObject());
            // Add headers to the CSV
            $csv->insertOne($columns);
            foreach ($data as $row) {
                $hasCarpet = $this->hasCarpetItems($row) ? 'Yes' : 'No';

                // Handle potential null values
                $userName = $row->user ? $row->user->first_name ?? 'N/A' : 'N/A';
                $userPhone = $row->user ? $row->user->phone ?? 'N/A' : 'N/A';
                $pickupDate = $row->pickup_date ?? 'N/A';
                $pickupTime = $row->pickup_time ?? 'N/A';
                $deliveryFee = $row->delivery_fee ?? 0;
                $grandTotal = $row->grand_total ?? 0;
                $createdAt = $row->created_at ?? 'N/A';
                $status = $row->status ?? 'N/A';
                $instructions = $row->instructions ?? 'N/A';

                $single = [
                    $row->order_code,
                    $userName,
                    $userPhone,
                    $pickupDate,
                    $pickupTime,
                    env('CURRENCY') . $deliveryFee,
                    env('CURRENCY') . $grandTotal,
                    $createdAt,
                    $status,
                    $hasCarpet,
                    $instructions
                ];
                $csv->insertOne($single);
            }
            $filename = 'orders_' . date('Y-m-d') . '.csv';
            // Open a temporary file for writing
            $file = fopen('php://temp', 'w+');
            fwrite($file, "\xEF\xBB\xBF");
            fwrite($file, $csv->getContent());
            rewind($file);
            // Set the appropriate headers for downloading the file
            $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="' . $filename . '"',];

            return response()->streamDownload(function () use ($file) {
                fpassthru($file);
            }, $filename, $headers);
        } catch (\Exception $e) {
            $this->dispatch('error', 'خطأ في التصدير: ' . $e->getMessage());
            return;
        }
    }

    public function setDel($id)
    {
        $this->dId = $id;
    }

    public function delOrder($id)
    {
        $order = Order::with('user', 'vendor')->findOrFail($id);

        // use the OrderCancellationService to process the cancellation
        $cancellationService = new OrderCancellationService();
        $result = $cancellationService->processOrderCancellation($order, 'admin');

        if ($result['success']) {
            $order->update(['status' => 'CANCELLED']);

            // prepare the success message for the admin
            $successMessage = $cancellationService->prepareAdminSuccessMessage(
                $result['total_refund_amount'],
                $result['package_refunded']
            );

            $this->dispatch('success', $successMessage);

            Log::info('Order cancelled successfully via Show component', [
                'order_id' => $order->id,
                'refund_amount' => $result['total_refund_amount'],
                'package_refunded' => $result['package_refunded']
            ]);
        } else {
            Log::error('Order cancellation failed in Show component', [
                'order_id' => $order->id,
                'error' => $result['error']
            ]);

            $this->dispatch('error', 'Failed to cancel order: ' . $result['error']);
        }
    }

    public function gotoDetails($id)
    {
        return redirect('admin/order-details/' . $id);
    }

    /**
     * Check if an order contains carpet items
     * Carpet item IDs: 94, 100, 1008, 1052
     */
    public function hasCarpetItems($order)
    {
        $carpetItemIds = [94, 100, 1008, 1052];

        // Check if order has orderItems and the relationship is loaded
        if (!$order->relationLoaded('orderItems')) {
            $order->load('orderItems');
        }

        return $order->orderItems && $order->orderItems->whereIn('item_id', $carpetItemIds)->count() > 0;
    }
}

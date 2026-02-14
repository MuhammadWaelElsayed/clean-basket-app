<?php

namespace App\Livewire\Admin\Order;

use App\Models\Driver;
use App\Models\Vendor;
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

class ShowForB2b extends Component
{
    use WithPagination;

    public $aId = '';
    public $statusId;
    public $dId = '';
    public $status = '';
    public $vendor_id = '';
    public $driver_id = '';
    public $vendors = [];
    public $drivers = [];
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
        abort_unless(auth()->user()->can('manage_b2b_orders'), 403);
    }

    public function render()
    {
        $this->vendors = Vendor::select('id', 'business_name', 'first_name', 'last_name')
            ->get()
            ->map(function ($vendor) {
                return [
                    'id' => (string) $vendor->id,
                    'name' => $vendor->business_name . ' - ' . $vendor->first_name . ' ' . $vendor->last_name
                ];
            });

        $this->drivers = Driver::select('id', 'name', 'phone')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => (string) $driver->id,
                    'name' => $driver->name . ' (' . $driver->phone . ')'
                ];
            });

        $this->statuses = config('order_status');

        $orders = Order::with(['client', 'orderItems.item'])
            ->orderBy('id', 'desc')
            ->whereNull('deleted_at')
            ->where('type', 'b2b');

        // Apply status filter first
        if ($this->status !== '') {
            $orders->where('status', $this->status);
        }
        if ($this->vendor_id !== '') {
            $orders->where('vendor_id', $this->vendor_id);
        }
        if ($this->driver_id !== '') {
            $orders->where('driver_id', $this->driver_id);
        }

        // Apply search filter within the status filter
        if ($this->search !== '') {
            $orders->where(function ($query) {
                $query->where('order_code', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('id', 'LIKE', '%' . $this->search . '%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('contact_person', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('phone', 'LIKE', '%' . $this->search . '%')
                        ->orWhere('company_name', 'LIKE', '%' . $this->search . '%');
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
        if ($this->daterange !== '') {
            // dd($this->daterange);
            $date = explode(' to ', $this->daterange);
            $startDate = date('Y-m-d', strtotime($date[0]));
            if (isset($date[1])) {
                $endDate = date('Y-m-d', strtotime($date[1]));
                $orders->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
            }
        }
        if ($this->export == true) {
            // dd('her');
            $order = $orders->get();
            $this->export($order);
        }
        $this->orders = $orders->paginate(15);

        return view('livewire.admin.orders.index-for-b2b', [
            'orders' => $this->orders
        ])->layout('components.layouts.admin-dashboard');
    }

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
            $orders = Order::with(['client', 'vendor', 'driver', 'orderItems'])->orderBy('id', 'desc')->where('type', 'b2b')->whereNull('deleted_at');

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
                $userName = $row->client ? ($row->client->contact_person .' - '. $row->client->company_name) ?? 'N/A' : 'N/A';
                $userPhone = $row->client ? $row->client->phone ?? 'N/A' : 'N/A';
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

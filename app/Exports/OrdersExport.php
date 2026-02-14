<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return Order::with([
            'paymentLogs',
            'user',
            'vendor',
            'items',
            'items.item',
            'items.serviceType',
            'items.addOns'
        ])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Order#',
            'User',
            'Phone',
            'Pickup',
            'Pickup Time',
            'Delivery Fee',
            'Order Amount',
            'Order Time',
            'Status',
            'Carpet',
            'Instructions',
            'Payment Status',
            'Payment Method',
            'Vendor',
            'Grand Total',
            'Items Count',
            'Services'
        ];
    }

    public function map($order): array
    {
        // Format pickup information
        $pickupType = $order->pickup_type ?? 'delivery'; // Assuming you have pickup_type field
        $pickupTime = $order->pickup_time ?? 'N/A';

        // Get user information
        $userName = $order->user ? $order->user->name : 'N/A';
        $userPhone = $order->user ? $order->user->phone : 'N/A';

        // Get vendor information
        $vendorName = $order->vendor ? $order->vendor->business_name : 'N/A';

        // Format items and services
        $itemsCount = $order->items->count();
        $services = $this->formatServices($order->items);

        // Format amounts
        $deliveryFee = number_format($order->delivery_fee ?? 0, 2);
        $orderAmount = number_format($order->order_amount ?? 0, 2);
        $grandTotal = number_format($order->grand_total, 2);

        return [
            $order->order_code, // Order#
            $userName, // User
            $userPhone, // Phone
            $pickupType, // Pickup
            $pickupTime, // Pickup Time
            $deliveryFee, // Delivery Fee
            $orderAmount, // Order Amount
            $order->created_at, // Order Time (already formatted in model)
            $order->status_display, // Status
            $order->is_carpet ? 'Yes' : 'No', // Carpet
            $order->instructions ?? 'N/A', // Instructions
            $order->pay_status ?? 'N/A', // Payment Status
            $order->payment_method ?? 'N/A', // Payment Method
            $vendorName, // Vendor
            $grandTotal, // Grand Total
            $itemsCount, // Items Count
            $services // Services
        ];
    }

    protected function formatServices($items): string
    {
        $services = [];

        foreach ($items as $item) {
            $itemName = $item->item ? $item->item->name : 'Unknown Item';
            $serviceType = $item->serviceType ? $item->serviceType->name : 'Unknown Service';

            $serviceInfo = "{$itemName} ({$serviceType})";

            // Add add-ons if any
            if ($item->addOns->isNotEmpty()) {
                $addons = $item->addOns->pluck('name')->implode(', ');
                $serviceInfo .= " + {$addons}";
            }

            $services[] = $serviceInfo;
        }

        return implode('; ', $services);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],

            // Style the header row
            'A1:T1' => [
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6E6FA']
                ]
            ],
        ];
    }
}

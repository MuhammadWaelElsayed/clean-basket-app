<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderTracking;
use Carbon\Carbon;

class Tracking extends Component
{
    public $orderId;
    public $order;
    public $trackingHistory = [];
    public $statuses = [];

    // حالات الطلب بالترتيب
    public $orderStatuses = [
        'DRAFT' => 'مسودة',
        'PLACED' => 'تم الطلب',
        'ON_THE_WAY_FOR_PICKUP' => 'في الطريق للاستلام',
        'PICKED_UP' => 'تم الاستلام',
        'ARRIVED' => 'وصل إلى الشريك',
        'PROCESSING' => 'قيد المعالجة',
        'CONFIRMED_PAID' => 'تم التأكيد والدفع',
        'ON_THE_WAY_TO_PARTNER' => 'في الطريق للشريك',
        'READY_TO_DELIVER' => 'جاهز للتوصيل',
        'PICKED_FOR_DELIVER' => 'تم الاستلام للتوصيل',
        'DELIVERED' => 'تم التوصيل',
        'CANCELLED' => 'ملغي'
    ];

    public function mount($id)
    {
        $this->statuses = config('order_status');
        $this->orderId = $id;
        $this->order = Order::with(['user', 'vendor', 'driver', 'deliveryAddress', 'orderItems.item'])
            ->findOrFail($this->orderId);

        // جلب سجل التتبع
        $this->trackingHistory = OrderTracking::where('order_id', $this->orderId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * التحقق من أن الحالة قد تمت
     */
    public function isStatusCompleted($status)
    {
        // إذا كانت الحالة في tracking history
        if ($this->trackingHistory->contains('status', $status)) {
            return true;
        }

        // إذا كانت الحالة الأولى (PLACED) ولم توجد في tracking، نعتبرها مكتملة
        if ($status === 'PLACED') {
            return true;
        }

        return false;
    }

    /**
     * الحصول على وقت الحالة
     */
    public function getStatusTime($status)
    {
        $tracking = $this->trackingHistory->where('status', $status)->first();
        if ($tracking) {
            return $tracking->created_at;
        }

        // إذا لم توجد في tracking، نتحقق من تاريخ إنشاء الطلب للحالة الأولى
        if ($status === 'PLACED') {
            return $this->order->created_at;
        }

        return null;
    }

    /**
     * التحقق من أن الحالة هي الحالة الحالية
     */
    public function isCurrentStatus($status)
    {
        // إذا كانت الحالة الحالية للطلب
        if ($this->order->status === $status) {
            return true;
        }

        // إذا كانت الحالة الأخيرة في tracking history
        $lastTracking = $this->trackingHistory->last();
        if ($lastTracking && $lastTracking->status === $status) {
            return true;
        }

        return false;
    }

    public function render()
    {
        return view('livewire.admin.orders.tracking')
            ->layout('components.layouts.admin-dashboard');
    }
}


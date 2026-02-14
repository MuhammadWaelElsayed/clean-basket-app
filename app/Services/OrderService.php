<?php

// File: app/Services/OrderService.php
namespace App\Services;

use App\Models\Order;
use App\Models\OrderTracking;
use App\Http\Controllers\Controller;

class OrderService
{
    /**
     * Process the given order: apply fees, update status, track and notify.
     */
    public function process(Order $order): void
    {
        // Fresh instance from DB
        $order = $order->fresh();

        // 1️⃣ Apply delivery fee
        $this->applyDeliveryFee($order);

        // 2️⃣ Update status and create tracking record
        // $order->update(['status' => 'PROCESSING']);
        // OrderTracking::create([
        //     'order_id' => $order->id,
        //     'status'   => 'PROCESSING',
        // ]);

        // 3️⃣ Notify user
        // Controller::sendNotifications([
        //     'title'      => "Your order #{$order->order_code} is now processing",
        //     'title_ar'   => "طلبك رقم {$order->order_code} قيد المعالجة",
        //     'message'    => "We're processing your order #{$order->order_code}.",
        //     'message_ar' => "نقوم الآن بمعالجة طلبك رقم {$order->order_code}.",
        //     'user'       => $order->user,
        // ], 'user');

        // 4️⃣ Notify driver if assigned
        if ($order->driver) {
            Controller::sendNotifications([
                'title'      => "New order assigned #{$order->order_code}",
                'title_ar'   => "تم تعيين طلب جديد #{$order->order_code}",
                'message'    => "A new order #{$order->order_code} has been assigned to you.",
                'message_ar' => "تم تعيين طلب جديد رقم {$order->order_code} لك.",
                'user'       => $order->driver,
                'order'      => $order,
            ], 'driver');
        }

        // 5️⃣ Notify vendor if available
        if ($order->vendor) {
            Controller::sendNotifications([
                'title'      => "New order #{$order->order_code} received",
                'title_ar'   => "هناك طلب جديد رقم {$order->order_code}",
                'message'    => "Order #{$order->order_code} has been placed by {$order->user->name}.",
                'message_ar' => "تم تقديم طلب رقم {$order->order_code} من قِبل {$order->user->name}.",
                'user'       => $order->vendor,
                'order'      => $order,
            ], 'vendor');
        }

        // 6️⃣ Notify admin
        // Controller::sendNotifications([
        //     'title'   => "Order #{$order->order_code} processing",
        //     'message' => "Order #{$order->order_code} is now in processing stage.",
        //     'link'    => "admin/order-details/{$order->id}",
        // ], 'admin');
    }

    /**
     * Calculate and save delivery fee based on the user's active package.
     */
    protected function applyDeliveryFee(Order $order): void
    {
        $activePackage = $order->user->userPackages()
            ->where('is_active', true)
            ->where('end_date', '>=', now())
            ->latest('start_date')
            ->with('package')
            ->first();

        $fee = $activePackage
            ? $activePackage->package->delivery_fee
            : 0;

        $order->update(['delivery_fee' => $fee]);
    }
}

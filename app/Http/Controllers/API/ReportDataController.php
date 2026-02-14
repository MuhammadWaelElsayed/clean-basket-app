<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;

class ReportDataController extends Controller
{
    // public function ordersReport()
    // {
    //     $orders = DB::table('orders')
    //         ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
    //         ->select(
    //             'orders.order_code',
    //             'orders.order_image',
    //             'orders.created_at',
    //             'vendors.business_name AS vendor_name'
    //         )
    //         ->where('orders.status', 'DELIVERED') // ✅ شرط حالة الطلب
    //         ->whereBetween('orders.created_at', ['2025-01-01', '2025-07-04'])
    //         ->orderBy('orders.created_at', 'desc')
    //         ->get();

    //     return response()->json($orders);
    // }

    public function listForSelect()
    {
        // نُعيد فقط العمودين id و business_name
        $vendors = Vendor::select('id', 'business_name')->get();
        return response()->json($vendors);
    }


    /**
     * تقرير الطلبات التي تم تسليمها ومدفوعة
     */
    public function ordersReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'vendor_id' => 'required|integer|exists:vendors,id',
        ]);

        $orders = DB::table('orders')
            ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->join('order_items as oi', 'oi.order_id', '=', 'orders.id')
            ->join('items', 'items.id', '=', 'oi.item_id')
            ->select(
                'orders.order_code',
                'orders.order_image',
                'orders.created_at',
                'vendors.business_name AS vendor_name',
                'orders.pay_status',
                // هنا نأخذ المجموع الكلّي من عمود grand_total
                'orders.grand_total',
                // تجميع الأصناف
                DB::raw("GROUP_CONCAT(DISTINCT items.name SEPARATOR ', ') AS items"),
                // مجموع الكميّات
                DB::raw("SUM(oi.quantity) AS items_count")
            )
            ->where('orders.status', 'DELIVERED')
            ->where('orders.pay_status', 'Paid')
            ->whereBetween('orders.created_at', [
                $request->date_from,
                $request->date_to,
            ])
            ->where('vendors.id', $request->vendor_id)
            ->groupBy(
                'orders.id',
                'orders.order_code',
                'orders.order_image',
                'orders.created_at',
                'vendors.business_name',
                'orders.pay_status',
                'orders.grand_total'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function allOrderDeliveredByDate(Request $request)
    {
        $from = $request->input('from', '2024-10-01');
        $to   = $request->input('to', '2025-05-31');

        $orders = Order::with(['user', 'vendor'])
            ->where('status', 'DELIVERED')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'order_code'     => $order->order_code,
                    'order_id'       => $order->id,
                    'created_at'     => $order->created_at,
                    'customer_name'  => trim($order->user->first_name . ' ' . $order->user->last_name),
                    'phone'          => $order->user->phone,
                    'email'          => $order->user->email,
                    'vendor_name'    => $order->vendor->business_name,
                    'total_paid'     => $order->grand_total,
                ];
            });

        return response()->json($orders);
    }

    /**
     * تقرير الطلبات – الاستلام/التسليم + معلومات العميل والمغسلة + هل يوجد سجاد + حالة الطلب
     */
    public function ordersPickupDropoffReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'status'    => 'nullable|array',
            'status.*'  => 'string|in:PLACED,READY_TO_DELIVER,ON_THE_WAY_FOR_PICKUP,ON_THE_WAY_TO_PARTNER,ARRIVED,PROCESSING,CONFIRMED_PAID,PICKED_FOR_DELIVER,DELIVERED,CANCELLED',
            'limit'     => 'nullable|integer|min:1|max:1000',
        ]);

        $statuses = $request->input('status', ['PLACED', 'READY_TO_DELIVER']);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $limit = $request->input('limit', 100);

        // بناء الاستعلام مع تحسينات
        $query = DB::table('orders as o')
            ->leftJoin('user_address as ua', 'ua.id', '=', 'o.address_id')
            ->leftJoin('vendors as v', 'v.id', '=', 'o.vendor_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('areas as a', 'a.id', '=', 'ua.area')
            ->select(
                'o.id as order_id',
                'o.order_code',
                'o.created_at',
                DB::raw("COALESCE(a.name, CAST(ua.area AS CHAR)) as neighborhood"),
                DB::raw("CONCAT_WS(' ', o.pickup_date, o.pickup_time) as pickup_at"),
                DB::raw("CONCAT_WS(' ', o.dropoff_date, o.dropoff_time) as dropoff_at"),
                DB::raw("CONCAT(ua.lat, ',', ua.lng) as customer_location"),
                'v.business_name as laundry_name',
                'u.first_name as customer_name',
                'u.phone as customer_phone',
                'u.email as customer_email',
                'o.status as order_status',
                'o.grand_total',
                DB::raw("CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM order_items oi
                        WHERE oi.order_id = o.id
                        AND oi.item_id IN (94, 100, 1008, 1052)
                    )
                    THEN 'Yes'
                    ELSE 'No'
                END as has_carpet")
            )
            ->whereNull('o.deleted_at')
            ->whereBetween('o.pickup_date', [$dateFrom, $dateTo])
            ->whereIn('o.status', $statuses)
            ->orderBy('o.created_at', 'desc');

        // إضافة حد أقصى للنتائج
        $orders = $query->limit($limit)->get();

        // حساب العدد الإجمالي للطلبات
        $totalCount = DB::table('orders as o')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.pickup_date', [$dateFrom, $dateTo])
            ->whereIn('o.status', $statuses)
            ->count();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'total_count' => $totalCount,
            'returned_count' => $orders->count(),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $statuses,
                'limit' => $limit
            ]
        ]);
    }

    /**
     * تقرير الطلبات بدون حدود - لاختبار جميع الطلبات
     */
    public function ordersPickupDropoffReportAll(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'status'    => 'nullable|array',
            'status.*'  => 'string|in:PLACED,READY_TO_DELIVER,ON_THE_WAY_FOR_PICKUP,ON_THE_WAY_TO_PARTNER,ARRIVED,PROCESSING,CONFIRMED_PAID,PICKED_FOR_DELIVER,DELIVERED,CANCELLED',
        ]);

        $statuses = $request->input('status', ['PLACED', 'READY_TO_DELIVER']);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = DB::table('orders as o')
            ->leftJoin('user_address as ua', 'ua.id', '=', 'o.address_id')
            ->leftJoin('vendors as v', 'v.id', '=', 'o.vendor_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('areas as a', 'a.id', '=', 'ua.area')
            ->select(
                'o.id as order_id',
                'o.order_code',
                'o.created_at',
                DB::raw("COALESCE(a.name, CAST(ua.area AS CHAR)) as neighborhood"),
                DB::raw("CONCAT_WS(' ', o.pickup_date, o.pickup_time) as pickup_at"),
                DB::raw("CONCAT_WS(' ', o.dropoff_date, o.dropoff_time) as dropoff_at"),
                DB::raw("CONCAT(ua.lat, ',', ua.lng) as customer_location"),
                'v.business_name as laundry_name',
                'u.first_name as customer_name',
                'u.phone as customer_phone',
                'u.email as customer_email',
                'o.status as order_status',
                'o.grand_total',
                DB::raw("CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM order_items oi
                        WHERE oi.order_id = o.id
                        AND oi.item_id IN (94, 100, 1008, 1052)
                    )
                    THEN 'Yes'
                    ELSE 'No'
                END as has_carpet")
            )
            ->whereNull('o.deleted_at');

        // إضافة فلترة التاريخ إذا تم توفيرها
        if ($dateFrom && $dateTo) {
            $query->whereBetween('o.pickup_date', [$dateFrom, $dateTo]);
        }

        // إضافة فلترة الحالة
        $query->whereIn('o.status', $statuses);

        $orders = $query->orderBy('o.created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'total_count' => $orders->count(),
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'status' => $statuses
            ]
        ]);
    }





    /**
     * فحص البيانات - لمعرفة سبب استرجاع صف واحد فقط
     */
    public function debugOrdersData(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
        ]);

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // فحص الطلبات بدون فلترة
        $allOrders = DB::table('orders')
            ->whereNull('deleted_at')
            ->count();

        // فحص الطلبات في النطاق الزمني
        $ordersInDateRange = DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('pickup_date', [$dateFrom, $dateTo])
            ->count();

        // فحص الطلبات بالحالات المختلفة
        $ordersByStatus = DB::table('orders')
            ->whereNull('deleted_at')
            ->whereBetween('pickup_date', [$dateFrom, $dateTo])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // فحص الطلبات مع الـ JOINs
        $ordersWithJoins = DB::table('orders as o')
            ->leftJoin('user_address as ua', 'ua.id', '=', 'o.address_id')
            ->leftJoin('vendors as v', 'v.id', '=', 'o.vendor_id')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->whereNull('o.deleted_at')
            ->whereBetween('o.pickup_date', [$dateFrom, $dateTo])
            ->count();

        // فحص آخر 10 طلبات
        $lastOrders = DB::table('orders')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->select('id', 'order_code', 'pickup_date', 'status', 'created_at')
            ->get();

        return response()->json([
            'success' => true,
            'debug_info' => [
                'total_orders_in_db' => $allOrders,
                'orders_in_date_range' => $ordersInDateRange,
                'orders_with_joins' => $ordersWithJoins,
                'orders_by_status' => $ordersByStatus,
                'last_10_orders' => $lastOrders,
                'date_range' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ]
            ]
        ]);
    }
}

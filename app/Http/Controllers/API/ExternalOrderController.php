<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExternalOrderController extends Controller
{
   // الحقول المسموح تعديلها خارجيًا (عدّلها حسب سياستك)
   protected array $fillableExternal = [
    'pickup_date', 'pickup_time',
    'dropoff_date','dropoff_time',
    'instructions',
    'pay_method', 'pay_status',        // انتبه: التغيير المالي حسب سياستك
    'status', 'item_status',
    'deliver_lat','deliver_lng','deliver_image',
    'order_image','payment_response',
    'address_id','driver_id' , 'vendor_id',  'driver_id', // إن رغبت بالسماح
];

public function show(Request $request, Order $order = null)
{
    // التحقق من صحة query parameters
    $rules = [
        'order_id' => ['sometimes', 'nullable', 'integer'],
        'pickup_date' => ['sometimes', 'nullable', 'date'],
        'pickup_time' => ['sometimes', 'nullable', 'string', 'max:255'],
        'dropoff_date' => ['sometimes', 'nullable', 'date'],
        'dropoff_time' => ['sometimes', 'nullable', 'string', 'max:255'],
        'pay_status' => ['sometimes', 'nullable', 'string', 'max:255'],
        'status' => ['sometimes', 'nullable', 'string', 'max:255'],
        'driver_id' => ['sometimes', 'nullable', 'integer'],
        'vendor_id' => ['sometimes', 'nullable', 'integer'],
        'user_id' => ['sometimes', 'nullable', 'integer'],
        'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
    ];

    $queryParams = $request->validate($rules);

    // تطبيق الفلترة بناءً على query parameters
    $query = Order::query();

    // إضافة فلتر معرف الطلب إذا تم تمريره
    if (!empty($queryParams['order_id'])) {
        $query->where('id', $queryParams['order_id']);
    } elseif ($order) {
        // إذا تم تمرير الطلب من الراوت، استخدمه
        $query->where('id', $order->id);
    }

    // إضافة الفلاتر إذا تم تمريرها
    if (!empty($queryParams['pickup_date'])) {
        $query->whereDate('pickup_date', $queryParams['pickup_date']);
    }

    if (!empty($queryParams['pickup_time'])) {
        $query->where('pickup_time', 'like', '%' . $queryParams['pickup_time'] . '%');
    }

    if (!empty($queryParams['dropoff_date'])) {
        $query->whereDate('dropoff_date', $queryParams['dropoff_date']);
    }

    if (!empty($queryParams['dropoff_time'])) {
        $query->where('dropoff_time', 'like', '%' . $queryParams['dropoff_time'] . '%');
    }

    if (!empty($queryParams['pay_status'])) {
        $query->where('pay_status', $queryParams['pay_status']);
    }

    if (!empty($queryParams['status'])) {
        $query->where('status', $queryParams['status']);
    }

    if (!empty($queryParams['driver_id'])) {
        $query->where('driver_id', $queryParams['driver_id']);
    }

    if (!empty($queryParams['vendor_id'])) {
        $query->where('vendor_id', $queryParams['vendor_id']);
    }

    if (!empty($queryParams['user_id'])) {
        $query->where('user_id', $queryParams['user_id']);
    }

    if (!empty($queryParams['phone'])) {
        $query->whereHas('user', function($q) use ($queryParams) {
            $q->where('phone', 'like', '%' . $queryParams['phone'] . '%');
        });
    }

    // تنفيذ الاستعلام
    $filteredOrder = $query->with(['vendor', 'driver', 'user'])->first();

    if (!$filteredOrder) {
        return response()->json([
            'status' => false,
            'message' => 'No orders found with the specified filters'
        ], 404);
    }

    return response()->json(['status' => true, 'data' => $filteredOrder]);
}

/**
 * عرض الطلبات في فترة زمنية معينة
 */
public function getOrdersByDateRange(Request $request)
{
    // التحقق من صحة query parameters
    $rules = [
        'start_date' => ['required', 'date'],
        'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        'pickup_date' => ['sometimes', 'nullable', 'date'],
        'pickup_time' => ['sometimes', 'nullable', 'string', 'max:255'],
        'dropoff_date' => ['sometimes', 'nullable', 'date'],
        'dropoff_time' => ['sometimes', 'nullable', 'string', 'max:255'],
        'pay_status' => ['sometimes', 'nullable', 'string', 'max:255'],
        'status' => ['sometimes', 'nullable', 'string', 'max:255'],
        'driver_id' => ['sometimes', 'nullable', 'integer'],
        'vendor_id' => ['sometimes', 'nullable', 'integer'],
        'user_id' => ['sometimes', 'nullable', 'integer'],
        'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
        'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100'],
        'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
    ];

    $queryParams = $request->validate($rules);

    // بناء الاستعلام
    $query = Order::query();

    // فلتر الفترة الزمنية (افتراضي على pickup_date)
    $query->whereBetween('pickup_date', [
        $queryParams['start_date'],
        $queryParams['end_date']
    ]);

    // إضافة الفلاتر الإضافية إذا تم تمريرها
    if (!empty($queryParams['pickup_date'])) {
        $query->whereDate('pickup_date', $queryParams['pickup_date']);
    }

    if (!empty($queryParams['pickup_time'])) {
        $query->where('pickup_time', 'like', '%' . $queryParams['pickup_time'] . '%');
    }

    if (!empty($queryParams['dropoff_date'])) {
        $query->whereDate('dropoff_date', $queryParams['dropoff_date']);
    }

    if (!empty($queryParams['dropoff_time'])) {
        $query->where('dropoff_time', 'like', '%' . $queryParams['dropoff_time'] . '%');
    }

    if (!empty($queryParams['pay_status'])) {
        $query->where('pay_status', $queryParams['pay_status']);
    }

    if (!empty($queryParams['status'])) {
        $query->where('status', $queryParams['status']);
    }

    if (!empty($queryParams['driver_id'])) {
        $query->where('driver_id', $queryParams['driver_id']);
    }

    if (!empty($queryParams['vendor_id'])) {
        $query->where('vendor_id', $queryParams['vendor_id']);
    }

    if (!empty($queryParams['user_id'])) {
        $query->where('user_id', $queryParams['user_id']);
    }

    if (!empty($queryParams['phone'])) {
        $query->whereHas('user', function($q) use ($queryParams) {
            $q->where('phone', 'like', '%' . $queryParams['phone'] . '%');
        });
    }

    // تحميل العلاقات
    $query->with(['vendor', 'driver', 'user']);

    // ترتيب النتائج حسب تاريخ الاستلام
    $query->orderBy('pickup_date', 'desc');

    // تطبيق الصفحات
    $perPage = $queryParams['per_page'] ?? 15;
    $orders = $query->paginate($perPage);

    // إضافة معلومات إضافية للاستجابة
    $response = [
        'status' => true,
        'data' => $orders->items(),
        'pagination' => [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
        ],
        'filters_applied' => [
            'start_date' => $queryParams['start_date'],
            'end_date' => $queryParams['end_date'],
            'additional_filters' => array_filter($queryParams, function($key) {
                return !in_array($key, ['start_date', 'end_date', 'per_page', 'page']);
            }, ARRAY_FILTER_USE_KEY)
        ]
    ];

    return response()->json($response);
}

/*
TODO: update the order
Who changed the order?
*/
public function update(Request $request, Order $order)
{
    // مثال: ربط الطلب بجهة خارجية لاحقًا عبر middleware
    // $externalNo = $request->attributes->get('external_number');

    // Idempotency (اختياري)
    $idempKey = $request->header('Idempotency-Key');
    if ($idempKey) {
        $exists = DB::table('idempotency_keys')
            ->where('key', $idempKey)
            ->where('resource', 'orders:update')
            ->exists();
        if ($exists) {
            // تحميل العلاقات المطلوبة
            $order->load(['vendor', 'driver', 'user']);
            return response()->json(['status' => true, 'data' => $order]); // نفس النتيجة السابقة
        }
    }

    // الحالات المسموحة من enum (حسب صورتك)
    $allowedStatuses = [
        'DRAFT','PLACED','PICKED_UP','ON_THE_WAY_FOR_PICKUP','ON_THE_WAY_TO_PARTNER',
        'ARRIVED','PROCESSING','CONFIRMED_PAID','READY_TO_DELIVER','PICKED_FOR_DELIVER',
        'DELIVERED','CANCELLED'
    ];

    // القيم المسموحة للـ pay_status (استنادًا لمدى استخدامك)
    $allowedPayStatuses = ['Unpaid','Paid','Partial'];

    // rules تدعم التحديث الجزئي (sometimes)
    $rules = [
        'pickup_date' => ['sometimes','nullable','date'],
        'pickup_time' => ['sometimes','nullable','string','max:255'],
        'dropoff_date'=> ['sometimes','nullable','date'],
        'dropoff_time'=> ['sometimes','nullable','string','max:255'],

        'instructions'=> ['sometimes','nullable','string'],
        'pay_method'  => ['sometimes','nullable','string','max:255'],
        'pay_status'  => ['sometimes','nullable','string', Rule::in($allowedPayStatuses)],

        'status'      => ['sometimes','nullable','string', Rule::in($allowedStatuses)],
        'item_status' => ['sometimes','nullable','string','max:50'],

        'sub_total'   => ['sometimes','nullable','numeric','min:0'],
        'promo_discount'=> ['sometimes','nullable','numeric','min:0'],
        'vat'         => ['sometimes','nullable','numeric','min:0'],
        'delivery_fee'=> ['sometimes','nullable','numeric','min:0'],
        'grand_total' => ['sometimes','nullable','numeric','min:0'],
        'due_amount'  => ['sometimes','nullable','numeric','min:0'],

        'deliver_lat' => ['sometimes','nullable','string','max:250'],
        'deliver_lng' => ['sometimes','nullable','string','max:250'],
        'deliver_image'=>['sometimes','nullable','string','max:250'],
        'order_image' => ['sometimes','nullable','string','max:250'],
        'payment_response'=>['sometimes','nullable','string'],

        'address_id'  => ['sometimes','nullable','integer'],
        'driver_id'   => ['sometimes','nullable','integer'],
        'sorting'     => ['sometimes','nullable','string', Rule::in(['client','vendor'])],
    ];

    $data = $request->validate($rules);

    // فلترة للحقول المسموحة فقط
    $update = array_intersect_key($data, array_flip($this->fillableExternal));

    if (empty($update)) {
        return response()->json(['status' => false, 'message' => 'No valid fields to update'], 422);
    }

    $order->fill($update);
    $order->save();

    // خزّن المفتاح المميّز إن أُرسل (اختياري)
    if ($idempKey) {
        DB::table('idempotency_keys')->updateOrInsert(
            ['key' => $idempKey, 'resource' => 'orders:update'],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    // تحميل العلاقات المطلوبة
    $order->load(['vendor', 'driver', 'user']);

    return response()->json(['status' => true, 'data' => $order]);
}
}

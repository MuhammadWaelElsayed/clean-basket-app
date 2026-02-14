<?php

namespace App\Http\Controllers\API\Partner;

use App\Http\Controllers\Controller;
use App\Http\Middleware\ValidatePartner;
use App\Jobs\ProcessOrderJob;
use App\Models\AddOn;
use App\Models\Driver;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderPriority;
use App\Models\OrderTracking;
use App\Models\Service;
use App\Models\SettingsServiceFee;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $phone = $request->input('phone');

        $source = ValidatePartner::getSource($request->header('X-Source'));

        $query = Order::where('type', 'partner')
            ->where('source_secret', $source['secret'])
            ->with(['items.addOns']);

        if (filled($request->phone)) {
            $query->whereRelation('user', 'phone', 'like', '%' . $phone . '%');
        }

        // Filter by status
        if (filled($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by multiple statuses
        if (filled($request->statuses)) {
            $statuses = is_array($request->statuses)
                ? $request->statuses
                : explode(',', $request->statuses);
            $query->whereIn('status', $statuses);
        }

        // Filter by payment status
        if (filled($request->pay_status)) {
            $query->where('pay_status', $request->pay_status);
        }

        // Filter by sorting type (vendor/client)
        if (filled($request->sorting)) {
            $query->where('sorting', $request->sorting);
        }

        // Filter by vendor
        if (filled($request->vendor_id)) {
            $query->where('vendor_id', $request->vendor_id);
        }

        // Filter by order code
        if (filled($request->order_code)) {
            $query->where('order_code', 'like', '%' . $request->order_code . '%');
        }

        // Filter by date range - pickup date
        if (filled($request->pickup_date_from)) {
            $query->where('pickup_date', '>=', $request->pickup_date_from);
        }
        if (filled($request->pickup_date_to)) {
            $query->where('pickup_date', '<=', $request->pickup_date_to);
        }

        // Filter by date range - dropoff date
        if (filled($request->dropoff_date_from)) {
            $query->where('dropoff_date', '>=', $request->dropoff_date_from);
        }
        if (filled($request->dropoff_date_to)) {
            $query->where('dropoff_date', '<=', $request->dropoff_date_to);
        }

        // Filter by date range - created at
        if (filled($request->created_from)) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if (filled($request->created_to)) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Filter by timeslot
        if (filled($request->has_timeslot)) {
            if (filter_var($request->has_timeslot, FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNotNull('timeslot');
            } else {
                $query->whereNull('timeslot');
            }
        }

        // Filter by service fee applied
        if (filled($request->service_fee_applied)) {
            $query->where('service_fee_applied', $request->service_fee_applied);
        }

        // Filter by grand total range
        if (filled($request->grand_total_min)) {
            $query->where('grand_total', '>=', $request->grand_total_min);
        }
        if (filled($request->grand_total_max)) {
            $query->where('grand_total', '<=', $request->grand_total_max);
        }

        // Filter by address
        if (filled($request->address_id)) {
            $query->where('address_id', $request->address_id);
        }

        // Filter by driver
        if (filled($request->driver_id)) {
            $query->where('driver_id', $request->driver_id);
        }

        // Filter by has items
        if (filled($request->has_items)) {
            if (filter_var($request->has_items, FILTER_VALIDATE_BOOLEAN)) {
                $query->has('items');
            } else {
                $query->doesntHave('items');
            }
        }

        // Filter by promo code
        if (filled($request->has_promo)) {
            if (filter_var($request->has_promo, FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNotNull('promo_code');
            } else {
                $query->whereNull('promo_code');
            }
        }

        // Filter by voucher
        if (filled($request->has_voucher)) {
            if (filter_var($request->has_voucher, FILTER_VALIDATE_BOOLEAN)) {
                $query->whereNotNull('voucher_id');
            } else {
                $query->whereNull('voucher_id');
            }
        }

        // Search functionality
        if (filled($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhere('instructions', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSortFields = [
            'id', 'order_code', 'created_at', 'updated_at',
            'pickup_date', 'dropoff_date', 'grand_total',
            'sub_total', 'status'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $perPage = min($perPage, 100); // Max 100 items per page

        if (filled($request->paginate) && !$request->paginate) {
            $orders = $query->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'orders' => $orders
                ]
            ]);
        }

        $orders = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => [
                'orders' => $orders->items(),
                'pagination' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ]
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $source = ValidatePartner::getSource($request->header('X-Source'));

        $order = Order::where('type', 'partner')
            ->where('id', $id)
            ->where('source_secret', $source['secret'])
            ->with(['items.addOns', 'vendor', 'driver', 'user'])
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data' => [
                'order' => $order
            ]
        ]);
    }

    public function rate(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|exists:users,phone',
            'order_code' => 'required|exists:orders,order_code',
            'rate' => 'required|in:Excellent,Very Good,Good,Acceptable,Weak',
            'description' => 'required|string|max:255'
        ]);

        if ($ticket = Ticket::where('order_code', $data['order_code'])->first()) {
            return response()->json(['status' => false, 'message' => 'Order already rated.'], 400);
        }

        $ticket = Ticket::create([
            'user_id' => User::firstWhere('phone', $data['phone'])->id,
            'issue_category_id' => 6,
            'order_code' => $data['order_code'],
            'description' => $data['description'],
            'sub_issue_category_id' => match ($data['rate']) {
                'Excellent' => 16,
                'Very Good' => 17,
                'Good' => 18,
                'Acceptable' => 19,
                'Weak' => 20,
            },
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Thank you for your feedback.'
        ], 201);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    // protected $hidden = [
    //     'updated_at',
    // ];

    protected $appends = [
        'status_display',
        'sub_total',
        'vat_amount',
        'grand_total',
        // 'delivery_eta',
        'due_amount',
        // 'paid_amount',
    ];

    // ↓ مهم: نخلي dropoff_time نصّاً حتى لا يحاول Eloquent تحويله تلقائياً إلى Carbon
    protected $casts = [
        'due_amount'   => 'decimal:2',
        'is_carpet' => 'boolean',
        'timeslot' => 'datetime:Y-m-d H:i:s',
        // 'dropoff_time' => 'string',
        // 'dropoff_date' => 'date:Y-m-d',
    ];

    protected $hidden = [
        'source_secret',
    ];
    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)
            ->setTimezone(config('app.timezone'))
            ->format('d M, Y h:i A');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)
            ->setTimezone(config('app.timezone'))
            ->format('d M, Y h:i A');
    }

    public function getBillAttribute($val)
    {
        return $val
            ? asset("storage/uploads/{$val}")
            : null;
    }

    public function getOrderCodeAttribute()
    {
        return 'CB' . $this->id;
    }

    public function getStatusDisplayAttribute()
    {
        if (request('language') === 'ar') {
            return config('order_status_ar')[$this->status] ?? '--';
        }
        return $this->status;
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id')->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(B2bClient::class, 'user_id');
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id')->withTrashed();
    }

    public function driver()
    {
        return $this->hasOne(Driver::class, 'id', 'driver_id')->withTrashed();
    }

    public function deliveryAddress()
    {
        return $this->hasOne(UserAddress::class, 'id', 'address_id')->withTrashed();
    }

    public function tracking()
    {
        return $this->hasMany(OrderTracking::class, 'order_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'related_order_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getSubTotalAttribute(): float
    {
        $itemsTotal = (float) $this->items->sum('total_price');
        $deliveryFee = (float) ($this->delivery_fee ?? 0);
        return $itemsTotal + $deliveryFee;
    }

    public function getVatAmountAttribute(): float
    {
        $itemsTotal = (float) $this->items->sum('total_price');
        $deliveryFee = (float) ($this->delivery_fee ?? 0);
        $taxRate = env('TAX_RATE', 0);
        return round(($itemsTotal + $deliveryFee) * $taxRate, 2);
    }

    // public function getGrandTotalAttribute(): float
    // {
    //     $itemsTotal = (float) $this->items->sum('total_price');
    //     $deliveryFee = (float) ($this->delivery_fee ?? 0);
    //     $vatAmount = $this->vat_amount;

    //     Log::info("Items Total: {$itemsTotal}");
    //     Log::info("Delivery Fee: {$deliveryFee}");
    //     Log::info("VAT Amount: {$vatAmount}");

    //     $total = $itemsTotal + $deliveryFee + $vatAmount;
    //     Log::info("Total: {$total}");
    //     if (! empty($this->promo_discount)) {
    //         $total -= $this->promo_discount;
    //     }

    //     Log::info("Total after discount: {$total}");
    //     return round($total, 2);
    // }
    public function getGrandTotalAttribute(): float
    {
        // إذا كان grand_total محفوظ في قاعدة البيانات، استخدمه
        if (isset($this->attributes['grand_total']) && $this->attributes['grand_total'] !== null) {
            return (float) $this->attributes['grand_total'];
        }

        // وإلا احسبه بنفس منطق PromoController
        $itemsTotal  = (float) $this->items->sum('total_price');
        $deliveryFee = (float) ($this->delivery_fee ?? 0);
        $serviceFee  = (float) ($this->service_fee ?? 0);
        $promoDiscount = (float) ($this->promo_discount ?? 0);

        // الخصم يطبق على المبلغ الأساسي (sub_total + delivery_fee)
        $baseAmount = max(0, $itemsTotal + $deliveryFee - $promoDiscount);

        // service_fee لا يخضع للخصم
        $vatAmount = (float) ($this->vat ?? 0);

        return round($baseAmount + $serviceFee + $vatAmount, 2);
    }

    public function getDueAmountAttribute(): float
    {
        $due = (float) ($this->attributes['due_amount'] ?? 0);
        return round(max(0, $due), 2);
    }


    // public function getDueAmountAttribute(): float
    // {
    //     return round(
    //         (float) ($this->attributes['due_amount'] ?? 0),
    //         2
    //     );
    // }

    public function paymentLogs()
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function getPaidFromPackageAttribute(): float
    {
        // استرجاع المبلغ المدفوع من الـ package من الـ transactions إن أردت
        return round(
            $this->walletTransactions()
                ->where('source', 'package')
                ->sum('amount'),
            2
        );
    }

    public function getPaidFromWalletAttribute(): float
    {
        return round(
            $this->walletTransactions()
                ->where('source', 'wallet')
                ->sum('amount'),
            2
        );
    }

    public function getPaidFromCardAttribute(): float
    {
        // Get the latest payment log (optional)
        $paymentLog = $this->paymentLogs()->latest()->first();

        if (! $paymentLog || empty($paymentLog->response)) {
            return 0.0;
        }

        $response = $paymentLog->response;

        // Check if invoice status is Paid
        if (Arr::get($response, 'InvoiceStatus') !== 'Paid') {
            return 0.0;
        }

        // Get all transactions
        $transactions = Arr::get($response, 'InvoiceTransactions', []);

        // Filter only successful card-based payments
        $cardTransaction = collect($transactions)
            ->first(function ($transaction) {
                return isset($transaction['TransactionStatus']) &&
                    strcasecmp($transaction['TransactionStatus'], 'Succss') === 0 &&
                    isset($transaction['PaymentGateway']) &&
                    in_array(strtolower($transaction['PaymentGateway']), [
                        'apple pay', 'credit card', 'mada', 'visa', 'mastercard'
                    ]);
            });

        return $cardTransaction
            ? floatval($cardTransaction['TransationValue'] ?? 0)
            : 0.0;
    }

    public function getPaymentMethodAttribute(): ?string
    {
        // Get latest payment log (or customize if needed)
        $paymentLog = $this->paymentLogs()->latest()->first();

        if (! $paymentLog || empty($paymentLog->response)) {
            return null;
        }

        $response = $paymentLog->response;
        $transactions = Arr::get($response, 'InvoiceTransactions', []);

        // Find the first successful transaction
        $transaction = collect($transactions)
            ->first(function ($t) {
                return isset($t['TransactionStatus']) &&
                    strcasecmp($t['TransactionStatus'], 'Succss') === 0;
            });
        return $transaction['PaymentGateway'] ?? null;
    }


    /**
     * إرجاع الوقت التقريبي للتسليم بصيغة نصّية.
     */
    // public function getDeliveryEtaAttribute(): string
    // {
    //     // 1) احصلي على التاريخ فقط (بدون الجزء الزمني)
    //     $date = $this->dropoff_date instanceof Carbon
    //         ? $this->dropoff_date->toDateString()  // "2025-07-11"
    //         : Carbon::parse($this->dropoff_date)->toDateString();

    //     // 2) أصلحي حالات "HH:MM HH:MM" بدون "-":
    //     $time = trim($this->dropoff_time);
    //     if (preg_match('/^\d{1,2}:\d{2}\s+\d{1,2}:\d{2}$/', $time)) {
    //         $time = preg_replace(
    //             '/^(\d{1,2}:\d{2})\s+(\d{1,2}:\d{2})$/',
    //             '$1 - $2',
    //             $time
    //         );
    //         Log::warning("Delivery ETA fixed missing dash: {$time}");
    //     }

    //     // 3) استخرجي أول "HH:MM"
    //     if (preg_match('/\d{1,2}:\d{2}/', $time, $m)) {
    //         $startTime = $m[0];
    //     } else {
    //         $startTime = $time;
    //     }

    //     // 4) سجّلي السلسلة الخام للّوق باستخدام $date فقط:
    //     $raw = "{$date} {$time}";
    //     Log::info("Delivery ETA raw: {$raw}");

    //     // 5) وبنفس التاريخ أبنِ السلسلة للـ parse:
    //     $toParse = "{$date} {$startTime}";
    //     Log::info("Delivery ETA to parse: {$toParse}");

    //     // 6) الآن Carbon.parse لن تتعرّض لـ "وقت مزدوج"
    //     try {
    //         $dropoffAt = Carbon::createFromFormat('Y-m-d H:i', $toParse);
    //     } catch (\Throwable $e) {
    //         $dropoffAt = Carbon::parse($toParse);
    //     }

    //     $now = Carbon::now();

    //     if ($now->greaterThanOrEqualTo($dropoffAt)) {
    //         return __('تم التسليم أو قيد التسليم');
    //     }

    //     $hours = $now->diffInHours($dropoffAt);
    //     if ($hours < 24) {
    //         return "ساعَة {$hours} سوف يتم التسليم بعد";
    //     }

    //     $days = $now->diffInDays($dropoffAt);
    //     return "يوم {$days} سوف يتم التسليم بعد";
    // }

    public function driverRequests()
    {
        return $this->hasMany(DriverRequest::class);
    }

    // NEW: Pickup driver relationship
    public function pickupDriver()
    {
        return $this->belongsTo(Driver::class, 'pickup_driver_id');
    }

    // NEW: Delivery driver relationship
    public function deliveryDriver()
    {
        return $this->belongsTo(Driver::class, 'delivery_driver_id');
    }

    // NEW: Pickup driver requests
    public function pickupRequests()
    {
        return $this->hasMany(DriverRequest::class)->where('request_type', 'PICKUP');
    }

    // NEW: Delivery driver requests
    public function deliveryRequests()
    {
        return $this->hasMany(DriverRequest::class)->where('request_type', 'DELIVERY');
    }

    /**
     * Accessors
     */

    // Check if order has pickup driver assigned
    public function hasPickupDriver()
    {
        return !is_null($this->pickup_driver_id);
    }

    // Check if order has delivery driver assigned
    public function hasDeliveryDriver()
    {
        return !is_null($this->delivery_driver_id);
    }

    // Check if order has both drivers assigned
    public function hasBothDrivers()
    {
        return $this->hasPickupDriver() && $this->hasDeliveryDriver();
    }

    // Get current phase of the order
    public function getCurrentPhase()
    {
        $pickupPhases = ['PLACED', 'PICKED_UP', 'ON_THE_WAY_FOR_PICKUP', 'ON_THE_WAY_TO_PARTNER', 'ARRIVED'];
        $processingPhases = ['PROCESSING', 'CONFIRMED_PAID'];
        $deliveryPhases = ['READY_TO_DELIVER', 'PICKED_FOR_DELIVER'];

        if (in_array($this->status, $pickupPhases)) {
            return 'PICKUP_PHASE';
        } elseif (in_array($this->status, $processingPhases)) {
            return 'PROCESSING_PHASE';
        } elseif (in_array($this->status, $deliveryPhases)) {
            return 'DELIVERY_PHASE';
        } elseif ($this->status === 'DELIVERED') {
            return 'COMPLETED';
        } elseif ($this->status === 'CANCELLED') {
            return 'CANCELLED';
        }

        return 'UNKNOWN';
    }

    // Check if order is in pickup phase
    public function isInPickupPhase()
    {
        return $this->getCurrentPhase() === 'PICKUP_PHASE';
    }

    // Check if order is in delivery phase
    public function isInDeliveryPhase()
    {
        return $this->getCurrentPhase() === 'DELIVERY_PHASE';
    }

    // Check if order is ready for delivery phase
    public function isReadyForDelivery()
    {
        return $this->status === 'READY_TO_DELIVER';
    }

    /**
     * Scopes
     */

    // Orders needing pickup driver
    public function scopeNeedingPickupDriver($query)
    {
        return $query->whereNull('pickup_driver_id')
            ->whereIn('status', ['PLACED']);
    }

    // Orders needing delivery driver
    public function scopeNeedingDeliveryDriver($query)
    {
        return $query->whereNull('delivery_driver_id')
            ->where('status', 'READY_TO_DELIVER');
    }

    // Orders with specific driver (pickup or delivery)
    public function scopeWithDriver($query, $driverId)
    {
        return $query->where(function($q) use ($driverId) {
            $q->where('pickup_driver_id', $driverId)
                ->orWhere('delivery_driver_id', $driverId);
        });
    }
}

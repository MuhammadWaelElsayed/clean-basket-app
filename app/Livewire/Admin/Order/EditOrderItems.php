<?php

namespace App\Livewire\Admin\Order;

use App\Models\Order;
use App\Models\Item;
use App\Models\ServiceType;
use App\Models\AddOn;
use App\Models\OrderPriority;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EditOrderItems extends Component
{
    public Order $order;

    public $orderItems = [];
    public $items = [];
    public $serviceTypes = [];
    public $addOns = [];
    public $orderPriorities = [];
    public $selectedPriority = null;

    public $editingItemIndex = null;   // null = لا يوجد تعديل
    public $showAddItemForm = false;   // true = وضع إضافة

    public $newItem = [
        'item_id' => '',
        'service_type_id' => '',
        'quantity' => 1,
        'add_on_ids' => [],
    ];

    public function mount(Order $order)
    {
        $this->order = $order;

        // // التحقق من أن الفرز من جانب العميل فقط
        // if ($order->sorting !== 'client') {
        //     abort(403, 'تعديل العناصر متاح فقط للطلبات المرتبة من جانب العميل');
        // }

        $this->loadOrderItems();
        $this->loadData();
    }

    public function loadOrderItems()
    {
        $this->orderItems = $this->order
            ->orderItems()
            ->with(['item', 'serviceType', 'addOns'])
            ->get()
            ->toArray();
    }

    public function loadData()
    {
        $this->items = Item::with('serviceTypes')->where('id', '>=', 1000)->get();
        $this->serviceTypes = ServiceType::all();
        $this->addOns = AddOn::all();
        $this->orderPriorities = OrderPriority::all();
        $this->selectedPriority = $this->order->order_priority_id ?? $this->orderPriorities->first()?->id;
    }

    public function addNewItem()
    {
        // if ($this->order->sorting !== 'client') {
        //     $this->dispatch('error', 'تعديل العناصر متاح فقط للطلبات المرتبة من جانب العميل');
        //     return;
        // }

        $this->showAddItemForm = true;
        $this->editingItemIndex = null;
        $this->newItem = [
            'item_id' => '',
            'service_type_id' => '',
            'quantity' => 1,
            'add_on_ids' => [],
        ];

        // للتشخيص (اختياري)
        Log::info('addNewItem called', [
            'showAddItemForm' => $this->showAddItemForm,
            'editingItemIndex' => $this->editingItemIndex,
        ]);
        $this->dispatch('lw:addItemOpened');
    }

    public function editItem($index)
    {
        // if ($this->order->sorting !== 'client') {
        //     $this->dispatch('error', 'تعديل العناصر متاح فقط للطلبات المرتبة من جانب العميل');
        //     return;
        // }

        $this->editingItemIndex = $index;
        $this->showAddItemForm = false;

        $item = $this->orderItems[$index];

        $this->newItem = [
            'item_id' => $item['item_id'],
            'service_type_id' => $item['service_type_id'],
            'quantity' => $item['quantity'],
            'add_on_ids' => collect($item['add_ons'] ?? [])->pluck('id')->toArray(),
        ];

        Log::info('editItem called', [
            'index' => $index,
            'showAddItemForm' => $this->showAddItemForm,
            'editingItemIndex' => $this->editingItemIndex,
            'newItem' => $this->newItem,
        ]);
        $this->dispatch('lw:editItemOpened');
    }

    public function cancelEdit()
    {
        $this->editingItemIndex = null;
        $this->showAddItemForm = false;
        $this->newItem = [
            'item_id' => '',
            'service_type_id' => '',
            'quantity' => 1,
            'add_on_ids' => [],
        ];
    }

    public function removeItem($index)
    {
        // if ($this->order->sorting !== 'client') {
        //     $this->dispatch('error', 'تعديل العناصر متاح فقط للطلبات المرتبة من جانب العميل');
        //     return;
        // }

        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
    }

    public function saveItem()
    {
        // if ($this->order->sorting !== 'client') {
        //     $this->dispatch('error', 'تعديل العناصر متاح فقط للطلبات المرتبة من جانب العميل');
        //     return;
        // }

        $this->validate([
            'newItem.item_id' => 'required|exists:items,id',
            'newItem.service_type_id' => 'required|exists:service_types,id',
            'newItem.quantity' => 'required|integer|min:1',
            'newItem.add_on_ids' => 'array',
            'newItem.add_on_ids.*' => 'exists:add_ons,id',
            'selectedPriority' => 'required|exists:order_priorities,id',
        ]);

        $pivot = DB::table('item_service_type')
            ->where('item_id', $this->newItem['item_id'])
            ->where('service_type_id', $this->newItem['service_type_id'])
            ->where('order_priority_id', $this->selectedPriority)
            ->first(['price', 'discount_price']);

        if (!$pivot) {
            $this->dispatch('error', 'لا يوجد سعر محدد لهذا الصنف مع نوع الخدمة المختار');
            return;
        }

        $unitPrice = $pivot->discount_price !== null
            ? (float) $pivot->discount_price
            : (float) $pivot->price;

        $quantity = (int) $this->newItem['quantity'];
        $baseTotal = $unitPrice * $quantity;

        $addonsTotal = 0;
        if (!empty($this->newItem['add_on_ids'])) {
            $addOns = AddOn::whereIn('id', $this->newItem['add_on_ids'])->get();
            foreach ($addOns as $addOn) {
                $addonsTotal += (float) $addOn->price;
            }
            $addonsTotal *= $quantity;
        }

        $totalPrice = $baseTotal + $addonsTotal;

        if ($this->editingItemIndex !== null) {
            // تعديل عنصر موجود
            $this->orderItems[$this->editingItemIndex] = [
                'id' => $this->orderItems[$this->editingItemIndex]['id'] ?? null,
                'item_id' => $this->newItem['item_id'],
                'service_type_id' => $this->newItem['service_type_id'],
                'price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'item' => Item::find($this->newItem['item_id']),
                'service_type' => ServiceType::find($this->newItem['service_type_id']),
                'add_ons' => AddOn::whereIn('id', $this->newItem['add_on_ids'])->get()->toArray(),
            ];
        } else {
            // إضافة عنصر جديد
            $this->orderItems[] = [
                'id' => null,
                'item_id' => $this->newItem['item_id'],
                'service_type_id' => $this->newItem['service_type_id'],
                'price' => $unitPrice,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'item' => Item::find($this->newItem['item_id']),
                'service_type' => ServiceType::find($this->newItem['service_type_id']),
                'add_ons' => AddOn::whereIn('id', $this->newItem['add_on_ids'])->get()->toArray(),
            ];
        }

        $this->cancelEdit();
    }

    public function saveOrderItems()
    {
        // if ($this->order->sorting !== 'client') {
        //     $this->dispatch('error', 'تعديل العناصر متاح فقط للطلبات المرتبة من جانب العميل');
        //     return;
        // }

        try {
            DB::beginTransaction();

            // حفظ المبلغ الإجمالي القديم قبل التعديل
            $originalGrandTotal = $this->order->grand_total;
            $originalDueAmount = $this->order->due_amount;
            $originalPayStatus = $this->order->pay_status;

            // حذف العناصر القديمة
            $this->order->orderItems()->delete();

            // إضافة العناصر الجديدة
            foreach ($this->orderItems as $itemData) {
                $orderItem = $this->order->orderItems()->create([
                    'item_id' => $itemData['item_id'],
                    'service_type_id' => $itemData['service_type_id'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'total_price' => $itemData['total_price'],
                ]);

                // إضافة الإضافات
                if (!empty($itemData['add_ons'])) {
                    foreach ($itemData['add_ons'] as $addOn) {
                        $orderItem->addOns()->attach($addOn['id'], [
                            'price' => $addOn['price'],
                        ]);
                    }
                }
            }

            // إعادة حساب الإجماليات
            $subTotal = $this->order->orderItems()->sum('total_price');
            $vatAmount = round($subTotal * env('TAX_RATE', 0), 2);
            $grandTotal = round($subTotal + $vatAmount + $this->order->delivery_fee, 2);

            // حساب المبلغ الإضافي
            $additionalAmount = $grandTotal - $originalGrandTotal;

            // إضافة المبلغ الإضافي إلى due_amount
            $newDueAmount = $originalDueAmount + $additionalAmount;

            // التأكد من أن due_amount لا يكون سالب
            $newDueAmount = max(0, $newDueAmount);

            // تحديث حالة الطلب من Paid إلى Partial إذا كان هناك مبلغ إضافي
            $payStatus = $originalPayStatus;
            if ($additionalAmount > 0) {
                if ($payStatus === 'Paid') {
                    $payStatus = 'Partial';
                }
            }

            // تسجيل التغييرات
            Log::info('Order items updated with due_amount calculation', [
                'order_id' => $this->order->id,
                'original_grand_total' => $originalGrandTotal,
                'new_grand_total' => $grandTotal,
                'additional_amount' => $additionalAmount,
                'original_due_amount' => $originalDueAmount,
                'new_due_amount' => $newDueAmount,
                'original_pay_status' => $originalPayStatus,
                'new_pay_status' => $payStatus,
                'pay_status_changed' => $payStatus !== $originalPayStatus
            ]);

            $this->order->update([
                'sub_total' => $subTotal,
                'vat' => $vatAmount,
                'grand_total' => $grandTotal,
                'due_amount' => $newDueAmount,
                'pay_status' => $payStatus,
            ]);

            DB::commit();

            $this->loadOrderItems();

            // رسالة إضافية عند تغيير حالة الدفع
            if ($additionalAmount > 0) {
                $this->dispatch('success', 'Order items updated successfully. Additional amount of ' . env('CURRENCY') . ' ' . number_format($additionalAmount, 2) . ' added to due amount.');
            } elseif ($additionalAmount < 0) {
                $this->dispatch('success', 'Order items updated successfully. Amount of ' . env('CURRENCY') . ' ' . number_format(abs($additionalAmount), 2) . ' deducted from due amount.');
            } else {
                $this->dispatch('success', 'Order items updated successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order items: ' . $e->getMessage());
            $this->dispatch('error', 'حدث خطأ أثناء تحديث عناصر الطلب');
        }
    }

    // خصائص محسوبة للملخص
    public function getSubTotalProperty()
    {
        return collect($this->orderItems)->sum('total_price');
    }

    public function getVatAmountProperty()
    {
        return round($this->subTotal * env('TAX_RATE', 0), 2);
    }

    public function getGrandTotalProperty()
    {
        return round($this->subTotal + $this->vatAmount + $this->order->delivery_fee, 2);
    }

    public function render()
    {
        Log::info('EditOrderItems render', [
            'showAddItemForm' => $this->showAddItemForm,
            'editingItemIndex' => $this->editingItemIndex,
            'orderItemsCount' => count($this->orderItems),
        ]);

        return view('livewire.admin.orders.edit-order-items')
            ->layout('components.layouts.admin-dashboard');
    }
}

@section('title', 'Order Slip')

@php
    if (isset($lang) && $lang == 'ar') {
        $translations = [
            'back' => 'رجوع',
            'print_slip' => 'طباعة الإيصال',
            'order' => 'طلب',
            'pickup' => 'استلام',
            'delivery' => 'توصيل',
            'customer' => 'العميل',
            'appartment' => 'شقة',
            'building' => 'مبنى',
            'area' => 'منطقة',
            'item' => 'اسم المنتج',
            'service' => 'الخدمة',
            'price' => 'السعر',
            'quantity' => 'الكمية',
            'total' => 'المجموع',
            'sub_total' => 'الإجمالي الفرعي',
            'service_fee' => 'رسوم الخدمة',
            'delivery_fee' => 'رسوم التوصيل',
            "promo_discount" => "خصم ترويجي",
            'vat' => 'ضريبة القيمة المضافة',
            'grand_total' => 'الإجمالي الكلي',
            'partial_discount' => 'خصم جزئي',
        ];
    } else {
        $translations = [
            'back' => 'Back',
            'print_slip' => 'Print Slip',
            'order' => 'Order',
            'pickup' => 'Pickup',
            'delivery' => 'Delivery',
            'customer' => 'Customer',
            'appartment' => 'Appartment',
            'building' => 'Building',
            'area' => 'Area',
            'item' => 'Item Name',
            'service' => 'Service',
            'price' => 'Price',
            'quantity' => 'Quantity',
            'total' => 'Total',
            'sub_total' => 'Sub Total',
            'service_fee' => 'Service Fee',
            'delivery_fee' => 'Delivery Fee',
            "promo_discount" => "Promo Discount",
            'vat' => 'VAT',
            'partial_discount' => 'Partial Discount',
            'grand_total' => 'Grand Total',
            'due_amount' => 'Due Amount',
        ];
    }
@endphp

<div class="app-main flex-column flex-row-fluid" id="kt_app_main" data-select2-id="select2-data-kt_app_main"
    style="direction:{{ $lang == 'ar' ? 'rtl' : 'ltr' }}">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid" data-select2-id="select2-data-129-xgx3">

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <!--begin::Content container-->
            <a class="btn btn-base m-2 hide-print" href="{{ url('admin/orders') }}" wire:navigate>
                < {{ $translations['back'] }}</a>
                    <button class="btn btn-base m-2 hide-print" onclick="printSlip()">
                        {{ $translations['print_slip'] }}</button>
                    @if ($lang == 'ar')
                        <a class="btn btn-light-primary m-2 hide-print"
                            href="{{ url('admin/order-details/' . $order->id . '?print=1') }}" wire:navigate>English</a>
                    @else
                        <a class="btn btn-light-primary m-2 hide-print"
                            href="{{ url('admin/order-details/' . $order->id . '?print=1&language=ar') }}"
                            wire:navigate>Arabic</a>
                    @endif

                    <div id="kt_app_content_container" class="app-container container-xxl"
                        data-select2-id="select2-data-kt_app_content_container">
                        <!--begin::Card-->
                        <div class="card border border-dark">
                            <!--end::Card header-->
                            <div class="text-center mt-3">
                                <img src="{{ asset('media/logo.png') }}" class="w-200px " alt="logo">
                            </div>

                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-8">
                                        <p class="fs-4"><b>{{ $translations['order'] }}# </b> {{ $order->order_code }}
                                        </p>
                                        <p class="fs-4"><b>{{ $translations['pickup'] }}:
                                            </b>{{ $order->pickup_date }} ({{ $order->pickup_time }})</p>
                                        <p class="fs-4"><b>{{ $translations['delivery'] }}:
                                            </b>{{ $order->dropoff_date }} ({{ $order->dropoff_time }})</p>

                                        <span
                                            class="badge badge-{{ $statuses[$order->status] }} fs-4">{{ $order->status }}</span>

                                    </div>
                                    <div class="col-4">
                                        <p class="fs-4"><b>{{ $translations['customer'] }}: </b>
                                            {{ $order->user->first_name ?? '--' }}</p>
                                        <p class="fs-4"><b>{{ $translations['appartment'] }}:</b>
                                            {{ $order->deliveryAddress->appartment ?? '--' }}</p>
                                        <p class="fs-4"><b>{{ $translations['building'] }}:</b>
                                            {{ $order->deliveryAddress->building ?? '--' }}</p>
                                        <p class="fs-4"><b>{{ $translations['area'] }}:</b>
                                            {{ $order->deliveryAddress->area ?? '--' }}</p>
                                    </div>

                                    <div class="col-12 mt-5">
                                        <table class="table table-striped fs-5  ">
                                            <tr>
                                                <th><b>{{ $translations['item'] }}</b></th>
                                                <th><b>{{ $translations['service'] }} </b></th>
                                                <th><b> {{ $translations['price'] }}</b></th>
                                                <th><b> {{ $translations['quantity'] }}</b></th>
                                                <th><b> {{ $translations['total'] }}</b></th>
                                            </tr>
                                            @foreach ($order->orderItems as $item)
                                                <tr>
                                                    <td>
                                                        {{-- <img src="{{$item->item->image}}" class="w-50px" alt="item"> --}}
                                                        {{ $lang == 'ar' ? $item->item->name_ar : $item->item->name }}</td>
                                                    <td>{{ $lang == 'ar' ? $item->item->service->name_ar : $item->item->service->name }}
                                                    </td>
                                                    <td>{{ env('CURRENCY') }} {{ $item->price }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ env('CURRENCY') }}
                                                        {{ number_format($item->total_price, 2) }}</td>

                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="2"></td>
                                                <th class="fw-bold " colspan="2">{{ $translations['sub_total'] }}
                                                </th>
                                                <td class="fw-bold"> {{ env('CURRENCY') }}
                                                    {{ number_format($order->sub_total, 2) }} </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"></td>
                                                <th class="" colspan="2">{{ $translations['service_fee'] }}
                                                </th>
                                                <td class=""> {{ env('CURRENCY') }}
                                                    {{ number_format($order->service_fee, 2) }} </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"></td>
                                                <th class="" colspan="2">{{ $translations['delivery_fee'] }}
                                                </th>
                                                <td class=""> {{ env('CURRENCY') }}
                                                    {{ number_format($order->delivery_fee, 2) }} </td>
                                            </tr>

                                            {{-- إذا كان هناك خصم ترويجي --}}
                                            @if (!empty($order->promo_discount) && $order->promo_discount > 0)
                                                <tr>
                                                    <td colspan="2"></td>
                                                    <th colspan="2">{{ $translations['promo_discount'] }}</th>
                                                    <td>
                                                        - {{ env('CURRENCY') }}
                                                        {{ number_format($order->promo_discount, 2) }}
                                                    </td>
                                                </tr>
                                            @endif

                                            <tr>
                                                <td colspan="2"></td>
                                                <th class=" " colspan="2">{{ $translations['vat'] }}</th>
                                                <td class=""> {{ env('CURRENCY') }}
                                                    {{ number_format($order->vat, 2) }} </td>
                                            </tr>

                                            <tr>
                                                <td colspan="2"></td>
                                                <th class="fw-bold " colspan="2">{{ $translations['grand_total'] }}
                                                </th>
                                                <td class="fw-bold"> {{ env('CURRENCY') }}
                                                    {{ number_format($order->grand_total, 2) }} </td>
                                            </tr>
                                            @if ($order->pay_status == 'Partial')
                                            <tr>
                                                <td colspan="2"></td>
                                                <th  class="fw-bold " colspan="2">
                                                   {{ $translations['partial_discount'] }}
                                                </th>
                                                <td class="fw-bold">
                                                    {{ env('CURRENCY') }}
                                                    {{ number_format($order->grand_total - $order->due_amount, 2) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2"></td>
                                                <th class="fw-bold " colspan="2">{{ $translations['due_amount'] }}
                                                </th>
                                                <td class="fw-bold"> {{ env('CURRENCY') }}
                                                    {{ number_format($order->due_amount, 2) }} </td>
                                            </tr>
                                            @endif

                                        </table>
                                    </div>


                                </div>

                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->


                    </div>
                    <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    <script>
        function printSlip() {
            $('.hide-print').hide();
            window.print();
            $('.hide-print').show();
        }
        // window.print();
    </script>

</div>

<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Slip</title>
    <style>
        .top-table{
             width:100%
        }
        .left{
            width: 70%;
        }
        .right{
            width: 30%;
        }
        .badge{
            background: #5DADA8;
            padding:5px 10px;
            border-radius:5px;
            font-size: 12px;
            color: white
        }
        .table-head{
            background: lightgray;
        }
       
        .table-head >th{
            padding: 20px !important;
        }
        .main-table{
            width: 100%;
        }
    </style>
</head>
<body>
@php
    if(isset($lang) && $lang=='ar'){
        $translations = [
            "back" => "رجوع",
            "print_slip" => "طباعة الإيصال",
            "order" => "طلب",
            "pickup" => "استلام",
            "delivery" => "توصيل",
            "customer" => "العميل",
            "appartment" => "شقة",
            "building" => "مبنى",
            "area" => "منطقة",
            "item" => "اسم المنتج",
            "service" => "الخدمة",
            "price" => "السعر",
            "quantity" => "الكمية",
            "total" => "المجموع",
            "sub_total" => "الإجمالي الفرعي",
            "service_fee" => "رسوم الخدمة",
            "delivery_fee" => "رسوم التوصيل",
            "vat" => "ضريبة القيمة المضافة",
            "grand_total" => "الإجمالي الكلي",
        ];

    }else{
        $translations=[
            "back"=>"Back",
            "print_slip"=>"Print Slip",
            "order"=>"Order",
            "pickup"=>"Pickup",
            "delivery"=>"Delivery",
            "customer"=>"Customer",
            "appartment"=>"Appartment",
            "building"=>"Building",
            "area"=>"Area",
            "item"=>"Item Name",
            "service"=>"Service",
            "price"=>"Price",
            "quantity"=>"Quantity",
            "total"=>"Total",
            "sub_total"=>"Sub Total",
            "service_fee"=>"Service Fee",
            "delivery_fee"=>"Delivery Fee",
            "vat"=>"VAT",
            "grand_total"=>"Grand Total",
        ];
    }
@endphp

<table class="top-table">
    <tr>
        <td  colspan="2" style="width: 100%; text-align:center">
            {{-- <img src="/media/logo.png"  style="width: 200px;" alt="logo" > --}}
            <img src="{{ public_path('media/logo.png') }}"  style="width: 200px;" alt="logo">

        </td>
    </tr>
    <tr>
        <td class="left">
            <p class="fs-4"><b>{{$translations['order']}}# </b> {{$order->order_code}}</p>
            <p class="fs-4"><b>{{$translations['pickup']}}:   </b>{{$order->pickup_date}} ({{$order->pickup_time}})</p>
            <p class="fs-4"><b>{{$translations['delivery']}}:  </b>{{$order->dropoff_date}} ({{$order->dropoff_time}})</p>
            <span class="badge badge-{{$statuses[$order->status]}}">{{$order->status}}</span>
        </td>
        <td class="right">
            <p class="fs-4"><b>{{$translations['customer']}}: </b> {{$order->user->first_name?? '--'}}</p>
            <p class="fs-4"><b>{{$translations['appartment']}}:</b>  {{$order->deliveryAddress->appartment?? '--'}}</p>
            <p class="fs-4"><b>{{$translations['building']}}:</b>  {{$order->deliveryAddress->building?? '--'}}</p>
            <p class="fs-4"><b>{{$translations['area']}}:</b>  {{$order->deliveryAddress->area?? '--'}}</p>
        </td>
    </tr>

</table>
<br>
<table class="main-table">
    <tr class="table-head">
        <th ><b>{{$translations['item']}}</b></th>
        <th><b>{{$translations['service']}} </b></th>
        <th><b> {{$translations['price']}}</b></th>
        <th><b> {{$translations['quantity']}}</b></th>
        <th><b> {{$translations['total']}}</b></th>
    </tr>
    @foreach ($order->orderItems as $item)
    <tr>
        <td>
            {{-- <img src="{{$item->item->image}}" class="w-50px" alt="item"> --}}
        {{($lang=="ar")?$item->item->name_ar:$item->item->name}}</td>
        <td>{{ ($lang=="ar")?$item->item->service->name_ar:$item->item->service->name}}</td>
        <td>{{env('CURRENCY')}} {{$item->price}}</td>
        <td>{{$item->quantity}}</td>
        <td>{{env('CURRENCY')}} {{number_format($item->total_price,2)}}</td>

    </tr>  
    @endforeach
    <tr>
        <td colspan="2"></td>
        <th class="fw-bold " colspan="2">{{$translations['sub_total']}}</th>
        <td class="fw-bold"> {{env("CURRENCY")}} {{number_format($order->sub_total,2)}} </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <th class="" colspan="2">{{$translations['service_fee']}}</th>
        <td class=""> {{env("CURRENCY")}} {{number_format($order->service_fee,2)}} </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <th class="" colspan="2">{{$translations['delivery_fee']}}</th>
        <td class=""> {{env("CURRENCY")}} {{number_format($order->delivery_fee,2)}} </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <th class=" " colspan="2">{{$translations['vat']}}</th>
        <td class=""> {{env("CURRENCY")}} {{number_format($order->vat,2)}} </td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <th class="fw-bold " colspan="2">{{$translations['grand_total']}}</th>
        <td class="fw-bold"> {{env("CURRENCY")}} {{number_format($order->grand_total,2)}} </td>
    </tr>
    
 
</table>


</body>
</html>

@section('ordersActive','active')


<div class="app-main flex-column flex-row-fluid" id="kt_app_main" data-select2-id="select2-data-kt_app_main">
    <style>
        th{
            font-weight: 600 !important;
        }
    </style>
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid" data-select2-id="select2-data-129-xgx3">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Order Details</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted"> 
                            <a href="{{ url('partner/dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">Orders</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
              
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->
                <div class="card"> 
                    <!--end::Card header-->
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                          <button class="nav-link active fs-lg-5" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false"><i class="fas fa-file-alt"></i> Order Details</button>
                        </div>
                    </nav>
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <div class="tab-content mt-5" id="nav-tabContent">
                            {{-- Tab 1- Order --}}
                            <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                <div class="row p-5">
                                   
                                    <div class="col-md-9">
                                        <table class="table table-striped fs-6">
                                            <tr>
                                                <th>Order#</th>
                                                <td>{{$order->order_code}}</td>
                                            </tr>
                                            <tr>
                                                <th>Pickup Date</th>
                                                <td>{{$order->pickup_date}}</td>
                                            </tr>
                                            <tr>
                                                <th>Pickup Time</th>
                                                <td> {{$order->pickup_time}} </td>
                                            </tr>
                                        
                                            <tr>
                                                <th> Instructions</th>
                                                <td> {{$order->instructions}} </td>
                                            </tr>
                                            <tr>
                                                <th class="fw-bold">Grand Total</th>
                                                <td class="fw-bold"> {{env("CURRENCY")}} {{number_format($order->grand_total)}} </td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <table class="table table-striped fs-lg-6 w-75">
                                            <tr>
                                                {{-- <th><b> Image</b></th> --}}
                                                <th><b>Item Name</b></th>
                                                <th><b> Price</b></th>
                                                <th><b> Quantity</b></th>
                                                <th><b> SubTotal</b></th>
                                            </tr>
                                            @foreach ($order->orderItems as $item)
                                            <tr>
                                                {{-- <td><img src="{{$item->image}}" class="w-50px" alt=""> </td> --}}
                                                <td> <img src="{{$item->item->image}}" class="w-50px h-50px" style="object-fit: cover" alt="item">
                                                     {{$item->item->name}}</td>
                                                <td>{{env('CURRENCY')}} {{$item->price}}</td>
                                                <td>{{$item->quantity}}</td>
                                                <td>{{env('CURRENCY')}} {{$item->total_price}}</td>
    
                                            </tr>  
                                            @endforeach
                                            <tr>
                                                <th class="fw-bold" colspan="3">Grand Total</th>
                                                <td class="fw-bold"> {{env("CURRENCY")}} {{number_format($order->grand_total)}} </td>
                                            </tr>
                                            
                                        </table>
  
                                    </div>
                                    <div class="col-md-3">
                                        <span class="badge badge-{{$statuses[$order->status]}} fs-4">{{$order->status}}</span>
                                        <br><br>
                                        @if ($order->status=="CONFIRMED_PAID")
                                            <form enctype="multipart/form-data" method="post" wire:submit.prevent="updateBill()">

                                                <h5 class="my-2">Receipt/Bill Picture </h5>
                                                @if ($order->bill)
                                                    <a href="{{ url($order->bill) }}" target="_blank"> 
                                                        <img src="{{$order->bill}}" alt="bill" class="w-80px">
                                                    </a>

                                                @else
                                                    <input type="file" class="form-control" accept="image/*"  wire:model="bill" >
                                                    @error('bill')
                                                        <p class="text-danger">{{$message}}</p>
                                                    @enderror
                                                    <button class="btn btn-base my-5" type="submit">Submit</button>
                                                @endif

                                            </form>
                                        @endif
                                        

                                    </div>
                                </div>
                                
                                  

                            </div>
                         
                             
                           
                          </div>

                            <!--end::Form-->
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

</div>


@section('scripts')
<script>
function myMap() {
    const lat= {{$order->deliveryAddress->lat}};
    const lng= {{$order->deliveryAddress->lng}};
    // const lat= 23.454552;
    // const lng= 12.4546256;

    var mapProp= {
      center:new google.maps.LatLng( lat,lng),
      zoom:5,
    };
    var map = new google.maps.Map(document.getElementById("googleMap"),mapProp);
    var marker = new google.maps.Marker({position: {lat: lat, lng: lng} });
    marker.setMap(map);
}
function getModal(id,status) {
        $('#order_id').val(id);
        $('#status').val(status);
}
</script>
    
<script src="https://maps.googleapis.com/maps/api/js?key={{env("GOOGLE_MAP_KEY")}}&callback=myMap"></script>

    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/custom/widgets.js') }}"></script>
@endsection
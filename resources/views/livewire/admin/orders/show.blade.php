
@section('ordersActive','active')


<div class="app-main flex-column flex-row-fluid" id="kt_app_main" data-select2-id="select2-data-kt_app_main">
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
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Home</a>
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
                <div class="back-home-btn">
                    {{-- @if($order->sorting === 'client') --}}
                        <a href="{{ route('admin.order.edit-items', $order->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit"></i> Edit items sorting
                        </a>
                    {{-- @endif --}}
                    <a href="{{ url($order->type == 'b2b' ? 'admin/b2b-orders' : 'admin/orders') }}" class="btn btn-primary">Back to Orders</a>
                </div>
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
                          <button class="nav-link fs-lg-5" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-rest" type="button" role="tab" aria-controls="nav-rest" aria-selected="false"><i class="fas fa-boxes"></i> Order Items </button>
                          <button class="nav-link fs-lg-5" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false"><i class="fas fa-user"></i> Customer</button>
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
                                                <th>Type</th>
                                                <td>{{$order->type}}</td>
                                            </tr>
                                            <tr>
                                                <th>Order#</th>
                                                <td>{{$order->order_code}}</td>
                                            </tr>
                                            <tr>
                                                <th>Pickup Time</th>
                                                <td>{{$order_pickup ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th>Delivery DateTime</th>
                                                <td> {{$order_delivery ?? 'N/A'}} </td>
                                            </tr>
                                            <tr>
                                                <th> Instructions</th>
                                                <td> {{$order->instructions}} </td>
                                            </tr>
                                            <tr>
                                                <th>Paid from wallet</th>
                                                <td> {{$order->paid_from_wallet}} </td>
                                            </tr>
                                            <tr>
                                                <th>Paid from package</th>
                                                <td> {{$order->paid_from_package}} </td>
                                            </tr>
                                            <tr>
                                                <th>Paid from card</th>
                                                <td> {{$order->paid_from_card}} </td>
                                            </tr>
                                            <tr>
                                                <th>Service fee</th>
                                                <td> {{$order->service_fee}} </td>
                                            </tr>
                                            <tr>
                                                <th>Commission amount</th>
                                                <td> {{$order->commission_amount}} </td>
                                            </tr>
                                            <tr>
                                                <th>Promo code</th>
                                                <td> {{$order->promo_code}} </td>
                                            </tr>

                                            <tr>
                                                <th> Items Total</th>
                                                <td> {{env("CURRENCY")}} {{number_format($order->sub_total,2)}} </td>
                                            </tr>
                                            <tr>
                                                <th> VAT</th>
                                                <td> {{env("CURRENCY")}} {{number_format($order->vat,2)}} </td>
                                            </tr>
                                            <tr>
                                                <th> Delivery Fee</th>
                                                <td> {{env("CURRENCY")}} {{number_format($order->delivery_fee,2)}} </td>
                                            </tr>

                                            <tr>
                                                <th class="fw-bold">Grand Total</th>
                                                <td class="fw-bold"> {{env("CURRENCY")}} {{number_format($order->grand_total,2)}} </td>
                                            </tr>

                                            @if ($order->pay_status == 'Partial')
                                            <tr>
                                                <th>Partial Discount</th>
                                                <td> {{env("CURRENCY")}} {{number_format($order->grand_total - $order->due_amount,2)}} </td>
                                            </tr>

                                            <tr>
                                                <th>Due Amount</th>
                                                <td> {{env("CURRENCY")}} {{number_format($order->due_amount,2)}} </td>
                                            </tr>
                                            @endif

                                            @if ($order->order_image!=null)
                                            <tr>
                                                <th> Ready to Deliver Image</th>
                                                <td>
                                                    <a href="{{ asset('uploads/'.$order->order_image) }}" target="_blank">
                                                        <img src="{{ asset('uploads/'.$order->order_image) }}" class="w-100px rounded" alt="deliver-img" >
                                                    </a>
                                                </td>
                                            </tr>
                                            @endif
                                            @if ($order->deliver_image!=null)
                                            <tr>
                                                <th> Delivered Image</th>
                                                <td>
                                                    <a href="{{ asset('uploads/'.$order->deliver_image) }}" target="_blank">
                                                        <img src="{{ asset('uploads/'.$order->deliver_image) }}" class="w-100px rounded" alt="deliver-img" >
                                                    </a>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                        <hr>

                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-transparent" onclick="getModal('{{$order->id}}','{{$order->status}}')"   data-bs-toggle="modal" data-bs-target="#kt_modal_new_target">
                                            <span class="badge badge-{{$statuses[$order->status]}} fs-4">{{$order->status}}</span>
                                        </button>
                                    </div>
                                </div>

                                    <div class="row">
                                        <div class="col-md-6 ps-5">
                                            {{-- <h6>Delivery Notes:</h6>
                                            <p>{{$order->Instructions?:'N/A'}}</p> --}}
                                              <h6>Delivery Details:</h6>
                                            <table class="table table-striped fs-6">

                                                <tr>
                                                    <th>Building </th>
                                                    <td>{{$order->deliveryAddress?->building??'--'}} </td>
                                                </tr>
                                                <tr>
                                                    <th>Appartment </th>
                                                    <td>{{$order->deliveryAddress?->appartment??'--'}} </td>
                                                </tr>
                                                <tr>
                                                    <th>Floor</th>
                                                    <td> {{$order->deliveryAddress?->floor ?? '--'}} </td>
                                                </tr>
                                                <tr>
                                                    <th>Area</th>
                                                    <td> {{$order->deliveryAddress?->area?? '--'}} </td>
                                                </tr>
                                                <tr>
                                                    <th></th>
                                                    <td><a href="https://maps.google.com/?q={{$order->deliveryAddress?->lat}},{{$order->deliveryAddress?->lng}}" target="_blank"
                                                         class="btn btn-sm btn-base">Get Directions</a></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">

                                            <div id="googleMap" style="width:100%;height:350px;"></div>
                                        </div>
                                    </div>

                            </div>
                            {{-- Tab 2- customer --}}
                            <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                                <div class="row">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>User Image</th>
                                            @if($order->type == 'b2b')
                                                <td><img src="{{$order->client->picture}}" class="w-50px rounded-circle" alt="" srcset=""></td>
                                            @else
                                                <td><img src="{{$order->user->picture}}" class="w-50px rounded-circle" alt="" srcset=""></td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <th>Name</th>
                                            @if($order->type == 'b2b')
                                                <td> {{$order->client->contact_person}}</td>
                                            @else
                                                <td> {{$order->user->first_name}}</td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            @if($order->type == 'b2b')
                                                <td> {{$order->client->email}}</td>
                                            @else
                                                <td> {{$order->user->email}}</td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <th>Phone</th>
                                            @if($order->type == 'b2b')
                                                <td> {{$order->client->phone}}</td>
                                            @else
                                                <td> {{$order->user->phone}}</td>
                                            @endif
                                        </tr>
                                        <tr>
                                            <th>Basket No.</th>
                                            <td> {{$order->deliveryAddress?->basket_no}}</td>
                                        </tr>
                                    </table>

                                </div>
                            </div>
                              {{-- Tab 3- Order items --}}
                            <div class="tab-pane fade" id="nav-rest" role="tabpanel" aria-labelledby="nav-rest-tab">
                                <div class="row">
                                    <table class="table table-striped fs-lg-6 w-75">
                                        <tr>
                                            {{-- <th><b> Image</b></th> --}}
                                            <th><b>Item Name</b></th>
                                            <th><b>Service </b></th>
                                            <th><b> Price</b></th>
                                            <th><b> Quantity</b></th>
                                            <th><b> SubTotal</b></th>
                                        </tr>
                                        @foreach ($order->orderItems as $item)
                                        <tr>
                                            {{-- <td><img src="{{$item->image}}" class="w-50px" alt=""> </td> --}}
                                            <td><img src="{{$item->item->image}}" class="w-50px" alt="item">  {{$item->item->name}}</td>
                                            <td>{{$item->item->service->name??'--'}}</td>
                                            <td>{{env('CURRENCY')}} {{number_format($item->price,2)}}</td>
                                            <td>{{$item->quantity}}</td>
                                            <td>{{env('CURRENCY')}} {{number_format($item->total_price,2)}}</td>

                                        </tr>
                                        @endforeach
                                        <tr>
                                            <th class="fw-bold" colspan="4">Grand Total</th>
                                            <td class="fw-bold"> {{env("CURRENCY")}} {{number_format($order->sub_total,2)}} </td>
                                        </tr>


                                    </table>


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
    const lat= {{$order->deliveryAddress?->lat}};
    const lng= {{$order->deliveryAddress?->lng}};
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

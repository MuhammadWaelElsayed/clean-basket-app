
@section('ordersActive','active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Order Tracking - #{{$order->order_code}}
                    </h1>
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
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/orders') }}" class="text-muted text-hover-primary">Orders</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">Tracking</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url('admin/order-details/'.$order->id) }}" class="btn btn-sm btn-light-primary">
                        <i class="fas fa-file-alt"></i> View Details
                    </a>
                    <a href="{{ url($order->type == 'b2b' ? 'admin/b2b-orders' : 'admin/orders') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">

                <!--begin::Order Summary Card-->
                <div class="card mb-5">
                    <div class="card-body p-6">
                        <div class="row g-5">
                            <!--begin::Customer Info-->
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-3">
                                        <img src="{{$order->user->picture}}" alt="customer" class="rounded-circle">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">{{$order->user->first_name}}</span>
                                        <span class="text-gray-400 fs-7">Customer</span>
                                    </div>
                                </div>
                            </div>
                            <!--end::Customer Info-->

                            <!--begin::Partner Info-->
                            <div class="col-md-3">
                                @if($order->vendor)
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-3">
                                        <img src="{{$order->vendor->picture}}" alt="partner" class="rounded-circle">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">{{$order->vendor->business_name}}</span>
                                        <span class="text-gray-400 fs-7">Partner</span>
                                    </div>
                                </div>
                                @else
                                <div class="text-gray-400">Partner: N/A</div>
                                @endif
                            </div>
                            <!--end::Partner Info-->

                            <!--begin::Driver Info-->
                            <div class="col-md-3">
                                @if($order->driver)
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-3">
                                        <img src="{{$order->driver->picture}}" alt="driver" class="rounded-circle">
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold fs-6">{{$order->driver->name}}</span>
                                        <span class="text-gray-400 fs-7">Driver</span>
                                    </div>
                                </div>
                                @else
                                <div class="text-gray-400">Driver: N/A</div>
                                @endif
                            </div>
                            <!--end::Driver Info-->

                            <!--begin::Order Total-->
                            <div class="col-md-3">
                                <div class="d-flex flex-column text-end">
                                    <span class="text-gray-800 fw-bold fs-3">{{env('CURRENCY')}} {{number_format($order->grand_total,2)}}</span>
                                    <span class="text-gray-400 fs-7">Total Amount</span>
                                </div>
                            </div>
                            <!--end::Order Total-->
                        </div>
                    </div>
                </div>
                <!--end::Order Summary Card-->

                <!--begin::Tracking Timeline Card-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Order Tracking Timeline</h3>
                        <div class="card-toolbar">
                            <span class="badge badge-lg badge-{{$statuses[$order->status]}}">
                                {{$order->status}}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-9">
                        <!--begin::Timeline-->
                        <div class="timeline-label">
                            @php
                                $timelineStatuses = [
                                    'DRAFT' => ['icon' => 'fa-drafting-compass', 'color' => 'primary', 'title' => 'Draft', 'desc' => 'Order is in draft state'],
                                    'PLACED' => ['icon' => 'fa-shopping-cart', 'color' => 'primary', 'title' => 'Order Placed', 'desc' => 'Order has been placed successfully'],
                                    'ON_THE_WAY_FOR_PICKUP' => ['icon' => 'fa-truck', 'color' => 'info', 'title' => 'On The Way For Pickup', 'desc' => 'Driver is on the way to pickup'],
                                    'PICKED_UP' => ['icon' => 'fa-box', 'color' => 'success', 'title' => 'Picked Up', 'desc' => 'Order has been picked up'],
                                    'ARRIVED' => ['icon' => 'fa-map-marker-alt', 'color' => 'warning', 'title' => 'Arrived at Partner', 'desc' => 'Order has arrived at partner location'],
                                    'PROCESSING' => ['icon' => 'fa-cog', 'color' => 'primary', 'title' => 'Processing', 'desc' => 'Order is being processed'],
                                    'CONFIRMED_PAID' => ['icon' => 'fa-check-circle', 'color' => 'success', 'title' => 'Confirmed & Paid', 'desc' => 'Payment confirmed'],
                                    'READY_TO_DELIVER' => ['icon' => 'fa-check-double', 'color' => 'info', 'title' => 'Ready to Deliver', 'desc' => 'Order is ready for delivery'],
                                    'PICKED_FOR_DELIVER' => ['icon' => 'fa-shipping-fast', 'color' => 'warning', 'title' => 'Out for Delivery', 'desc' => 'Order is out for delivery'],
                                    'DELIVERED' => ['icon' => 'fa-flag-checkered', 'color' => 'success', 'title' => 'Delivered', 'desc' => 'Order has been delivered'],
                                ];

                                // إذا كان الطلب ملغى، نعرض حالة الإلغاء
                                if($order->status === 'CANCELLED') {
                                    $timelineStatuses = [
                                        'DRAFT' => ['icon' => 'fa-drafting-compass', 'color' => 'secondary', 'title' => 'Draft', 'desc' => 'Order is in draft state'],
                                        'PLACED' => ['icon' => 'fa-shopping-cart', 'color' => 'primary', 'title' => 'Order Placed', 'desc' => 'Order has been placed successfully'],
                                        'CANCELLED' => ['icon' => 'fa-times-circle', 'color' => 'danger', 'title' => 'Cancelled', 'desc' => 'Order has been cancelled'],
                                    ];
                                }

                                // إذا كان الطلب في حالة draft، نعرض الحالات المناسبة
                                if($order->status === 'DRAFT') {
                                    $timelineStatuses = [
                                        'DRAFT' => ['icon' => 'fa-drafting-compass', 'color' => 'secondary', 'title' => 'Draft', 'desc' => 'Order is in draft state'],
                                        'PLACED' => ['icon' => 'fa-shopping-cart', 'color' => 'primary', 'title' => 'Order Placed', 'desc' => 'Order has been placed successfully'],
                                    ];
                                }
                            @endphp

                            @foreach($timelineStatuses as $statusKey => $statusInfo)
                                @php
                                    $isCompleted = $this->isStatusCompleted($statusKey);
                                    $isCurrent = $this->isCurrentStatus($statusKey);
                                    $statusTime = $this->getStatusTime($statusKey);

                                    // تحديد اللون والحالة
                                    if($isCompleted) {
                                        $badgeClass = 'badge-light-' . $statusInfo['color'];
                                        $iconClass = 'text-' . $statusInfo['color'];
                                        $opacity = '1';
                                    } elseif($isCurrent) {
                                        $badgeClass = 'badge-' . $statusInfo['color'];
                                        $iconClass = 'text-white';
                                        $opacity = '1';
                                    } else {
                                        $badgeClass = 'badge-light-secondary';
                                        $iconClass = 'text-muted';
                                        $opacity = '0.5';
                                    }
                                @endphp

                                <!--begin::Item-->
                                <div class="timeline-item" style="opacity: {{$opacity}}">
                                    <!--begin::Label-->
                                    <div class="timeline-label fw-bold text-gray-800 fs-6" style="width: 200px;">
                                        @if($statusTime)
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="fw-bold text-success fs-6">{{date('d M, Y', strtotime($statusTime))}}</span>
                                                <span class="text-primary fs-7 fw-semibold">{{date('h:i A', strtotime($statusTime))}}</span>
                                            </div>
                                        @else
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="text-muted fs-7">Not Started</span>
                                                <span class="text-muted fs-8">--:--</span>
                                            </div>
                                        @endif
                                    </div>
                                    <!--end::Label-->

                                    <!--begin::Badge-->
                                    <div class="timeline-badge">
                                        <span class="badge {{$badgeClass}} badge-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas {{$statusInfo['icon']}} fs-2 {{$iconClass}}"></i>
                                        </span>
                                    </div>
                                    <!--end::Badge-->

                                    <!--begin::Content-->
                                    <div class="timeline-content d-flex align-items-center">
                                        <div class="d-flex flex-column flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="fw-bold text-gray-800 fs-5 me-2">{{$statusInfo['title']}}</span>
                                                @if($isCompleted)
                                                    <i class="fas fa-check-circle text-success fs-4"></i>
                                                @elseif($isCurrent)
                                                    <span class="spinner-border spinner-border-sm text-{{$statusInfo['color']}}" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-gray-400 fs-7 mb-2">{{$statusInfo['desc']}}</span>
                                            @if($statusTime)
                                                <span class="text-success fs-8">
                                                    <i class="fas fa-check me-1"></i>
                                                    Completed
                                                </span>
                                            @elseif($isCurrent)
                                                <span class="text-{{$statusInfo['color']}} fs-8">
                                                    <i class="fas fa-spinner fa-spin me-1"></i>
                                                    In Progress
                                                </span>
                                            @else
                                                <span class="text-muted fs-8">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Not Started
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <!--end::Content-->
                                </div>
                                <!--end::Item-->
                            @endforeach
                        </div>
                        <!--end::Timeline-->
                    </div>
                </div>
                <!--end::Tracking Timeline Card-->

                <!--begin::Additional Info-->
                <div class="row g-5 mt-5">
                    <!--begin::Delivery Address-->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                    Delivery Address
                                </h3>
                            </div>
                            <div class="card-body">
                                @if($order->deliveryAddress)
                                    <div class="table-responsive">
                                        <table class="table table-row-bordered">
                                            <tr>
                                                <th class="w-150px">Building:</th>
                                                <td>{{$order->deliveryAddress->building ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th>Apartment:</th>
                                                <td>{{$order->deliveryAddress->appartment ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th>Floor:</th>
                                                <td>{{$order->deliveryAddress->floor ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th>Area:</th>
                                                <td>{{$order->deliveryAddress->area ?? 'N/A'}}</td>
                                            </tr>
                                            <tr>
                                                <th>Basket No:</th>
                                                <td>{{$order->deliveryAddress->basket_no ?? 'N/A'}}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <a href="https://maps.google.com/?q={{$order->deliveryAddress->lat}},{{$order->deliveryAddress->lng}}"
                                       target="_blank"
                                       class="btn btn-primary btn-sm w-100 mt-3">
                                        <i class="fas fa-directions"></i> Get Directions
                                    </a>
                                @else
                                    <p class="text-muted">No address information available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!--end::Delivery Address-->

                    <!--begin::Order Info-->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    Order Information
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-row-bordered">
                                        <tr>
                                            <th class="w-150px">Order #:</th>
                                            <td class="fw-bold">#{{$order->order_code}}</td>
                                        </tr>
                                        <tr>
                                            <th>Pickup Date:</th>
                                            <td>{{date('d M, Y', strtotime($order->pickup_date))}}</td>
                                        </tr>
                                        <tr>
                                            <th>Pickup Time:</th>
                                            <td>{{$order->pickup_time}}</td>
                                        </tr>
                                        <tr>
                                            <th>Dropoff Date:</th>
                                            <td>{{date('d M, Y', strtotime($order->dropoff_date))}}</td>
                                        </tr>
                                        <tr>
                                            <th>Dropoff Time:</th>
                                            <td>{{$order->dropoff_time}}</td>
                                        </tr>
                                        <tr>
                                            <th>Items Count:</th>
                                            <td>{{$order->orderItems->count()}} items</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method:</th>
                                            <td>
                                                <span class="badge badge-light-primary">{{$order->pay_method}}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Payment Status:</th>
                                            <td>
                                                <span class="badge badge-light-success">{{$order->pay_status}}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Order Info-->
                </div>
                <!--end::Additional Info-->

            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
</div>

@section('scripts')
<style>
    .timeline-label {
        position: relative;
    }

    .timeline-item {
        display: flex;
        align-items: flex-start;
        padding: 1.5rem 0;
        position: relative;
        min-height: 80px;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 175px;
        top: 50px;
        width: 2px;
        height: calc(100% - 25px);
        background: #e4e6ef;
    }

    .timeline-label {
        flex-shrink: 0;
        text-align: right;
        min-width: 200px;
        padding-right: 1rem;
    }

    .timeline-badge {
        margin: 0 2rem;
        flex-shrink: 0;
        z-index: 1;
    }

    .timeline-content {
        flex-grow: 1;
        padding-top: 0.5rem;
        padding-left: 1rem;
    }

    @media (max-width: 768px) {
        .timeline-label {
            width: 100px !important;
            font-size: 0.85rem !important;
        }

        .timeline-badge span {
            width: 40px !important;
            height: 40px !important;
        }

        .timeline-badge i {
            font-size: 1rem !important;
        }
    }
</style>

<script>
    // Auto refresh every 30 seconds
    setInterval(function() {
        Livewire.dispatch('$refresh');
    }, 30000);
</script>
@endsection


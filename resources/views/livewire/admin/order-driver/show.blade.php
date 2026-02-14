@section('orderDriversActive', 'active')

<div>
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">External Driver Details</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.order-drivers.index') }}" class="text-muted text-hover-primary">External Drivers</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Details</li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->

                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('admin.order-drivers.index') }}" class="btn btn-sm fw-bold btn-secondary">
                        <i class="ki-duotone ki-arrow-right fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to List
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
                <!--begin::Layout-->
                <div class="d-flex flex-column flex-lg-row">
                    <!--begin::Content-->
                    <div class="flex-lg-row-fluid mb-10 mb-lg-0 me-lg-7 me-xl-10">
                        <!--begin::Card-->
                        <div class="card">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Ride Information</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">External ride details</span>
                                </h3>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-5">
                                <!--begin::Form-->
                                <div class="row">
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">External Ride ID</label>
                                        <div class="fw-bold fs-6 text-gray-800">{{ $orderDriver->external_ride_id }}</div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Provider</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            <span class="badge badge-light-info">{{ $orderDriver->provider ?? 'Not specified' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Status</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @php
                                                $statusColors = [
                                                    'ACCEPTED' => 'success',
                                                    'ARRIVING' => 'warning',
                                                    'ARRIVED' => 'primary',
                                                    'IN_PROGRESS' => 'info',
                                                    'COMPLETED' => 'success',
                                                    'CANCELLED' => 'danger',
                                                    'UNKNOWN' => 'secondary'
                                                ];
                                                $color = $statusColors[$orderDriver->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-light-{{ $color }}">{{ $orderDriver->status }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Trip Cost</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @if($orderDriver->trip_cost)
                                                {{ number_format($orderDriver->trip_cost, 2) }} SAR
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Changed Date</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @if($orderDriver->time_changed)
                                                {{ \Carbon\Carbon::parse($orderDriver->time_changed)->format('Y-m-d H:i:s') }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Created Date</label>
                                        <div class="fw-bold fs-6 text-gray-800">{{ $orderDriver->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                </div>
                                <!--end::Form-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->

                        <!--begin::Card-->
                        <div class="card mt-7">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Location Information</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Start and end coordinates</span>
                                </h3>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-5">
                                <div class="row">
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Start Latitude</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @if($orderDriver->start_lat)
                                                {{ $orderDriver->start_lat }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">Start Longitude</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @if($orderDriver->start_lng)
                                                {{ $orderDriver->start_lng }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">End Latitude</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @if($orderDriver->end_lat)
                                                {{ $orderDriver->end_lat }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-7">
                                        <label class="form-label fw-semibold fs-6 text-gray-700">End Longitude</label>
                                        <div class="fw-bold fs-6 text-gray-800">
                                            @if($orderDriver->end_lng)
                                                {{ $orderDriver->end_lng }}
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->
                    </div>
                    <!--end::Content-->

                    <!--begin::Sidebar-->
                    <div class="flex-lg-auto min-w-lg-300px">
                        <!--begin::Card-->
                        <div class="card" data-kt-sticky="true" data-kt-sticky-name="invoice" data-kt-sticky-offset="{default: false, lg: '200px'}" data-kt-sticky-width="{lg: '250px', lg: '300px'}" data-kt-sticky-left="auto" data-kt-sticky-top="150px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
                            <!--begin::Card header-->
                            <div class="card-header border-0 pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Driver Information</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Assigned driver details</span>
                                </h3>
                            </div>
                            <!--end::Card header-->

                            <!--begin::Card body-->
                            <div class="card-body pt-5">
                                @if($orderDriver->driver)
                                    <div class="d-flex flex-center flex-column py-5">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-100px symbol-circle mb-7">
                                            @if($orderDriver->driver->profile_image)
                                                <img src="{{ asset('uploads/' . $orderDriver->driver->profile_image) }}" alt="Driver Image" />
                                            @else
                                                <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                                                    {{ substr($orderDriver->driver->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>
                                        <!--end::Avatar-->

                                        <!--begin::Name-->
                                        <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ $orderDriver->driver->name }}</a>
                                        <!--end::Name-->

                                        <!--begin::Position-->
                                        <div class="mb-9">
                                            <div class="badge badge-lg badge-light-primary d-inline">{{ $orderDriver->driver->provider ?? 'Not specified' }}</div>
                                        </div>
                                        <!--end::Position-->
                                    </div>

                                    <!--begin::Info-->
                                    <div class="d-flex flex-wrap flex-center">
                                        <!--begin::Stats-->
                                        <div class="border border-gray-300 border-dashed rounded min-w-80px py-3 px-4 me-6 mb-3">
                                                <div class="fs-6 fw-bold text-gray-700">Driver ID</div>
                                            <div class="fs-2 fw-bold text-gray-900">{{ $orderDriver->driver->external_driver_id }}</div>
                                        </div>
                                        <!--end::Stats-->

                                        @if($orderDriver->driver->phone)
                                            <!--begin::Stats-->
                                            <div class="border border-gray-300 border-dashed rounded min-w-80px py-3 px-4 me-6 mb-3">
                                                <div class="fs-6 fw-bold text-gray-700">Phone</div>
                                                <div class="fs-2 fw-bold text-gray-900">{{ $orderDriver->driver->phone }}</div>
                                            </div>
                                            <!--end::Stats-->
                                        @endif

                                        @if($orderDriver->driver->email)
                                            <!--begin::Stats-->
                                            <div class="border border-gray-300 border-dashed rounded min-w-80px py-3 px-4 me-6 mb-3">
                                                <div class="fs-6 fw-bold text-gray-700">Email</div>
                                                <div class="fs-2 fw-bold text-gray-900">{{ $orderDriver->driver->email }}</div>
                                            </div>
                                            <!--end::Stats-->
                                        @endif
                                    </div>
                                    <!--end::Info-->
                                @else
                                    <div class="text-center py-10">
                                        <div class="text-gray-600">No driver assigned to this ride</div>
                                    </div>
                                @endif
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Card-->

                        @if($orderDriver->order)
                            <!--begin::Card-->
                            <div class="card mt-7">
                                <!--begin::Card header-->
                                <div class="card-header border-0 pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Order Information</span>
                                    <span class="text-muted mt-1 fw-semibold fs-7">Related order details</span>
                                    </h3>
                                </div>
                                <!--end::Card header-->

                                <!--begin::Card body-->
                                <div class="card-body pt-5">
                                    <div class="d-flex flex-column">
                                        <div class="mb-3">
                                            <span class="fw-semibold text-gray-700">Order Number:</span>
                                            <a href="{{ route('admin.orders.edit', $orderDriver->order->id) }}" class="text-primary fw-bold ms-2">
                                                {{ $orderDriver->order->order_code }}
                                            </a>
                                        </div>
                                        <div class="mb-3">
                                            <span class="fw-semibold text-gray-700">Order Status:</span>
                                            <span class="badge badge-light-info ms-2">{{ $orderDriver->order->status }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <span class="fw-semibold text-gray-700">Order Date:</span>
                                            <span class="ms-2">{{ $orderDriver->order->created_at }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Card-->
                        @endif
                    </div>
                    <!--end::Sidebar-->
                </div>
                <!--end::Layout-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
</div>

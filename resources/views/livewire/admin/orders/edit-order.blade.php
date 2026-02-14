@section('ordersActive', 'active')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Edit Order</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url($order->type == 'b2b' ? 'admin/b2b-orders' : 'admin/orders') }}" class="text-muted text-hover-primary">Orders</a>
                        </li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <div class="back-home-btn">
                    <a href="{{ url($order->type == 'b2b' ? 'admin/b2b-orders' : 'admin/orders') }}" class="btn btn-primary">Back to Orders</a>
                </div>
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Form-->
                        <form id="kt_modal_new_target_form" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="save">
                            @csrf

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Order Code</label>
                                    <input type="text" class="form-control form-control-solid" wire:model="order_code" readonly>
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Order Status</label>
                                    <select class="form-select form-select-solid" wire:model="status">
                                        <option value="">Select Status</option>
                                        <option value="DRAFT">Draft</option>
                                        <option value="PLACED">Placed</option>
                                        <option value="ON_THE_WAY_FOR_PICKUP">On the Way for Pickup</option>
                                        <option value="PICKED_UP">Picked Up</option>
                                        <option value="ARRIVED">Arrived</option>
                                        <option value="PROCESSING">Processing</option>
                                        <option value="CONFIRMED_PAID">Confirmed & Paid</option>
                                        <option value="ON_THE_WAY_TO_PARTNER">On the Way to Partner</option>
                                        <option value="READY_TO_DELIVER">Ready to Deliver</option>
                                        <option value="PICKED_FOR_DELIVER">Picked for Delivery</option>
                                        <option value="DELIVERED">Delivered</option>
                                        <option value="CANCELLED">Cancelled</option>
                                    </select>
                                    @error('status') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">User</label>
                                    <input type="text" class="form-control form-control-solid" value="{{ $user_display }}" readonly>
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Address</label>
                                    <input type="text" class="form-control form-control-solid" value="{{ $address_display }}" readonly>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Vendor</label>
                                    <select class="form-select form-select-solid" wire:model="vendor_id">
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor['id'] }}">{{ $vendor['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Driver</label>
                                    <select class="form-select form-select-solid" wire:model="driver_id">
                                        <option value="">Select Driver</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver['id'] }}">{{ $driver['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('driver_id') <span class="text-danger">{{$message}}</span> @enderror
                                </div>

                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Pickup Driver</label>
                                    <select class="form-select form-select-solid" wire:model="pickup_driver_id">
                                        <option value="">Select Pickup Driver</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver['id'] }}">{{ $driver['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('pickup_driver_id') <span class="text-danger">{{$message}}</span> @enderror
                                </div>

                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Delivery Driver</label>
                                    <select class="form-select form-select-solid" wire:model="delivery_driver_id">
                                        <option value="">Select Delivery Driver</option>
                                        @foreach($drivers as $driver)
                                            <option value="{{ $driver['id'] }}">{{ $driver['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('delivery_driver_id') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Pickup Date</label>
                                    <input type="date" class="form-control form-control-solid" wire:model="pickup_date">
                                    @error('pickup_date') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Pickup Time</label>
                                    <input type="text" class="form-control form-control-solid" placeholder="HH:MM - HH:MM" wire:model="pickup_time">
                                    @error('pickup_time') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Dropoff Date</label>
                                    <input type="date" class="form-control form-control-solid" wire:model="dropoff_date">
                                    @error('dropoff_date') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Dropoff Time</label>
                                    <input type="text" class="form-control form-control-solid" placeholder="HH:MM - HH:MM" wire:model="dropoff_time">
                                    @error('dropoff_time') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Payment Status</label>
                                    <select class="form-select form-select-solid" wire:model="pay_status">
                                        <option value="">Select Payment Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Unpaid">Unpaid</option>
                                        <option value="Paid">Paid</option>
                                        <option value="Partial">Partial</option>
                                        <option value="دفع_جزئي">دفع جزئي</option>
                                    </select>
                                    @error('pay_status') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Payment Method</label>
                                    <input type="text" class="form-control form-control-solid" wire:model="pay_method">
                                    @error('pay_method') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Sub Total</label>
                                    <input type="number" class="form-control form-control-solid" step="0.01" wire:model="sub_total" readonly>
                                    @error('sub_total') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Grand Total</label>
                                    <input type="number" class="form-control form-control-solid" step="0.01" wire:model="grand_total" readonly>
                                    @error('grand_total') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Instructions</label>
                                    <textarea class="form-control form-control-solid" rows="3" wire:model="instructions"></textarea>
                                    @error('instructions') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2">Delivery Coordinates</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control form-control-solid" placeholder="Latitude" wire:model="deliver_lat">
                                            @error('deliver_lat') <span class="text-danger">{{$message}}</span> @enderror
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control form-control-solid" placeholder="Longitude" wire:model="deliver_lng">
                                            @error('deliver_lng') <span class="text-danger">{{$message}}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Actions-->
                            <div class="text-center">
                                <a href="{{ url($order->type == 'b2b' ? 'admin/b2b-orders' : 'admin/orders') }}" id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
                                <button type="submit" id="kt_modal_new_target_submit" class="btn btn-base">
                                    <span class="indicator-label">Update Order</span>
                                    <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                            <!--end::Actions-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
        </div>
    </div>
</div>

@section('scripts')
    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>
@endsection

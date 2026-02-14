{{-- @extends('components.layouts.admin-dashboard') --}}

@section('ordersActive','active')

{{-- @section('main') --}}
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Edit Order Items</h1>
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
        <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->

                <div class="card">
                 <div class="d-block">
                    <button class="btn btn-base btn-sm float-end m-3" type="button" wire:click="add()">+ Add New</button>
                 </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->

                    <div class="card-body pt-0">
                        <!--begin::Form-->
                        <form id="kt_modal_new_target_form" enctype="multipart/form-data"  method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="save()">
                            @csrf
                            <br>
							<!--begin::Input group-->
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="required fs-6 fw-semibold mb-2"> Item</label>
                                </div>
                                <div class="col-md-2">
                                    <label class="required fs-6 fw-semibold mb-2"> Service Type</label>
                                </div>
                                <div class="col-md-2">
                                    <label class="required fs-6 fw-semibold mb-2"> Price</label>
                                </div>
                                <div class="col-md-2">
                                    <label class="required fs-6 fw-semibold mb-2"> Quantity</label>
                                </div>
                                <div class="col-md-2">
                                    <label class="required fs-6 fw-semibold mb-2"> Sub Total</label>
                                </div>
                                <div class="col-md-1">
                                    <label class=" fs-6  mb-2"> </label>
                                </div>
                            </div>
                            @foreach ($items as $key => $val)
                            @php
                               $grand_total+=$val['sub_total'];
                            @endphp
                                <div class="row g-5 my-1">
                                    <div class="col-md-3 fv-row fv-plugins-icon-container">
                                        {{-- <select class="form-control mySelect"  wire:model="items.{{$key}}.item_id"  wire:change="getPrice({{$key}})">
                                            <option value="">Select Item</option>
                                            @foreach ($pricing_items as $item)
                                            <option value="{{$item->id}}" {{($item->id==$items[$key]['item_id'])?"selected":""}}>{{$item->name}}</option>
                                            @endforeach
                                        </select> --}}
                                        <select class="form-control mySelect"  id="{{$key}}"  wire:model="items.{{$key}}.item_id"  wire:change="getPrice({{$key}})">
                                            <option value="">Select Item</option>
                                            @foreach ($pricing_items as $item)
                                            <option value="{{$item->id}}" {{($item->id==$items[$key]['item_id'])?"selected":""}}>{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                         @error('item_id') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <select class="form-control "  id="{{$key}}"  wire:model="items.{{$key}}.type"  wire:change="getPrice({{$key}})">
                                            <option value="Only Press">Only Press</option>
                                            <option value="Full Service">Full Service</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 fv-row fv-plugins-icon-container">
                                        <input type="number" readonly step="0.01" class="form-control form-control-solid" placeholder="Item Price" wire:model="items.{{$key}}.price" >
                                    </div>
                                    <div class="col-md-2 fv-row fv-plugins-icon-container">
                                        <input type="number" step="0.01" class="form-control form-control-solid" placeholder="Enter Item Quantity" wire:keyup="getPrice({{$key}})" wire:model="items.{{$key}}.quantity" >
                                        @error('quantity') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                    <div class="col-md-2 fv-row fv-plugins-icon-container">
                                        <input type="number" readonly step="0.01" class="form-control form-control-solid" placeholder="Sub Total" wire:model="items.{{$key}}.sub_total" >
                                    </div>
                                    <div class="col-md-1 fv-row fv-plugins-icon-container">
                                        @if ($key!=0)
                                            <button class="btn btn-base btn-sm" type="button" wire:click="remove({{$key}})">Remove</button>
                                        @endif
                                    </div>
                                </div>

                            @endforeach


                         <div class="row g-9 my-3">
							<!--begin::Actions-->
							<div class="text-center">
                                <h4 class="mb-5">Grand Total : {{env('CURRENCY')." ".$grand_total}}</h4>
								<a href="{{ url('admin/orders') }}" id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
								<button type="button" id="kt_modal_new_target_submit" onclick="confirmSave()" class="btn btn-base">
									<span class="indicator-label">Save</span>
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

<script>
    initializeSelect2();
    function initializeSelect2() {
        $('.mySelect').select2();
        $('.mySelect').on('select2:select', function(e) {
            var value = e.params.data.id;
            var key=e.target.id;
            @this.set('items.'+key+'.item_id', value);
            Livewire.dispatch('getPrice',key);
        });
    }


    function confirmSave() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to add these items. Once customer accepted you will not be able to update items.",
            icon: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#5E6278',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('save');
            }
        });
    }
</script>
    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>

@endsection


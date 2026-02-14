{{-- @extends('components.layouts.admin-dashboard') --}}

@section('itemsActive', 'active')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Add
                        New Item</h1>
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
                            <a href="{{ url('admin/items') }}" class="text-muted text-hover-primary">Items</a>
                        </li>
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
            <div id="kt_app_content_container" class="app-container container-xxl"
                data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->
                <div class="card">

                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Form-->
                        <form id="kt_modal_new_target_form" enctype="multipart/form-data" method="POST"
                            class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="store()">
                            @csrf
                            <!--begin::Input group-->
                            <div class="row g-9 my-3">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2"> Name</label>
                                    <input type="text" class="form-control form-control-solid"
                                        placeholder="Enter Item Name" wire:model="name">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class=" fs-6 fw-semibold mb-2"> Name Arabic</label>
                                    <input type="text" class="form-control form-control-solid"
                                        placeholder="Enter Item Name Arabic" wire:model="name_ar">
                                    @error('name_ar')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!--begin::Service Categories Dropdown-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Service Categories:</label>
                                    <div id="service_id" wire:ignore></div>

                                    @error('service_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!--end::Service Categories Dropdown-->

                                <!--begin::Order Priority Dropdown-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Order Priority</label>
                                    <select class="form-select" wire:model="order_priority_id">
                                        <option value="">Select Priority</option>
                                        @foreach ($orderPriorities as $priority)
                                            <option value="{{ $priority->id }}">{{ $priority->name }}
                                                ({{ $priority->name_ar }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('order_priority_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!--end::Order Priority Dropdown-->

                                <!--begin::Services Section-->
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class="required fs-6 fw-semibold mb-2">Available Services</label>
                                    <div class="card card-flush">
                                        <div class="card-body">
                                            <div class="row">
                                                @foreach ($serviceTypes as $serviceType)
                                                    <div class="col-md-4 mb-3"
                                                        wire:key="service-{{ $serviceType->id }}">
                                                        <div class="form-check form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox"
                                                                wire:model="selectedServices"
                                                                value="{{ (int) $serviceType->id }}"
                                                                id="service_{{ $serviceType->id }}">
                                                            <label class="form-check-label"
                                                                for="service_{{ $serviceType->id }}">
                                                                {{ $serviceType->name }} ({{ $serviceType->name_ar }})
                                                            </label>
                                                        </div>

                                                        {{-- @if (in_array($serviceType->id, $selectedServices)) --}}
                                                        <div class="mt-2">
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label
                                                                        class="fs-7 fw-semibold text-muted">Price</label>
                                                                    <input type="number" step="0.01"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Price"
                                                                        wire:model="servicePrices.{{ $serviceType->id }}">
                                                                    @error("servicePrices.{$serviceType->id}")
                                                                        <span
                                                                            class="text-danger fs-8">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                                <div class="col-6">
                                                                    <label class="fs-7 fw-semibold text-muted">Discount
                                                                        Price</label>
                                                                    <input type="number" step="0.01"
                                                                        class="form-control form-control-sm"
                                                                        placeholder="Discount Price"
                                                                        wire:model="serviceDiscountPrices.{{ $serviceType->id }}">
                                                                    @error("serviceDiscountPrices.{$serviceType->id}")
                                                                        <span
                                                                            class="text-danger fs-8">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        {{-- @endif --}}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @error('selectedServices')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <!--end::Services Section-->



                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <label class="fs-6 fw-semibold mb-2 required">Pressing Price</label>
                                    <input type="number" step="0.01" class="form-control form-control-solid"
                                        placeholder="Enter Item Pressing Price" wire:model="price">
                                    @error('price')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>



                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class=" fs-6 fw-semibold mb-2">Descrpition</label>
                                    <textarea wire:model="description" class="form-control form-control-solid" placeholder="Write Descrpition"
                                        cols="30" rows="3"></textarea>
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
                                    <label class=" fs-6 fw-semibold mb-2">Descrpition Arabic</label>
                                    <textarea wire:model="description_ar" class="form-control form-control-solid" placeholder="Write Descrpition Arabic"
                                        cols="30" rows="3"></textarea>
                                </div>
                                {{-- Picture --}}
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-semibold mb-3">
                                        <span>Upload Picture</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            aria-label="Allowed file types: png, jpg, jpeg."
                                            data-kt-initialized="1"></i>
                                    </label>
                                    <div class="mt-1">
                                        <div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty"
                                            data-kt-image-input="true">
                                            <!--begin::Preview existing avatar-->

                                            <div class="image-input-wrapper w-100px h-100px"
                                                style="background-image: url('{{ $image != null ? $image->temporaryUrl() : asset('uploads/blank2.jpg') }} ')">
                                            </div>

                                            <label
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                aria-label="Change avatar" data-kt-initialized="1">
                                                <i class="bi bi-pencil-fill fs-7"></i>
                                                <input type="file" wire:model="image" name="image"
                                                    accept=".png, .jpg, .jpeg">
                                                {{-- <input type="hidden" name="avatar_remove"> --}}
                                            </label>
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                aria-label="Cancel avatar" data-kt-initialized="1">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                            <!--end::Cancel-->
                                            <!--begin::Remove-->
                                            <span
                                                class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                aria-label="Remove avatar" data-kt-initialized="1">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                            <!--end::Remove-->
                                        </div>
                                        <!--end::Image input-->
                                    </div>
                                    @error('image')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>

                            <div class="row g-9 my-3">

                                <!--begin::Actions-->
                                <div class="text-center">
                                    <a href="{{ url('admin/items') }}" id="kt_modal_new_target_cancel"
                                        class="btn btn-light me-3">Back</a>
                                    <button type="submit" id="kt_modal_new_target_submit" class="btn btn-base">
                                        <span class="indicator-label" wire:loading.remove>Submit</span>
                                        <span class="indicator-progress" wire:loading>Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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

{{-- @endsection --}}
@section('scripts')

    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>

    <link data-navigate-once rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.css"
        integrity="sha256-KqTuc/vUgQsb5EMyyxWf62qYinMUXDpWELyNx+cCUr0=" crossorigin="anonymous">
    <script data-navigate-once src="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.js"
        integrity="sha256-Gsn2XyJGdUeHy0r4gaP1mJy1JkLiIWY6g6hJhV5UrIw=" crossorigin="anonymous"></script>

    <script>
        VirtualSelect.init({
            ele: '#service_id',
            options: <?= json_encode($services) ?>,
            placeholder: '{{ __('Select Service Categories') }}',
            additionalClasses: 'filter-field',
            search: true,
            multiple: true,
            showSelectedOptionsFirst: true,
            maxWidth: '100%',
        });
        $('#service_id').on('change', function(e) {
            @this.set('service_id', $(this).val());
        });
    </script>

@endsection

<div>

    @section('partnersActive', 'active')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <style>
            .form-select[multiple] {
                height: 50px !important;
            }

            .phone-field,
            .partner-field,
            .vscomp-wrapper.has-clear-button .vscomp-toggle-button {
                background: #f5f8fa !important;
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
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            {{ $eId ? 'Edit' : 'Create' }} Partner</h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Partner-->
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" wire:navigate
                                    class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <!--end::Partner-->
                            <!--begin::Partner-->
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <!--end::Partner-->
                            <!--begin::Partner-->
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/partners') }}" wire:navigate
                                    class="text-muted text-hover-primary">Partners</a>
                            </li>
                            <!--end::Partner-->
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->

                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid"
                data-select2-id="select2-data-kt_app_content">
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
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">First Name</label>
                                        <input type="text" class="form-control form-control-solid"
                                            placeholder="Enter First Name" wire:model="first_name">
                                        @error('first_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Last Name</label>
                                        <input type="text" class="form-control form-control-solid"
                                            placeholder="Enter Last Name" wire:model="last_name">
                                        @error('last_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Business Name</label>
                                        <input type="text" class="form-control form-control-solid"
                                            placeholder="Enter Business Name" wire:model="business_name">
                                        @error('business_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2"> Email</label>
                                        <input type="email" class="form-control form-control-solid"
                                            placeholder="Enter Partner Email" wire:model="email">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2"> Phone</label>
                                        {{-- <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon2">966</span>
                                            <input type="number" class="form-control form-control-solid" placeholder="51234568" wire:model="phone" value="">
                                        </div> --}}
                                        <input type="number" wire:model="phone"
                                            class="form-control form-control-solid " placeholder="966xxxxxxxxx">
                                        @error('phone')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">City</label>
                                        <div id="city" wire:ignore></div>
                                        @error('city')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Area</label>
                                        <div id="areas" wire:ignore></div>
                                        @error('area') <span class="text-danger">{{$message}}</span> @enderror
                                    </div> --}}
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column  fv-row fv-plugins-icon-container">
                                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                                <span class="required">Commission</span>
                                            </label>
                                            <div class="input-group mb-3">
                                                <input type="number" min="1" max="100"
                                                    class="form-control form-control-solid"
                                                    placeholder="Enter Vendor Commission(%)" wire:model="commission">
                                                <span class="input-group-text" id="basic-addon2">%</span>
                                            </div>
                                        </div>
                                        @error('commission')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror

                                    </div>


                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        <label class=" fs-6 fw-semibold mb-2">Descrpition</label>
                                        <textarea wire:model="about" class="form-control form-control-solid" placeholder="Write Descrpition" cols="30"
                                            rows="3"></textarea>
                                        @error('about')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
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
                                                    style="background-image: url('{{ $image != null ? $image->temporaryUrl() : $imageUrl }} ')">
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

                                    {{-- <div class="col-md-12 fv-row " wire:ignore>
                                        <input type="text" id="address-input" wire:model="location" required autocomplete="on" class="form-control  map-input ">

                                        <div class="row mt-5">
                                            <div class="col-md-6">
                                                <input type="hidden" wire:model="lat"  class="form-control" placeholder="Enter latitude" id="address-latitude"  />
                                            </div>
                                            <div class="col-md-6">
                                                <input type="hidden" wire:model="lng" class="form-control" placeholder="Enter longitude" id="address-longitude"  />
                                            </div>
                                        </div>
                                        <br>
                                        <div id="address-map-container" style="width:100%;height:400px; ">
                                            <div style="width: 100%; height: 100%" id="address-map"></div>
                                        </div>
                                    </div> --}}

                                    {{-- Vendor Location --}}
                                    <div class="col-md-12 fv-row fv-plugins-icon-container">
                                        @if ($location)
                                            <div class="mb-2">
                                                <div id="locationName" class="p-3 bg-light rounded border">
                                                    {{ $location }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-2">
                                                <div id="locationName" class="p-3 bg-light rounded border"></div>
                                            </div>
                                        @endif
                                        <div wire:ignore>
                                            <div id="map" style="height:400px; border:1px solid #ccc;"></div>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" id="createDefaultCircle" class="btn btn-sm btn-outline-primary" style="display: none;">
                                                <i class="fas fa-circle"></i> إنشاء منطقة افتراضية (6 كم)
                                            </button>
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-info-circle"></i>
                                                عند إنشاء مغسلة جديدة، سيتم تلقائياً إنشاء منطقة خدمة بنصف قطر 6 كيلومترات حول الموقع المحدد
                                            </small>
                                        </div>
                                        <input type="hidden" id="location" wire:model="location">
                                        <input type="hidden" id="lat" wire:model="lat">
                                        <input type="hidden" id="lng" wire:model="lng">
                                    </div>
                                </div>
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <!--begin::Actions-->
                                <div class="text-center">
                                    <a href="{{ url('admin/partners') }}" id="kt_modal_new_target_cancel"
                                        class="btn btn-light me-3">Back</a>
                                    <button type="submit" id="kt_modal_new_target_submit" class="btn btn-base"
                                        wire:loading.attr="disabled">
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

    @section('scripts')
        <!-- Leaflet CSS + JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
        <!-- Leaflet Control Geocoder -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

        <script type="module">
            import {
                initLeafletMap
            } from '/js/leaflet-service.js';


            const defaultLat = {{ $lat ?? 24.7136 }};
            const defaultLng = {{ $lng ?? 46.6753 }};
            const initialZone = @json($vendor_areas);

            console.log('defaultLat', defaultLat);
            console.log('defaultLng', defaultLng);
            console.log('initialZone', initialZone);

            initLeafletMap(
                'map', // containerId
                'location', // locationInputId
                'lat', // latInputId
                'lng', // lngInputId
                defaultLat,
                defaultLng,
                initialZone // ترسلها لتظهر أولاً
            );
        </script>


<script>
    cities = <?= $cities ?>;

    VirtualSelect.init({
        ele: '#city',
        options: cities,
        placeholder: '{{ __('Select City') }}',
        additionalClasses: 'partner-field',
        noOptionsText: '{{ __('No options found') }}',
        noSearchResultsText: '{{ __('No options found') }}',
        searchPlaceholderText: '{{ __('Search') }}...',
        allOptionsSelectedText: '{{ __('All') }}',
        optionsSelectedText: '{{ __('options selected') }}',
        search: true,
        selectedValue: '{{ $city }}',

    });

    $('#city').on('change', function(e) {
        @this.set('city', $(this).val());
        // @this.call('getCityAreas');
    });
</script>

    @endsection


</div>

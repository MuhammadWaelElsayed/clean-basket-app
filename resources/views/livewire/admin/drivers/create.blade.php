<div>

    @section('driversActive','active')


    <div class="app-main flex-column flex-row-fluid" id="kt_app_main" >
        <style>
            .form-select[multiple]{
                height: 50px !important;
            }
            .phone-field, .partner-field, .vscomp-wrapper.has-clear-button .vscomp-toggle-button{
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
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{($eId)?'Edit':'Create'}} Driver</h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <!--begin::Partner-->
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/dashboard') }}" wire:navigate class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <!--end::Partner-->
                            <!--begin::Partner-->
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <!--end::Partner-->
                            <!--begin::Partner-->
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ url('admin/drivers') }}" wire:navigate class="text-muted text-hover-primary">Drivers</a>
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
            <div id="kt_app_content" class="app-content flex-column-fluid" data-select2-id="select2-data-kt_app_content">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                    <!--begin::Card-->
                    <div class="card">

                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Form-->
                            <form id="kt_modal_new_target_form" enctype="multipart/form-data"  method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="store()">
                                @csrf
                                <!--begin::Input group-->
                                <div class="row g-9 my-3">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2"> Name</label>
                                        <input type="text" class="form-control form-control-solid" placeholder="Enter  Name" wire:model="name" >
                                        @error('name') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>

                                    {{-- <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2"> Email</label>
                                        <input type="email" class="form-control form-control-solid" placeholder="Enter Partner Email" wire:model="email" >
                                        @error('email') <span class="text-danger">{{$message}}</span> @enderror
                                    </div> --}}

                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2"> Phone</label>
                                        <input type="number" wire:model="phone"  class="form-control form-control-solid " placeholder="966xxxxxxxxx">
                                        @error('phone') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>

                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2"> Password</label>
                                        <input type="text" wire:model="password"  class="form-control form-control-solid " placeholder="Password">
                                        @error('password') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>

                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-6 fw-semibold mb-2">Partners</label>
                                        <div id="partner" wire:ignore></div>
                                        @error('partners') <span class="text-danger">{{$message}}</span> @enderror
                                        @error('partners.*') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>

                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="form-label fw-semibold">Role</label>
                                        <select wire:model.live="role" class="form-select">
                                            <option value="">Select Role</option>
                                            <option value="FREELANCE">FREELANCE</option>
                                            <option value="FULL_TIME">FULL TIME</option>
                                            <option value="THIRD_PARTY">THIRD PARTY</option>
                                        </select>
                                        @error('role') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                    {{-- Picture --}}
                                    <div class="col-md-6 fv-row fv-plugins-icon-container ">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-3">
                                            <span>Upload Picture</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" aria-label="Allowed file types: png, jpg, jpeg." data-kt-initialized="1"></i>
                                        </label>
                                        <div class="mt-1">
                                            <div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty" data-kt-image-input="true">
                                                <!--begin::Preview existing avatar-->

                                                <div class="image-input-wrapper w-100px h-100px" style="background-image: url('{{($image!=null)?$image->temporaryUrl(): $imageUrl}} ')"></div>

                                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change avatar" data-kt-initialized="1">
                                                    <i class="bi bi-pencil-fill fs-7"></i>
                                                    <input type="file" wire:model="image" name="image" accept=".png, .jpg, .jpeg">
                                                    {{-- <input type="hidden" name="avatar_remove"> --}}
                                                </label>
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel avatar" data-kt-initialized="1">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                                                <!--end::Cancel-->
                                                <!--begin::Remove-->
                                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove avatar" data-kt-initialized="1">
                                                    <i class="bi bi-x fs-2"></i>
                                                </span>
                                                <!--end::Remove-->
                                            </div>
                                            <!--end::Image input-->
                                        </div>
                                        @error('image') <span class="text-danger">{{$message}}</span> @enderror

                                    </div>

                                </div>

                                <!--begin::Actions-->
                                <div class="text-center mt-5">
                                    <a href="{{ url('admin/drivers') }}"  id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
                                    <button type="submit" id="kt_modal_new_target_submit" class="btn btn-base" wire:loading.attr="disabled">
                                        <span class="indicator-label"  wire:loading.remove >Submit</span>
                                        <span class="indicator-progress"  wire:loading >Please wait...
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
            vendors = <?=$vendors; ?>;

            VirtualSelect.init({
                ele: '#partner',
                options: vendors,
                placeholder: '{{__("Select Partners")}}',
                additionalClasses: 'partner-field',
                noOptionsText: '{{__("No options found")}}',
                noSearchResultsText: '{{__("No options found")}}',
                searchPlaceholderText: '{{__("Search")}}...',
                allOptionsSelectedText: '{{__("All")}}',
                optionsSelectedText: '{{__("options selected")}}',
                search: true,
                multiple: true, // Enable multiple selection
                selectedValue: @json($partners), // Pass array
                maxValues: 0, // No limit on selections
            });

            $('#partner').on('change', function (e) {
                let selectedValues = $(this).val();
                @this.set('partners', selectedValues ? selectedValues : []);
            });
        </script>
    @endsection

    </div>

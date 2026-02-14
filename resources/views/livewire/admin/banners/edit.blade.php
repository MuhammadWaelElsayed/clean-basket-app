{{-- @extends('components.layouts.admin-dashboard') --}}

@section('bannersActive','active')
@section('appShow','show')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Edit Banner</h1>
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
                            <a href="{{ url('admin/banners') }}" class="text-muted text-hover-primary">Banners</a>
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
            <div id="kt_app_content_container" class="app-container container-xxl" data-select2-id="select2-data-kt_app_content_container">
                <!--begin::Card-->
                <div class="card">
                 
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Form--> 
                        <form id="kt_modal_new_target_form" enctype="multipart/form-data"  method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="updateData()">
                            @csrf
							<!--begin::Input group-->
							<div class="row g-9 my-3">
                                {{-- <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2">Link to</label>
                                    <div class="select" id="select-input">
                                        <select wire:model="link_to" id="link_to" class="form-control" wire:change="getOptions()" >
                                            <option value="url">External Url </option>
                                            <option value="no_link">No Link </option>
                                        </select>
                                    </div>
                                    
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2 link_label">Link with</label>
                                    @if ($link_to=="url")
                                        <input type="text" wire:model="link_id" class="form-control" id="link_url" placeholder="External URL">
                                    @else
                                        <select  wire:model="link_id" id="link_id" class="form-control">
                                            <option value="">Select Option</option>
                                            @foreach ($options as $item)
                                                <option value="{{$item->id}}">{{$item->name}}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                   
                                </div> --}}
								<!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-semibold mb-3">
                                        <span>Upload Picture</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" aria-label="Allowed file types: png, jpg, jpeg." data-kt-initialized="1"></i>
                                    </label>
                                    <div class="mt-1"> 
                                        <div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty" data-kt-image-input="true">
                                         
                                            <div class="image-input-wrapper w-100px h-100px" style="background-image: url('{{($image!=null)?$image->temporaryUrl():$imageUrl}}')"></div>
                                        
                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change avatar" data-kt-initialized="1">
                                                <i class="bi bi-pencil-fill fs-7"></i>
                                                <input type="file" wire:model="image" accept=".png, .jpg, .jpeg" >
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
						
                         <div class="row g-9 my-3">
						
							<!--begin::Actions-->
							<div class=""> 
								<a href="{{ url('admin/banners') }}" id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
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

{{-- @endsection --}}
@section('scripts')

    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>

@endsection
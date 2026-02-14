<div>

@section('vendorsActive','active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <style>
        .form-select[multiple]{
            height: 50px !important;
        }
        .phone-field, .partner-field, .vscomp-wrapper.has-clear-button .vscomp-toggle-button{
            background: #f5f8fa !important;
        }
    </style>
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Add New Vendor</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Vendor-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <!--end::Vendor-->
                        <!--begin::Vendor-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Vendor-->
                        <!--begin::Vendor-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/vendors') }}" class="text-muted text-hover-primary">Vendors</a>
                        </li>
                        <!--end::Vendor-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
              
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl" >
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
								<!--begin::Col-->
                                @if ($partner==null)
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2">Is Company</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="is_company" value="1" checked="" id="flexSwitchCheckChecked">
                                    </div>
                                    
                                </div>
                                {{-- <div class="col-md-5 fv-row fv-plugins-icon-container" >
                                    @if ($is_company)
                                        <label class="required fs-6 fw-semibold mb-2"> Company Name</label>
                                        <input type="text" class="form-control form-control-solid" placeholder="Enter Company Name" wire:model="company_name" >
                                        @error('company_name') <span class="text-danger">{{$message}}</span> @enderror
                                    @endif
                                </div> 
                                @endif --}}
                                

                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2">{{($is_company)?"Company":""}} Name</label>
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter {{($is_company)?"Company":"Vendor"}} Name" wire:model="name" >
                                    @error('name') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2"> Email</label>
                                    <input type="email" class="form-control form-control-solid" placeholder="Enter Vendor Email" wire:model="email" >
                                    @error('email') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2"> Phone</label>
                                    <div class="input-group  mb-3 phone-field">
                                        <div class="input-group-prepend" wire:ignore>
                                          <span class="input-group-text p-0 border-0" id="basic-addon1">
                                                <div id="phoneCode" wire:ignore></div>
                                            </span>
                                        </div>
                                        <input type="number" wire:model="phone"  class="form-control form-control-solid border-0" placeholder="Phone">
                                    </div>
                                    @error('phone') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2">Country:</label>
                                    <div id="country" wire:ignore></div>
                                    @error('country') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2">Nationality:</label>
                                    <div id="nationality" wire:ignore></div>
                                    @error('nationality') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2">Languages:</label>
                                    <div id="languages" wire:ignore></div>
                                    @error('languages') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2">Arbitrators:</label>
                                    <div id="arbitrators" wire:ignore></div>
                                    @error('vendor_arbitrators') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2">Categories:</label>
                                    <div id="categories" wire:ignore></div>
                                    @error('vendor_categories') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                             
                                <div class="col-md-6 fv-row fv-plugins-icon-container" >
									<label class="required fs-6 fw-semibold mb-2">Sub Categories:</label>
                                    <div id="sub_categories" wire:ignore></div>
                                    @error('vendor_sub_categories') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                
                                {{-- <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="fs-6 fw-semibold mb-2">Min Case Value</label>
                                    <input type="number" step="0.01" class="form-control form-control-solid" placeholder="Enter Min Case Value" wire:model="min_case_value" >
                                    @error('min_case_value') <span class="text-danger">{{$message}}</span> @enderror
                                </div> --}}
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="fs-6 fw-semibold mb-2">Cases Won</label>
                                    <input type="number"  class="form-control form-control-solid" placeholder="Enter Cases Won" wire:model="cases_won" >
                                    @error('cases_won') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                
                                <div class="col-md-12 fv-row fv-plugins-icon-container">
									<label class=" fs-6 fw-semibold mb-2">Descrpition</label>
                                    <textarea wire:model="about" class="form-control form-control-solid" placeholder="Write Descrpition" cols="30" rows="3"></textarea>
                                    @error('about') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2">Certificate</label>
                                    <input type="file" wire:model="certificate" class="form-control" >
                                    @error('certificate') <span class="text-danger">{{$message}}</span> @enderror

                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2">License</label>
                                    <input type="file" wire:model="license" class="form-control " >
                                    @error('license') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                {{-- Picture --}}
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-semibold mb-3">
                                        <span>Upload Picture</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" aria-label="Allowed file types: png, jpg, jpeg." data-kt-initialized="1"></i>
                                    </label>
                                    <div class="mt-1"> 
                                        <div class="image-input image-input-outline image-input-placeholder image-input-empty image-input-empty" data-kt-image-input="true">
                                            <!--begin::Preview existing avatar-->
                                           
                                            <div class="image-input-wrapper w-100px h-100px" style="background-image: url('{{($image!=null)?$image->temporaryUrl():asset('uploads/blank2.jpg')}} ')"></div>
                                        
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
							<div class="text-center"> 
								<a href="{{ url('admin/vendors') }}"  id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
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
    @include('components.vendor-script')
@endsection


</div>
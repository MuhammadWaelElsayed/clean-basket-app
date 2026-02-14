
@section('accountActive','active')



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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Change Email or Password</h1>
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
                        <li class="breadcrumb-item text-muted">Account</li>
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
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                          <button class="nav-link {{($tab=='email')?'active':''}} fs-lg-5" wire:click="setTab('email')" id="nav-profile-tab"  type="button" >
                            <i class="fas fa-envelope"></i> Change Email</button>
                          <button class="nav-link fs-lg-5 {{($tab=='password')?'active':''}}" wire:click="setTab('password')" id="nav-contact-tab"  type="button" >
                            <i class="fas fa-key"></i> Change Password </button>
                        </div>
                    </nav>
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
							<!--begin::Heading-->
							<div class="mb-8 text-center">
							</div>
							<!--end::Heading-->
							<div class="row g-9 mb-8">
                                <div class="col-md-12">
                                    <div class="tab-content mt-5" id="nav-tabContent">
                                        {{-- Tab 1- Order --}}
                                        @if ($tab=="email")
                                            <div class="tab-pane fade show active"  >
                                                <form id="kt_modal_new_target_form" enctype="multipart/form-data" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="updateEmail()">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="d-flex flex-column  fv-row fv-plugins-icon-container">
                                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                                            <span class="required">Email</span>
                                                        </label>
                                                        <input type="email" class="form-control form-control-solid" placeholder="Enter Email" wire:model="email">
                                                        @error('email') <span class="text-danger">{{$message}}</span> @enderror
                                                    </div>
                                                    <button type="submit" class="btn btn-base my-5" name="change_email" >Update Email</button>
                                                </form>
                                            </div>
                                        @elseif ($tab=="password")
                                            <div class="tab-pane fade show active"  >
                                                <form id="kt_modal_new_target_form" enctype="multipart/form-data" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="updatePassword()">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-md-12 fv-row fv-plugins-icon-container">
                                                            <label class="required fs-6 fw-semibold mb-2">Old(Current) Password</label>
                                                            <input type="password"  class="form-control form-control-solid" placeholder="Enter Your Old Password" wire:model="old_password">
                                                            @error('old_password') <span class="text-danger">{{$message}}</span> @enderror
                                                            
                                                            <br>
                                                        </div>
                                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                                            <label class="required fs-6 fw-semibold mb-2">New Password</label>
                                                            <input type="password"  class="form-control form-control-solid"  placeholder="Enter New Password" wire:model="new_password">
                                                            @error('new_password') <span class="text-danger">{{$message}}</span> @enderror
                                                        
                                                        </div>
                                                        <div class="col-md-6 fv-row fv-plugins-icon-container">
                                                            <label class="required fs-6 fw-semibold mb-2">Confirm Password</label>
                                                            <input type="password"  class="form-control form-control-solid"  placeholder="Enter Confirm Password" wire:model="confirm_password" >
                                                            @error('confirm_password') <span class="text-danger">{{$message}}</span> @enderror
                                                    
                                                        </div>
                                                    </div>
                                                    <div class="text-left mt-5">
                                                        <button type="submit" id="kt_modal_new_target_submit"  name="change_password" class="btn btn-base">
                                                            <span class="indicator-label">Change Password</span>
                                                            <span class="indicator-progress">Please wait...
                                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                        </button>
                                                    </div>
                                                    <!--end::Actions-->
                                                </form>
                                            </div>
                                        @endif
                                        
                                        
                                    </div>
                                
							</div>
						
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


@section('codesActive','active')
@section('dataShow','show')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{($this->eId)?'Edit':'Add New'}} Promo Code</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" wire:navigate class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/codes') }}" wire:navigate class="text-muted text-hover-primary">Promo Codes</a>
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
                    <div class="card-body pt-10">
                        <!--begin::Form-->  
                        <form id="kt_modal_new_target_form" enctype="multipart/form-data"  method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="store()">
                            @csrf
                            <div class="row fv-plugins-icon-container">
                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10">
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Title</span>
                                    </label>
                                    <!--end::Label-->
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Promo Code Title" wire:model.live="title" >
                                    @error('title') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                               

                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10">
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Promo Code</span>
                                    </label>
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Promo Code" wire:model.live="code">
                                    @error('code') <span class="text-danger">{{$message}}</span> @enderror

                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10">
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Min Order</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-solid" onchange="document.getElementById('disc_amount').max=this.value" placeholder="Enter Min Order Value" wire:model.live="min_order" >
                                    @error('min_order') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10">
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Max Order</span>
                                    </label>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-solid"  placeholder="Enter Max Order Value" wire:model.live="max_order" >
                                    @error('max_order') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                

                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" >
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Discount Type</span>
                                    </label>
                                    <select class="form-select form-control-solid"  required="" wire:model.live="promo_type" >
										    <option value="Amount" >Amount</option>
										    <option value="Percentage" >Percentage</option>
									</select>
                                </div>
                                @if ($promo_type=="Percentage")
                                    <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" id="show_percentage" >
                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                            <span class="required">Discount Percentage</span>
                                        </label>
                                        <div class="input-group mb-3">
                                            <input type="number" step="0.01" min="1" class="form-control form-control-solid" placeholder="Enter Discounted Percentage" wire:model.live="discount_percentage" value="">
                                            <span class="input-group-text" id="basic-addon2">%</span>
                                        </div>
                                        @error('discount_percentage') <span class="text-danger">{{$message}}</span> @enderror

                                    </div>
                                @elseif($promo_type=="Amount")
                                    <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" id="show_amount">
                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                            <span class="required">Discounted Amount</span>
                                        </label>
                                        <input type="number" step="0.01" min="1" id="disc_amount" class="form-control form-control-solid" placeholder="Enter Discounted Amount" wire:model.live="discounted_amount" value="">
                                        @error('discounted_amount') <span class="text-danger">{{$message}}</span> @enderror
                                        
                                    </div>
                                @endif
                                
                                
                              
                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10">
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">Expiry Type</span>
                                    </label>
                                    <select class="form-select form-control-solid" onchange="changeExpiry(this.value)"  required="" wire:model.live="expiry">
										    <option value="" data-select2-id="select2-data-6-qid1">Select Expiry</option>
										    <option value="COUNT">COUNT</option>
										    <option value="DATE">DATE</option>
									</select>
                                    @error('expiry') <span class="text-danger">{{$message}}</span> @enderror

                                </div>
                                @if ($expiry=="COUNT")
                                    <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" id="show_count">
                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                            <span class="required">COUNT</span>
                                        </label>
                                        <input type="number" min="1" class="form-control form-control-solid" placeholder="Enter COUNT Times" wire:model.live="count" value="">
                                        @error('count') <span class="text-danger">{{$message}}</span> @enderror

                                    </div>
                                @elseif($expiry=="DATE")
                                    <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" id="show_date" >
                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                            <span class="required">Date</span>
                                        </label>
                                        <div class="position-relative d-flex align-items-center">
                                            <!--begin::Icon-->
                                            <span class="svg-icon svg-icon-2 position-absolute mx-4" style="z-index: 1">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path opacity="0.3" d="M21 22H3C2.4 22 2 21.6 2 21V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5V21C22 21.6 21.6 22 21 22Z" fill="currentColor"></path>
                                                    <path d="M6 6C5.4 6 5 5.6 5 5V3C5 2.4 5.4 2 6 2C6.6 2 7 2.4 7 3V5C7 5.6 6.6 6 6 6ZM11 5V3C11 2.4 10.6 2 10 2C9.4 2 9 2.4 9 3V5C9 5.6 9.4 6 10 6C10.6 6 11 5.6 11 5ZM15 5V3C15 2.4 14.6 2 14 2C13.4 2 13 2.4 13 3V5C13 5.6 13.4 6 14 6C14.6 6 15 5.6 15 5ZM19 5V3C19 2.4 18.6 2 18 2C17.4 2 17 2.4 17 3V5C17 5.6 17.4 6 18 6C18.6 6 19 5.6 19 5Z" fill="currentColor"></path>
                                                    <path d="M8.8 13.1C9.2 13.1 9.5 13 9.7 12.8C9.9 12.6 10.1 12.3 10.1 11.9C10.1 11.6 10 11.3 9.8 11.1C9.6 10.9 9.3 10.8 9 10.8C8.8 10.8 8.59999 10.8 8.39999 10.9C8.19999 11 8.1 11.1 8 11.2C7.9 11.3 7.8 11.4 7.7 11.6C7.6 11.8 7.5 11.9 7.5 12.1C7.5 12.2 7.4 12.2 7.3 12.3C7.2 12.4 7.09999 12.4 6.89999 12.4C6.69999 12.4 6.6 12.3 6.5 12.2C6.4 12.1 6.3 11.9 6.3 11.7C6.3 11.5 6.4 11.3 6.5 11.1C6.6 10.9 6.8 10.7 7 10.5C7.2 10.3 7.49999 10.1 7.89999 10C8.29999 9.90003 8.60001 9.80003 9.10001 9.80003C9.50001 9.80003 9.80001 9.90003 10.1 10C10.4 10.1 10.7 10.3 10.9 10.4C11.1 10.5 11.3 10.8 11.4 11.1C11.5 11.4 11.6 11.6 11.6 11.9C11.6 12.3 11.5 12.6 11.3 12.9C11.1 13.2 10.9 13.5 10.6 13.7C10.9 13.9 11.2 14.1 11.4 14.3C11.6 14.5 11.8 14.7 11.9 15C12 15.3 12.1 15.5 12.1 15.8C12.1 16.2 12 16.5 11.9 16.8C11.8 17.1 11.5 17.4 11.3 17.7C11.1 18 10.7 18.2 10.3 18.3C9.9 18.4 9.5 18.5 9 18.5C8.5 18.5 8.1 18.4 7.7 18.2C7.3 18 7 17.8 6.8 17.6C6.6 17.4 6.4 17.1 6.3 16.8C6.2 16.5 6.10001 16.3 6.10001 16.1C6.10001 15.9 6.2 15.7 6.3 15.6C6.4 15.5 6.6 15.4 6.8 15.4C6.9 15.4 7.00001 15.4 7.10001 15.5C7.20001 15.6 7.3 15.6 7.3 15.7C7.5 16.2 7.7 16.6 8 16.9C8.3 17.2 8.6 17.3 9 17.3C9.2 17.3 9.5 17.2 9.7 17.1C9.9 17 10.1 16.8 10.3 16.6C10.5 16.4 10.5 16.1 10.5 15.8C10.5 15.3 10.4 15 10.1 14.7C9.80001 14.4 9.50001 14.3 9.10001 14.3C9.00001 14.3 8.9 14.3 8.7 14.3C8.5 14.3 8.39999 14.3 8.39999 14.3C8.19999 14.3 7.99999 14.2 7.89999 14.1C7.79999 14 7.7 13.8 7.7 13.7C7.7 13.5 7.79999 13.4 7.89999 13.2C7.99999 13 8.2 13 8.5 13H8.8V13.1ZM15.3 17.5V12.2C14.3 13 13.6 13.3 13.3 13.3C13.1 13.3 13 13.2 12.9 13.1C12.8 13 12.7 12.8 12.7 12.6C12.7 12.4 12.8 12.3 12.9 12.2C13 12.1 13.2 12 13.6 11.8C14.1 11.6 14.5 11.3 14.7 11.1C14.9 10.9 15.2 10.6 15.5 10.3C15.8 10 15.9 9.80003 15.9 9.70003C15.9 9.60003 16.1 9.60004 16.3 9.60004C16.5 9.60004 16.7 9.70003 16.8 9.80003C16.9 9.90003 17 10.2 17 10.5V17.2C17 18 16.7 18.4 16.2 18.4C16 18.4 15.8 18.3 15.6 18.2C15.4 18.1 15.3 17.8 15.3 17.5Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                            <input class="form-control form-control-solid  ps-12" wire:model="daterange"  id="daterange" placeholder="Select From-To Date">
                                        </div>
                                        @error('daterange') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                @endif

                                <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" >
                                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                        <span class="required">User Type</span>
                                    </label>
                                    <select class="form-select form-control-solid" onchange="showUserType(this.value)" required="" wire:model.live="user_type" >
										    <option value="All" >All Users</option>
										    <option value="Selected" >Selected User</option>
									</select>
                                    @error('user_type') <span class="text-danger">{{$message}}</span> @enderror

                                </div>

                                @if ($eId < 1 && $user_type=='Selected')
                                    <div class="col-md-6 fv-row fv-plugins-icon-container mb-10" data-select2-id="select2-data-10-cc35">
                                        <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                            <span class="required">Select Users</span>
                                        </label>
                                        <div id="users" wire:ignore></div>
                                        @error('users') <span class="text-danger">{{$message}}</span> @enderror
                                    </div>
                                @endif
                                
                            </div>
					
						
						
							<!--begin::Actions-->
							<div class="text-center"> 
								<a href="{{ url('admin/codes') }}"  id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
								<button type="submit" id="kt_modal_new_target_submit" class="btn btn-base">
									<span class="indicator-label">Submit</span>
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
{{-- @dd($users_arr) --}}
{{-- @endsection --}}
@section('scripts')

    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>

	<link  data-navigate-once rel="stylesheet" href="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.css" integrity="sha256-KqTuc/vUgQsb5EMyyxWf62qYinMUXDpWELyNx+cCUr0=" crossorigin="anonymous">
    <script data-navigate-once src="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.js" integrity="sha256-Gsn2XyJGdUeHy0r4gaP1mJy1JkLiIWY6g6hJhV5UrIw=" crossorigin="anonymous"></script>

    <script>
        // VirtualSelect.init({
        //     ele: '#users',
        //     options: <?=json_encode($users_arr)?>,
        //     placeholder:'{{__("Select Users")}}',
        //     additionalClasses:'filter-field',
        //     search:true,
        //     multiple:true
        // });
       
        function showUserType(type){
            if (type=='Selected') {
                setTimeout(() => {
                    VirtualSelect.init({
                        ele: '#users',
                        options: <?=json_encode($users_arr)?>,
                        placeholder:'{{__("Select Users")}}',
                        additionalClasses:'filter-field',
                        search:true,
                        multiple:true
                    });
                    $('#users').on('change', function (e) {
                        @this.set('users', $(this).val());
                    });
                }, 800);
                
            }
            
        }

        function changeExpiry(expiry){
            if (expiry=='DATE') {
                setTimeout(() => {
                    flatpickr('#daterange',
                    {
                        mode: "range",
                        // minDate: "today",
                        dateFormat: "Y-m-d",
                    });
                }, 800);
                
            }
            
        }
    </script>

<link rel="stylesheet" href="{{ asset('node_modules/flatpickr/dist/flatpickr.min.css') }}">
<script src="{{ asset('node_modules/flatpickr/dist/flatpickr.min.js') }}"></script>

<script>
    $(function() {
    
        flatpickr('#daterange',
        {
            mode: "range",
            // minDate: "today",
            dateFormat: "Y-m-d",
        });

    });
    </script>
@endsection
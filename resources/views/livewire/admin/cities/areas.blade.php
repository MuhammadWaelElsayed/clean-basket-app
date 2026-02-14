
@section('citiesActive','active')
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0"> City Areas</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::City-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <!--end::City-->
                        <!--begin::City-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::City-->
                        <!--begin::City-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/cities') }}" class="text-muted text-hover-primary">Cities</a>
                        </li>
                        <!--end::City-->
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

                        <h4 class="mt-3">City: {{$cityName}}
                            <button wire:click="addNew()" class="btn btn-base btn-sm mx-3" type="button">+ New Area</button></h4>
                        <!--begin::Form-->  
                        <form id="kt_modal_new_target_form" enctype="multipart/form-data"  method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" wire:submit.prevent="updateData()">
                            @csrf
							<!--begin::Input group-->
                            @foreach ($areas as $key => $item)
                            <div class="row g-9 my-1">
								<!--begin::Col-->
                                <div class="col-md-5 fv-row fv-plugins-icon-container">
                                    <input type="hidden" wire:model.live="areas.{{$key}}.id">
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Area Name" wire:model.live="areas.{{$key}}.name" >
                                    @error('name') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-5 fv-row fv-plugins-icon-container">
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Area Name Arabic" wire:model.live="areas.{{$key}}.name_ar" >
                                    @error('name_ar') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-2 fv-row fv-plugins-icon-container"> 
                                    <button class="btn btn-danger btn-sm mt-1" wire:click="removeRow({{$key}},{{$item['id']}})" type="button"> - Remove</button>
                                </div>
                                
                            </div>
                            @endforeach
							
						
                         <div class="row g-9 my-3">
						
							<!--begin::Actions-->
							<div class="text-center"> 
								<a href="{{ url('admin/cities') }}" wire:navigate  id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
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

{{-- @endsection --}}
@section('scripts')

    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>

@endsection
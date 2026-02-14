
@section('pagesActive','active')
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{($this->eId)?'Edit':'Add New'}} Page</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Page-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <!--end::Page-->
                        <!--begin::Page-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Page-->
                        <!--begin::Page-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/pages') }}" class="text-muted text-hover-primary">Pages</a>
                        </li>
                        <!--end::Page-->
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
								<!--begin::Col-->
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2"> Title</label>
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Page Title" wire:model="title" >
                                    @error('title') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-6 fv-row fv-plugins-icon-container">
									<label class="required fs-6 fw-semibold mb-2"> Title Arabic</label>
                                    <input type="text" class="form-control form-control-solid" placeholder="Enter Page Title Arabic" wire:model="title_ar" >
                                    @error('title_ar') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2"> Content</label>
                                    <textarea id="editor" wire:model.defer="content" class="form-control form-control-solid" placeholder="Write Page Content"></textarea>
                                    @error('content') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                <div class="col-md-12 fv-row fv-plugins-icon-container" wire:ignore>
									<label class="required fs-6 fw-semibold mb-2"> Content Arabic</label>
                                    <textarea id="editor2" wire:model.defer="content_ar" class="form-control form-control-solid" placeholder="Write Page Content"></textarea>
                                    @error('content_ar') <span class="text-danger">{{$message}}</span> @enderror
                                </div>
                                
                            </div>
						
                         <div class="row g-9 my-3">
						
							<!--begin::Actions-->
							<div class=""> 
								<a href="{{ url('admin/pages') }}"  id="kt_modal_new_target_cancel" class="btn btn-light me-3">Back</a>
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
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
        CKEDITOR.replace('editor', {
            on: {
                change: function () {
                    @this.set('content', CKEDITOR.instances.editor.getData());
                }
            }
        });
        CKEDITOR.replace('editor2', {
            on: {
                change: function () {
                    @this.set('content_ar', CKEDITOR.instances.editor.getData());
                }
            }
        });

  
</script>

    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>

@endsection
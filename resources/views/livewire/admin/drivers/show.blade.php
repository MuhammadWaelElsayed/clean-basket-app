
@section('vendorsActive','active')

<style>
    th{
        font-weight: bold !important;
    }
</style>
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Partner Details</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted"> 
                            <a href="{{url('admin/dashboard') }}" class="text-muted text-hover-primary">Home /</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item ">
                            <a href="{{url('admin/vendors') }}" class="text-muted text-hover-primary">Partners /</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">Details</li>
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
                    {{-- <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                          <button class="nav-link {{($tab=='email')?'active':''}} fs-lg-5" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
                            <i class="fas fa-envelope"></i>Company Details</button>
                          <button class="nav-link fs-lg-5 {{($tab=='password')?'active':''}}" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-v" aria-selected="false">
                            <i class="fas fa-key"></i> Company Vendors </button>
                        </div>
                    </nav> --}}
                    <div class="card-body pt-0">
                            <a href="{{ url('admin/partners') }}" id="kt_modal_new_target_cancel" class="btn btn-light m-3">< Back</a>
                    

                        <!--begin::Form--> 
                        <div class="row p-5">
                            
                            <div class="col-md-9">
                                <table class="table table-striped fs-6">
                                    <tr>
                                        <th>Picture</th>
                                        <td> <img src="{{$item->picture}}" class="w-70px  img-responsive shadow" alt=""> </td>
                                    </tr>
                                    <tr>
                                        <th>Owner Name</th>
                                        <td> {{$item->first_name.' '.$item->last_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Business Name</th>
                                        <td> {{$item->business_name}}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td> {{$item->email}} </td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td> {{$item->phone}} </td>
                                    </tr>
                                  
                                    <tr>
                                        <th>City</th>
                                        <td>{{($item->city)?$item->city->name :'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th>Area</th>
                                        <td>{{($item->area)?$item->area->name:'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th> About</th>
                                        <td>{{$item->about}}</td>
                                    </tr>
                                  
                                
                                </table>

                            </div>
                            
                        </div>
                        <!--end::Form-->
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

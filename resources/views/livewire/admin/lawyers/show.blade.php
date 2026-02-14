
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Vendor Details</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted"> 
                            <a href="{{($partner==null)?url('admin/dashboard'):route('partner.dashboard') }}" class="text-muted text-hover-primary">Home /</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item ">
                            <a href="{{($partner==null)?url('admin/vendors'):route('partner.vendors') }}" class="text-muted text-hover-primary">vendors /</a>
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
                        @if ($partner==null)
                            <a href="{{ url('admin/vendors') }}" id="kt_modal_new_target_cancel" class="btn btn-light m-3">< Back</a>
                        @else
                         <a href="{{ route('partner.vendors') }}" id="kt_modal_new_target_cancel" class="btn btn-light m-3">< Back</a>
                        @endif

                        <!--begin::Form--> 
                        <div class="row p-5">
                            
                            <div class="col-md-9">
                                <table class="table table-striped fs-6">
                                    <tr>
                                        <th>Picture</th>
                                        <td> <img src="{{$item->picture}}" class="w-70px  img-responsive shadow" alt=""> </td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td> {{$item->name}}</td>
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
                                        <th>Country</th>
                                        <td>{{($item->country)?$item->country:'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th>City</th>
                                        <td>{{($item->city)?$item->city:'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th>Nationality</th>
                                        <td>{{($item->nationality)?$item->nationality:'N/A'}}</td>
                                    </tr>
                                    <tr>
                                        <th>Vendor Languages</th>
                                        <td>
                                            @if ($item->languages)
                                                @foreach ($item->languages as $lang)
                                                    <span class="badge badge-dark text-bold fs-6">{{$lang}}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Legal Consultant</th>
                                        <td> 
                                            @if ($item->arbitrators!=null)
                                                @foreach ($item->arbitrators as $arbitrator)
                                                    <span class="badge bg-primary">{{$arbitrator->name}}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Jurisdictions</th>
                                        <td> 
                                            @if ($item->jurisdictions!=null)
                                                @foreach ($item->jurisdictions as $jur)
                                                    <span class="badge bg-primary">{{$jur->name}}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Categories</th>
                                        <td>
                                            @if ($item->categories)
                                                @foreach ($item->categories as $cat)
                                                    <span class="badge badge-success text-bold fs-6">{{$cat->name}}</span>
                                                @endforeach
                                            @endif
                                            
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Sub Categories</th>
                                        <td>
                                            @foreach ($item->subCategories as $cat)
                                                <span class="badge badge-warning text-bold fs-6">{{$cat->name}}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                    {{-- <tr>
                                        <th>Min Cases</th>
                                        <td>
                                               <b> {{env('CURRENCY')}} {{number_format($item->min_case_value,2)}}</b>
                                        </td>
                                    </tr> --}}
                                    <tr>
                                        <th>Cases Won</th>
                                        <td>
                                                <span class="badge badge-danger text-bold fs-6">{{$item->cases_won}}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th> About</th>
                                        <td>{{$item->about}}</td>
                                    </tr>
                                    <tr>
                                        <th> Certificate</th>
                                        <td> <a href="{{$item->certificate}}" target="_blank"><i class="fas fa-file-alt"></i> View Certificate</a> </td>
                                    </tr>
                                    <tr>
                                        <th> License</th>
                                        <td><a href=" {{$item->license}}" target="_blank"><i class="fas fa-file-alt"></i> View License</a></td>
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

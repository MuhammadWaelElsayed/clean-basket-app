
@section('codesActive','active')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Promo Details</h1>
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
                            <a href="{{url('admin/codes') }}" class="text-muted text-hover-primary">Promos /</a>
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
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link fs-lg-5 active" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="true">
                                <i class="fas fa-percentage"></i> Discount Details
                            </button>
                            <button class="nav-link fs-lg-5" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-users" type="button" role="tab" aria-controls="nav-users" aria-selected="false" tabindex="-1">
                                <i class="fas fa-users"></i> Users
                         </button>
                        </div>
                    </nav>
                    <div class="card-body pt-0">
                        <!--begin::Form--> 
                        <div class="row p-5">
                            <div class="tab-content mt-5" id="nav-tabContent">

                                <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" >
                                    <div class="col-md-9">
                                        <table class="table table-striped fs-6">
                                            <tr>
                                                {{-- @dd($code) --}}
                                                <th>Title</th>
                                                <td> {{$code->title}}</td>
                                            </tr>
                                            <tr>
                                                <th>Promo Code</th>
                                                <td> {{$code->code}}</td>
                                            </tr>
                                            <tr>
                                                <th>Order Amount</th>
                                                <td> 
                                                    Min: {{env('CURRENCY')}} {{$code->min_order}}  <br>
                                                    Max: {{env('CURRENCY')}} {{$code->max_order}} 
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Promo Type</th>
                                                <td> 
                                                    {{$code->promo_type}}  <br>
                                                    @if ($code->promo_type == 'Amount')
                                                    {{env('CURRENCY')}} {{$code->discounted_amount}} 
                                                    @else
                                                        {{$code->discount_percentage}}%
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Expiry</th>
                                                <td>
                                                    {{$code->expiry}}: 
                                                    @if ($code->expiry == 'COUNT')
                                                    {{$code->count}} 
                                                    @else
                                                        <br>
                                                        {{date('d M, Y',strtotime($code->from_date))}} - {{date('d M, Y',strtotime($code->to_date))}}
                                                    @endif    
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>User Type</th>
                                                <td> 
                                                    {{$code->user_type}}  
                                                   
                                                </td>
                                            </tr>
                                        </table>

                                    </div>
                                </div>

                                <div class="tab-pane fade "  id="nav-users" role="tabpanel" aria-labelledby="nav-users-tab" >
                                    <div class="col-md-9">
                                        <table class="table table-striped fs-6">
                                            <tr>
                                                <th>User</th>
                                                <th>Promo Code Satus</th>
                                            </tr>
                                        
                                            @foreach ($users as $item)
                                                <tr>
                                                    <td>{{$item->user->first_name.' '.$item->user->last_name}} ({{$item->user->phone}})</td>
                                                    <td> <span class="fs-6 badge badge-{{( $item->is_used==1)?'danger':'success'}}"> {{( $item->is_used==1)?'Used':'Available'}} </span> </td>
                                                </tr>
                                            @endforeach
                                        
                                        
                                        
                                        </table>

                                    </div>
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

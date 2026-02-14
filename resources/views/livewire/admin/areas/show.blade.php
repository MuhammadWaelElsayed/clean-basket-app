@extends('admin.layout.app')

@section('itemsActive','active')

@section('main')
<style>
    th{
        font-weight: bold;
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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Surprise Bag Details</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted"> 
                            <a href="{{ url('admin/dashboard') }}" class="text-muted text-hover-primary">Home /</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item ">
                            <a href="{{ url('admin/items') }}" class="text-muted text-hover-primary">Surprise Bags /</a>
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
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                    <a href="{{ url('admin/items') }}" id="kt_modal_new_target_cancel" class="btn btn-light m-3">< Back</a>

                        <!--begin::Form--> 
                        <div class="row p-5">
                            <div class="col-md-3"> 
                                {{-- {{dd($item)}} --}}
                                <h3>{{$item->name}}</h3>
                                <img src="{{$item->images[0]}}" class="w-100 img-responsive shadow" alt="">  
                            </div>
                            <div class="col-md-9">
                                <table class="table table-striped fs-6">
                                    <tr>
                                        <th>Category</th>
                                        <td><span class="badge badge-light-success text-bold fs-6">{{$item->category->name}}</span></td>
                                    </tr>
                                    <tr>
                                        <th> Description</th>
                                        <td>{{$item->description}}</td>
                                    </tr>
                                    @foreach ($item->properties as $property)
                                    <tr>
                                        <th>{{$property->property->name}} </th>
                                        <td> {{$property->value}} </td>
                                    </tr>
                                    @endforeach
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
@endsection
@section('scripts')
<link href="{{asset('plugins/custom/fullcalendar/fullcalendar.item.css')}}" rel="stylesheet" type="text/css" />

    <script src="{{asset('plugins/custom/fullcalendar/fullcalendar.item.js') }}"></script>

    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.item.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>
@endsection
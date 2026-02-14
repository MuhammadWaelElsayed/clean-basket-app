
@section('partnersActive','active')

<div class="app-main flex-column flex-row-fluid" id="kt_app_main" data-select2-id="select2-data-kt_app_main">

<style>
    th{
        font-weight: bold !important;
    }
</style>
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
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link {{($tab=='details')?'active':''}} fs-lg-5"  type="button"  wire:click="$set('tab','details')">
                                <i class="fas fa-building"></i> Partner Details
                            </button>
                            <button class="nav-link fs-lg-5 {{($tab=='drivers')?'active':''}}"  type="button" wire:click="$set('tab','drivers')">
                                <i class="fas fa-motorcycle"></i> Drivers
                            </button>
                            <button class="nav-link fs-lg-5 {{($tab=='orders')?'active':''}}"  type="button" wire:click="$set('tab','orders')">
                                <i class="fas fa-cubes"></i> Orders
                            </button>
                        </div>
                    </nav>

                    <div class="card-body pt-0">
                            {{-- <a href="{{ url('admin/partners') }}" id="kt_modal_new_target_cancel" class="btn btn-light m-3">< Back</a> --}}


                        <!--begin::Form-->
                        @if ($tab=='details')
                            <div class=" table-responsive">
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
                                    {{-- <tr>
                                        <th>Area</th>
                                        <td>{{($item->area)?$item->area->name:'N/A'}}</td>
                                    </tr> --}}
                                    <tr>
                                        <th> About</th>
                                        <td>{{$item->about}}</td>
                                    </tr>
                                    <tr>
                                        <th> Commission</th>
                                        <td>{{$item->commission}}%</td>
                                    </tr>
                                    <tr>
                                        <th> Area</th>
                                        <td>
                                            <p>{{$item->location}} </p>
                                            <div style="width: 100%; height: 300px" id="map"></div>
                                        </td>
                                    </tr>


                                </table>

                            </div>
                        @elseif($tab=="drivers")
                            <div class=" table-responsive">
                                <table class="table align-middle table-hover fs-6 gy-5 " id="kt_drivers_table">
                                    <thead>
                                        <!--begin::Table row-->
                                        <tr class="text-start text-gray-600 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2 sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 29.8906px;">
                                            # </th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_drivers_table">Picture</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_drivers_table">Name</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_drivers_table">Phone</th>
                                            <th class="min-w-75px sorting" tabindex="0" aria-controls="kt_drivers_table" >Status</th>
                                            <th class="min-w-125px "aria-sort="ascending">Created Date</th>
                                        </tr>
                                        <!--end::Table row-->
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600 fs-6">

                                        @foreach ($drivers as $key => $item)
                                        {{-- {{dd($item)}} --}}
                                            <tr class="odd">
                                                <!--begin::Checkbox-->
                                                <td>{{$key+1 }} </td>

                                                <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">
                                                    <img src="{{$item->picture}}" width="50" height="50" class="rounded-circle" alt="driver">
                                                </td>
                                                <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">{{$item->name}} </a> </td>
                                                <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">{{$item->phone}} </a> </td>

                                                <td><button wire:click="activeInactive({{$item->id}},{{$item->status}})"  class="activeInactive p-0 btn btn-transparent"> <span class="badge badge-light-{{($item->status==1)?'success':'danger' }}"> {{($item->status==1)?'Active':'Inactive' }} </span> </button></td>

                                                <td>{{date('d M, Y',strtotime($item->created_at))}} </td>

                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        @elseif($tab=="orders")
                            <div class=" table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="kt_Orders_table">
                                    <!--begin::Table head-->
                                    <thead>
                                        <!--begin::Table row-->
                                        <tr class="text-start text-gray-600 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2 sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 29.8906px;">
                                            Order # </th>
                                            <th class="min-w-100px sorting" tabindex="0" aria-controls="kt_Orders_table" >Customer</th>
                                            <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_Orders_table" >Pickup</th>
                                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_Orders_table"  >Order Amount</th>
                                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_Orders_table"  >Order Time</th>
                                            <th class="min-w-100px sorting" tabindex="0" aria-controls="kt_Orders_table" >Status</th>
                                        </tr>
                                        <!--end::Table row-->
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="fw-semibold text-gray-600 fs-6">
                                        @foreach ($orders as $item)
                                        {{-- {{dd($item->user)}} --}}
                                            <tr class="odd">
                                                <!--begin::Checkbox-->
                                                <td> #{{$item->order_code}}
                                                    @if ($item->status=="PLACED")
                                                    <small class="badge badge rounded-pill bg-primary m-3">New</small>
                                                    @endif
                                                </td>

                                                <td class="text-center"><img src="{{$item->user->picture}}" class="w-30px h-30px rounded-circle" alt="user">  <br>
                                                    {{$item->user->first_name}}  </td>

                                                <td><p> {{date('d M, Y',strtotime($item->pickup_date))}} <br> {{$item->pickup_time}}  </p></td>


                                                <td>{{env('CURRENCY')}} {{number_format($item->grand_total)}} </td>

                                                <td><small> {{$item->created_at}} </small></td>

                                                <td><button  class=" p-0 btn btn-transparent"> <span class="badge fs-lg-7 badge-{{$statuses[$item->status]}}"> {{$item->status}} </span> </button></td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                    <!--end::Table body-->
                                </table>

                            </div>
                        @endif


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
@section('scripts')
<script async data-navigate-track src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_MAP_KEY')}}&callback=initMap"></script>

<script>
    function initMap() {
        // Initialize the map
        var map = new google.maps.Map(document.getElementById('map'), {
          center: { lat: 24.743, lng: 46.63 },
          zoom: 13
        });

        // Define the polygon coordinates
        var polygonCoords = <?=json_encode($item->areas)?>;
        console.log(polygonCoords);

        // Create the polygon
        var polygon = new google.maps.Polygon({
          paths: polygonCoords,
          strokeColor: '#FF0000',
          strokeOpacity: 0.8,
          strokeWeight: 2,
          fillColor: '#FF0000',
          fillOpacity: 0.35,
          editable: false // Disable editing
        });
        polygon.setMap(map);

        // Adjust the map to fit the polygon
        var bounds = new google.maps.LatLngBounds();
        polygonCoords.forEach(coord => {
          bounds.extend(coord);
        });
        map.fitBounds(bounds);
    }
</script>

@endsection


</div>


@section('requestsActive','active')
@section('basketShow','show')

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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0"> Requests</h1>
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
                        <li class="breadcrumb-item text-muted"> Requests</li>
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
                <div class="card ">
                    <!--begin::Card header-->
                    <div class="border-0 pt-6 ">
                        <!--begin::Card title-->
                        <div class="card-title px-5 ms-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-categories-center position-relative my-1">
                                        <span class="svg-icon svg-icon-1 position-absolute ms-6 mt-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                                                <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                                            </svg>
                                        </span>
                                        <!--end::Svg Icon-->
                                        <input type="text" wire:model.live="search" data-kt-bundle-table-filter="search" class="form-control form-control-solid ps-15"
                                        placeholder="Search Customer Name, Phone.." >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <select wire:model.live="status" class="form-select form-control-solid" >
                                        <option value="">Basket Status</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Delivered">Delivered</option>
                                    </select>
                                </div>
                                    <div class="col-md-4">
                                        <div class="position-relative d-flex align-categories-center">
                                            <!--begin::Icon-->
                                            <!--begin::Svg Icon | path: icons/duotune/general/gen014.svg-->
                                            <span class="svg-icon svg-icon-2 position-absolute mx-4 mt-3" style="z-index: 1">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path opacity="0.3" d="M21 22H3C2.4 22 2 21.6 2 21V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5V21C22 21.6 21.6 22 21 22Z" fill="currentColor"></path>
                                                    <path d="M6 6C5.4 6 5 5.6 5 5V3C5 2.4 5.4 2 6 2C6.6 2 7 2.4 7 3V5C7 5.6 6.6 6 6 6ZM11 5V3C11 2.4 10.6 2 10 2C9.4 2 9 2.4 9 3V5C9 5.6 9.4 6 10 6C10.6 6 11 5.6 11 5ZM15 5V3C15 2.4 14.6 2 14 2C13.4 2 13 2.4 13 3V5C13 5.6 13.4 6 14 6C14.6 6 15 5.6 15 5ZM19 5V3C19 2.4 18.6 2 18 2C17.4 2 17 2.4 17 3V5C17 5.6 17.4 6 18 6C18.6 6 19 5.6 19 5Z" fill="currentColor"></path>
                                                    <path d="M8.8 13.1C9.2 13.1 9.5 13 9.7 12.8C9.9 12.6 10.1 12.3 10.1 11.9C10.1 11.6 10 11.3 9.8 11.1C9.6 10.9 9.3 10.8 9 10.8C8.8 10.8 8.59999 10.8 8.39999 10.9C8.19999 11 8.1 11.1 8 11.2C7.9 11.3 7.8 11.4 7.7 11.6C7.6 11.8 7.5 11.9 7.5 12.1C7.5 12.2 7.4 12.2 7.3 12.3C7.2 12.4 7.09999 12.4 6.89999 12.4C6.69999 12.4 6.6 12.3 6.5 12.2C6.4 12.1 6.3 11.9 6.3 11.7C6.3 11.5 6.4 11.3 6.5 11.1C6.6 10.9 6.8 10.7 7 10.5C7.2 10.3 7.49999 10.1 7.89999 10C8.29999 9.90003 8.60001 9.80003 9.10001 9.80003C9.50001 9.80003 9.80001 9.90003 10.1 10C10.4 10.1 10.7 10.3 10.9 10.4C11.1 10.5 11.3 10.8 11.4 11.1C11.5 11.4 11.6 11.6 11.6 11.9C11.6 12.3 11.5 12.6 11.3 12.9C11.1 13.2 10.9 13.5 10.6 13.7C10.9 13.9 11.2 14.1 11.4 14.3C11.6 14.5 11.8 14.7 11.9 15C12 15.3 12.1 15.5 12.1 15.8C12.1 16.2 12 16.5 11.9 16.8C11.8 17.1 11.5 17.4 11.3 17.7C11.1 18 10.7 18.2 10.3 18.3C9.9 18.4 9.5 18.5 9 18.5C8.5 18.5 8.1 18.4 7.7 18.2C7.3 18 7 17.8 6.8 17.6C6.6 17.4 6.4 17.1 6.3 16.8C6.2 16.5 6.10001 16.3 6.10001 16.1C6.10001 15.9 6.2 15.7 6.3 15.6C6.4 15.5 6.6 15.4 6.8 15.4C6.9 15.4 7.00001 15.4 7.10001 15.5C7.20001 15.6 7.3 15.6 7.3 15.7C7.5 16.2 7.7 16.6 8 16.9C8.3 17.2 8.6 17.3 9 17.3C9.2 17.3 9.5 17.2 9.7 17.1C9.9 17 10.1 16.8 10.3 16.6C10.5 16.4 10.5 16.1 10.5 15.8C10.5 15.3 10.4 15 10.1 14.7C9.80001 14.4 9.50001 14.3 9.10001 14.3C9.00001 14.3 8.9 14.3 8.7 14.3C8.5 14.3 8.39999 14.3 8.39999 14.3C8.19999 14.3 7.99999 14.2 7.89999 14.1C7.79999 14 7.7 13.8 7.7 13.7C7.7 13.5 7.79999 13.4 7.89999 13.2C7.99999 13 8.2 13 8.5 13H8.8V13.1ZM15.3 17.5V12.2C14.3 13 13.6 13.3 13.3 13.3C13.1 13.3 13 13.2 12.9 13.1C12.8 13 12.7 12.8 12.7 12.6C12.7 12.4 12.8 12.3 12.9 12.2C13 12.1 13.2 12 13.6 11.8C14.1 11.6 14.5 11.3 14.7 11.1C14.9 10.9 15.2 10.6 15.5 10.3C15.8 10 15.9 9.80003 15.9 9.70003C15.9 9.60003 16.1 9.60004 16.3 9.60004C16.5 9.60004 16.7 9.70003 16.8 9.80003C16.9 9.90003 17 10.2 17 10.5V17.2C17 18 16.7 18.4 16.2 18.4C16 18.4 15.8 18.3 15.6 18.2C15.4 18.1 15.3 17.8 15.3 17.5Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                           
                                            <input class="form-control form-control-solid  ps-12" wire:model.live.debounce.2000ms="daterange" id="daterange" autocomplete="off" placeholder="Select Date Range">
                                            <button type="button" wire:click="clearFilter()" class="btn {{($search!=='' || $daterange!=='')?'d-block':'d-none'}} btn-sm  btn-light-primary mx-3" >
                                                Clear</button>
                                        </div>
                                    </div>
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--begin::Card title-->
                        <div class="card-toolbar">
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div id="kt_packages_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer"><div class="table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer" id="kt_packages_table">
                            <!--begin::Table head-->
                            <thead>
                                <!--begin::Table row-->
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0 bg-light">
                                    <th class="w-50px px-2 " rowspan="1" colspan="1" aria-label="" >
                                       # </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Basket # </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Partner </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Driver </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Custmoer Name </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Custmoer Mobile </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Address</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table">Status</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_packages_table"> Date</th>
                                </tr>
                                <!--end::Table row-->
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody class="fw-semibold text-gray-600 fs-6">
                              
                                @foreach ($requests as $item)
                                {{-- {{dd($item)}} --}}
                                    <tr class="odd">
                                        <!--begin::Checkbox-->
                                        <td class="px-2">{{ ($requests->currentPage() - 1)  * $requests->perPage() + $loop->iteration }} </td> 
                                        <td>#{{$item->basket_no ?? '--'}} </td>
                                        <td>{{$item->vendor->business_name ?? '--'}} </td>
                                        <td>{{$item->driver->name ?? '--'}} </td>

                                        <td>{{$item->user->first_name ?? '--'}} </td>
                                        <td>{{$item->user->phone ?? '--'}} </td>
                                        <td>
                                            @if ($item->address_type=="House")
                                                House No: {{$item->house_no}}, Street No: {{$item->street_no}}, Area: {{$item->area}} <br>
                                            @else
                                                Appartment: {{$item->appartment}}, Floor: {{$item->floor}}, Building: {{$item->building}}, Area: {{$item->area}} <br>
                                            @endif
                                            
                                       
                                            <a href="https://www.google.com/maps?q={{$item->lat}},{{$item->lng}}" target="_blank" class="btn btn-sm btn-light-primary mt-3">Get Direction</a>
                                         </td>

                                        <td> <span class="badge badge-{{($item->basket_status=='Requested')?'info':'success'}} fs-6">{{$item->basket_status}}</span>  <br>
                                            @if ($item->basket_status=='Requested')
                                                {{-- <button wire:confirm="Are you sure? you want to mark as Delivered" wire:click="markDelivered({{$item->id}})" class="btn btn-sm btn-base mt-3">
                                                    Delivered <i class="fas fa-check text-white fs-5"></i></button> --}}
                                            @endif
                                         </td>
                                        <td>{{date('d M Y, h:i A',strtotime($item->created_at))}} </td>
                                       
                                        <!--end::Action=-->
                                    </tr>
                                @endforeach
                                
                            </tbody>
                            <!--end::Table body-->
                        </table></div> <br>
                        {!! $requests->links() !!}
                        <!--end::Table-->
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


@section('scripts')


<script>
   $(function() {
        flatpickr('#daterange',
        {
            mode: "range",
            dateFormat: "Y-m-d",
        });
    });
    
    var statusBtns= document.getElementsByClassName('approve');
       for (let i = 0; i < statusBtns.length; i++) {
        statusBtns[i].onclick = function(e){
                // var form = this;
                e.preventDefault();
                Swal.fire({
                    // title: 'Are you sure?',
                    title: "You want to change approve this review!",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, approve it!'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        // this.form.submit();
                        Livewire.dispatch('approve');
                    }
                    })
            };
        
       }


       var statusBtns= document.getElementsByClassName('reject');
       for (let i = 0; i < statusBtns.length; i++) {
        statusBtns[i].onclick = function(e){
                // var form = this;
                e.preventDefault();
                Swal.fire({
                    // title: 'Are you sure?',
                    title: "You want to change reject this review!",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, reject it!'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        // this.form.submit();
                        Livewire.dispatch('reject');
                    }
                    })
            };
        
       }
</script>
@endsection
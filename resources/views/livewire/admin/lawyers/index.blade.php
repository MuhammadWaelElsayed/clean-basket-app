
@section('vendorsActive','active')


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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Vendors</h1>
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
                        <li class="breadcrumb-item text-muted">Vendors</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-vendors-center gap-2 gap-lg-3">
                    <!--begin::Export--> 
                    <button type="button" wire:click="exportData" class="btn  btn-sm  btn-light-primary me-3" >
                        <span class="svg-icon svg-icon-2">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect opacity="0.3" x="12.75" y="4.25" width="12" height="2" rx="1" transform="rotate(90 12.75 4.25)" fill="currentColor"></rect>
                                <path d="M12.0573 6.11875L13.5203 7.87435C13.9121 8.34457 14.6232 8.37683 15.056 7.94401C15.4457 7.5543 15.4641 6.92836 15.0979 6.51643L12.4974 3.59084C12.0996 3.14332 11.4004 3.14332 11.0026 3.59084L8.40206 6.51643C8.0359 6.92836 8.0543 7.5543 8.44401 7.94401C8.87683 8.37683 9.58785 8.34458 9.9797 7.87435L11.4427 6.11875C11.6026 5.92684 11.8974 5.92684 12.0573 6.11875Z" fill="currentColor"></path>
                                <path opacity="0.3" d="M18.75 8.25H17.75C17.1977 8.25 16.75 8.69772 16.75 9.25C16.75 9.80228 17.1977 10.25 17.75 10.25C18.3023 10.25 18.75 10.6977 18.75 11.25V18.25C18.75 18.8023 18.3023 19.25 17.75 19.25H5.75C5.19772 19.25 4.75 18.8023 4.75 18.25V11.25C4.75 10.6977 5.19771 10.25 5.75 10.25C6.30229 10.25 6.75 9.80228 6.75 9.25C6.75 8.69772 6.30229 8.25 5.75 8.25H4.75C3.64543 8.25 2.75 9.14543 2.75 10.25V19.25C2.75 20.3546 3.64543 21.25 4.75 21.25H18.75C19.8546 21.25 20.75 20.3546 20.75 19.25V10.25C20.75 9.14543 19.8546 8.25 18.75 8.25Z" fill="currentColor"></path>
                            </svg>
                        </span>
                        Export
                    </button>
               
                    <a href=" {{ url('admin/vendors/create') }}" class="btn btn-base btn-sm" >Add New Vendor</a>
                    <!--end::Primary button-->
                </div>
                <!--end::Actions-->
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
                    <!--begin::Card header-->
                    <div class="mx-5 border-0 pt-6">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <!--begin::Search-->
                            <form action="{{ url('admin/vendors') }}" method="get"> 
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="d-flex align-vendors-center position-relative ">
                                            <span class="svg-icon svg-icon-1 position-absolute ms-6 mt-3">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor"></rect>
                                                    <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                            <!--end::Svg Icon-->
                                            <input type="text" wire:model="search" data-kt-bundle-table-filter="search" class="form-control form-control-solid ps-15"
                                            placeholder="Search Vendor Name, Email, Phone.." >
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <select wire:model="is_company" class="form-control" id="">
                                            <option value="">Filter Vendor/Company</option>
                                            <option value="0">Vendors</option>
                                            <option value="1">Companies</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="position-relative d-flex align-vendors-center">
                                            <span class="svg-icon svg-icon-2 position-absolute mx-4 mt-3" style="z-index: 1">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path opacity="0.3" d="M21 22H3C2.4 22 2 21.6 2 21V5C2 4.4 2.4 4 3 4H21C21.6 4 22 4.4 22 5V21C22 21.6 21.6 22 21 22Z" fill="currentColor"></path>
                                                    <path d="M6 6C5.4 6 5 5.6 5 5V3C5 2.4 5.4 2 6 2C6.6 2 7 2.4 7 3V5C7 5.6 6.6 6 6 6ZM11 5V3C11 2.4 10.6 2 10 2C9.4 2 9 2.4 9 3V5C9 5.6 9.4 6 10 6C10.6 6 11 5.6 11 5ZM15 5V3C15 2.4 14.6 2 14 2C13.4 2 13 2.4 13 3V5C13 5.6 13.4 6 14 6C14.6 6 15 5.6 15 5ZM19 5V3C19 2.4 18.6 2 18 2C17.4 2 17 2.4 17 3V5C17 5.6 17.4 6 18 6C18.6 6 19 5.6 19 5Z" fill="currentColor"></path>
                                                    <path d="M8.8 13.1C9.2 13.1 9.5 13 9.7 12.8C9.9 12.6 10.1 12.3 10.1 11.9C10.1 11.6 10 11.3 9.8 11.1C9.6 10.9 9.3 10.8 9 10.8C8.8 10.8 8.59999 10.8 8.39999 10.9C8.19999 11 8.1 11.1 8 11.2C7.9 11.3 7.8 11.4 7.7 11.6C7.6 11.8 7.5 11.9 7.5 12.1C7.5 12.2 7.4 12.2 7.3 12.3C7.2 12.4 7.09999 12.4 6.89999 12.4C6.69999 12.4 6.6 12.3 6.5 12.2C6.4 12.1 6.3 11.9 6.3 11.7C6.3 11.5 6.4 11.3 6.5 11.1C6.6 10.9 6.8 10.7 7 10.5C7.2 10.3 7.49999 10.1 7.89999 10C8.29999 9.90003 8.60001 9.80003 9.10001 9.80003C9.50001 9.80003 9.80001 9.90003 10.1 10C10.4 10.1 10.7 10.3 10.9 10.4C11.1 10.5 11.3 10.8 11.4 11.1C11.5 11.4 11.6 11.6 11.6 11.9C11.6 12.3 11.5 12.6 11.3 12.9C11.1 13.2 10.9 13.5 10.6 13.7C10.9 13.9 11.2 14.1 11.4 14.3C11.6 14.5 11.8 14.7 11.9 15C12 15.3 12.1 15.5 12.1 15.8C12.1 16.2 12 16.5 11.9 16.8C11.8 17.1 11.5 17.4 11.3 17.7C11.1 18 10.7 18.2 10.3 18.3C9.9 18.4 9.5 18.5 9 18.5C8.5 18.5 8.1 18.4 7.7 18.2C7.3 18 7 17.8 6.8 17.6C6.6 17.4 6.4 17.1 6.3 16.8C6.2 16.5 6.10001 16.3 6.10001 16.1C6.10001 15.9 6.2 15.7 6.3 15.6C6.4 15.5 6.6 15.4 6.8 15.4C6.9 15.4 7.00001 15.4 7.10001 15.5C7.20001 15.6 7.3 15.6 7.3 15.7C7.5 16.2 7.7 16.6 8 16.9C8.3 17.2 8.6 17.3 9 17.3C9.2 17.3 9.5 17.2 9.7 17.1C9.9 17 10.1 16.8 10.3 16.6C10.5 16.4 10.5 16.1 10.5 15.8C10.5 15.3 10.4 15 10.1 14.7C9.80001 14.4 9.50001 14.3 9.10001 14.3C9.00001 14.3 8.9 14.3 8.7 14.3C8.5 14.3 8.39999 14.3 8.39999 14.3C8.19999 14.3 7.99999 14.2 7.89999 14.1C7.79999 14 7.7 13.8 7.7 13.7C7.7 13.5 7.79999 13.4 7.89999 13.2C7.99999 13 8.2 13 8.5 13H8.8V13.1ZM15.3 17.5V12.2C14.3 13 13.6 13.3 13.3 13.3C13.1 13.3 13 13.2 12.9 13.1C12.8 13 12.7 12.8 12.7 12.6C12.7 12.4 12.8 12.3 12.9 12.2C13 12.1 13.2 12 13.6 11.8C14.1 11.6 14.5 11.3 14.7 11.1C14.9 10.9 15.2 10.6 15.5 10.3C15.8 10 15.9 9.80003 15.9 9.70003C15.9 9.60003 16.1 9.60004 16.3 9.60004C16.5 9.60004 16.7 9.70003 16.8 9.80003C16.9 9.90003 17 10.2 17 10.5V17.2C17 18 16.7 18.4 16.2 18.4C16 18.4 15.8 18.3 15.6 18.2C15.4 18.1 15.3 17.8 15.3 17.5Z" fill="currentColor"></path>
                                                </svg>
                                            </span>
                                            <input class="form-control form-control-solid  ps-12" wire:model.debounce.2000ms="daterange" id="daterange" placeholder="Select Date Range">
                                            <button type="button" wire:click="clearFilter()" class="btn {{($is_company!=='' || $search!=='' || $daterange!=='')?'d-block':'d-none'}} btn-sm  btn-light-primary mx-3" >
                                                Clear</button>
                                        </div>
                                    </div>
                                </div>
                                   
                            </form>
                            <!--end::Search-->
                        </div>
                        <div class="card-toolbar">
                            
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div id="kt_vendors_table_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover fs-6 gy-5 " id="kt_vendors_table">
                            <!--begin::Table head-->
                            <thead>
                                <!--begin::Table row-->
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="w-10px pe-2 sorting_disabled" rowspan="1" colspan="1" aria-label="" style="width: 29.8906px;">
                                       # </th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Picture</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Vendor/Company</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Email</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Phone</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Country</th>
                                    {{-- <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Min Case Value</th> --}}
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Cases Won</th>
                                    <th class="min-w-75px sorting" tabindex="0" aria-controls="kt_vendors_table" >Status</th>
                                    <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_vendors_table">Is Approved</th>
                                    <th class="min-w-125px "aria-sort="ascending">Created Date</th>
                                    <th class="min-w-75px "  >Actions</th>
                                </tr>
                                <!--end::Table row-->
                            </thead>
                            <tbody class="fw-semibold text-gray-600 fs-6">
                            
                                @foreach ($vendors as $item)
                                {{-- {{dd($item->images)}} --}}
                                    <tr class="odd">
                                        <!--begin::Checkbox-->
                                        <td>{{ ($vendors->currentPage() - 1)  * $vendors->perPage() + $loop->iteration }} </td> 

                                        <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">
                                            <img src="{{$item->picture}}" width="50" height="50" class="rounded-circle" alt="vendor">
                                            @if ($item->is_company==1)
                                            <br><span class="badge badge-dark m-1">Company</span>
                                                @endif
                                        </td>
                                        <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">{{($item->is_company==1)?$item->company_name:$item->name}} </a> </td>
                                        <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">{{$item->email}} </a> </td>
                                        <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">{{$item->phone}} </a> </td>
                                        <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer">{{($item->country)?$item->country:'N/A'}} </a> </td>
                                       
                                        {{-- <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer"> {{env('CURRENCY')}} {{number_format($item->min_case_value,2)}} </a></td> --}}
                                        <td wire:click="gotoDetails({{$item->id}})" class="cursor-pointer"> {{$item->cases_won}} </a></td>
                                        
                                        <td><button wire:click="activeInactive({{$item->id}},{{$item->status}})"  class="activeInactive p-0 btn btn-transparent"> <span class="badge badge-light-{{($item->status==1)?'success':'danger' }}"> {{($item->status==1)?'Active':'Inactive' }} </span> </button></td>

                                        <td><button wire:click="activeInactive({{$item->id}},{{$item->is_approved}})" class="approveAccount p-0 btn btn-transparent"> <span class="badge badge-light-{{($item->is_approved==1)?'success':'danger' }}"> {{($item->is_approved==1)?'Approved':'Pending' }} </span> </button></td>

                                        <td>{{date('d M, Y',strtotime($item->created_at))}} </td>
                                      
                                        <!--begin::Action=-->
                                        <td class="text-center" >
                                            <p class="d-flex">
                                            <a href="{{ url('admin/vendors/'.$item->id) }}"  class="btn btn-sm h-30px btn-light-primary"><i class="fas fa-eye"></i></a>
                                            <a href="{{ url('admin/vendors/'.$item->id.'/edit') }}" class="btn btn-sm h-30px btn-light-primary"><i class="fas fa-pencil"></i></a>
                                                <button type="button" wire:click="setDel({{$item->id}})" class="delBtn btn btn-sm btn-light-primary" ><i class="fas fa-trash"></i></button>
                                            </p>
                                        </td>
                                        <!--end::Action=-->
                                    </tr>
                                @endforeach
                                
                            </tbody>
                            <!--end::Table body-->
                        </table></div> <br>
                        {!! $vendors->links() !!}
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
            // minDate: "today",
            dateFormat: "Y-m-d",
        });

    });
    </script>
    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>
    <script>
      

        var statusBtns= document.getElementsByClassName('activeInactive');
       for (let i = 0; i < statusBtns.length; i++) {
        statusBtns[i].onclick = function(e){
                // var form = this;
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to change the status!",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        // this.form.submit();
                        Livewire.dispatch('submit-active');
                    }
                    })
            };
        
       }

       var delBtns= document.getElementsByClassName('approveAccount');
       for (let i = 0; i < delBtns.length; i++) {
        delBtns[i].onclick = function(e){
                // var form = this;
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to approved/disapprove account",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    // confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, confirm it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('approveAccount');
                        }
                    })
            };
        
       }
      
       var delBtns= document.getElementsByClassName('delBtn');
       for (let i = 0; i < delBtns.length; i++) {
        delBtns[i].onclick = function(e){
                // var form = this;
                e.preventDefault();
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert it!",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Livewire.dispatch('del-item');
                        }
                    })
            };
        
       }
        
    </script>
@endsection

@section('settingsActive','active')
@section('appShow','show')


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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Settings</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('admin/dashboard') }}" wire:navigate class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Settings</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-slots-center gap-2 gap-lg-3">
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
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link {{($tab=='settings')?'active':''}} fs-lg-5" wire:click="setTab('settings')" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
                                <i class="fas fa-gear"></i> Settings
                            </button>
                            <button class="nav-link {{($tab=='slots')?'active':''}}  fs-lg-5" wire:click="setTab('slots')" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-v" aria-selected="false">
                                <i class="fas fa-boxes"></i> Pickup slots 
                            </button>
                        </div>
                    </nav>
                    <div class="card-body pt-0">
                        <div class="tab-content mt-5" id="nav-tabContent">
                            {{-- Tab 1- Settings --}}
                            @if ($tab=='settings')
                                <div class="tab-pane fade show active" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                                    <form wire:submit.prevent="updateSettings()" method="post">
                                        <div class="row">
                                            @foreach ($settings as $key => $item)
                                            <div class="col-md-6 my-3">
                                                <label for="" class="fs-6 fw-semibold mb-2">{{ucwords(str_replace('_', ' ', $key)) }}
                                                @if ($key=="driver_deliver_radius")
                                                    (Meters)
                                                @elseif($key=="vat")
                                                    (%)
                                                @else
                                                    (AED)
                                                @endif</label>
                                                <input type="text" wire:model="settings.{{$key}}" class="form-control form-control-solid"  id="">
                                            </div>
                                            @endforeach
                                        </div>
                                        <button class="btn btn btn-base mt-3 mx-auto" type="submit">Save Changes</button>
                                    </form>
                                </div>
                            @elseif ($tab=='slots')
                                {{-- Tab 2- Pickup Slots --}}
                                <div class="tab-pane fade show active" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                                    <h4>Pickup Slots  <button class="mx-3 btn btn-sm btn-base" wire:click="addNew()">+ New</button></h4>
                                    <form  method="post" wire:submit.prevent="updateSlots()">
                                        @foreach ($slots as $key => $item)
                                            <div class="row my-5">
                                                <div class="col-md-3">
                                                    <input type="time" class="form-control form-control-solid" wire:model="slots.{{$key}}.from" >
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="time" class="form-control form-control-solid" wire:model="slots.{{$key}}.to" >
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="mx-3 btn btn-sm btn-secondary" wire:click="removeRow({{$key}})">- Remove</button>
                                                </div>
                                            </div>
                                        @endforeach
                                    <button class="btn btn btn-base mt-3 mx-auto" type="submit">Save Changes</button>
                                    </form>
                                </div>
                            @endif

                        </div>
                    </div>
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
    function setDay(day) {
        @this.set('day',day);
    }
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
                    // title: 'Are you sure?',
                    title: "You want to change the status!",
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

       var delBtns= document.getElementsByClassName('delBtn');
       for (let i = 0; i < delBtns.length; i++) {
        delBtns[i].onclick = function(e){
                // var form = this;
                e.preventDefault();
                Swal.fire({
                    // title: 'Are you sure?',
                    title: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    cancelButtonColor: '#5E6278',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        // this.form.submit();
                        Livewire.dispatch('del-item');

                    }
                    })
            };
        
       }
      
        
    </script>
@endsection

@section('dashboardActive','active')


<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Dashboard</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ url('partner/dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">Dashboards</li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <!--begin::Secondary button-->
                    {{-- <a href="#" class="btn btn-sm fw-bold bg-body btn-color-gray-700 btn-active-color-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_create_app">Report</a> --}}
                    <!--end::Secondary button-->
                  
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-fluid">
                <!--begin::Row-->
                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{route('partner.orders')}}" class="card border border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-danger text-uppercase mb-2">
                                            New Orders </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['new_orders']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-star-of-life fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{route('partner.orders')}}" class="card border border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-primary text-uppercase mb-2">
                                            Total Orders </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['total_order']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{route('partner.orders')}}" class="card border border-left-dark shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-dark text-uppercase mb-2">
                                            In-Process Orders </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['pending_orders']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hourglass fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{route('partner.orders')}}" class="card border border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-success text-uppercase mb-2">
                                            Orders Delivered </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['orders_delivered']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-square fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{route('partner.orders')}}"  class="card border border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold  text-warning text-uppercase mb-2">
                                            This Month Orders</div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800">{{$stats['month_orders']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-cubes fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>


                <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                  
                  
                    <div class="col-md-6">
                        <div class="card card-flush overflow-hidden">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <!--begin::Title-->
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Orders Stats</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Orders delivered per month</span>
                                </h3>
                                <!--end::Title-->
                                <!--begin::Toolbar-->
                                <div class="card-toolbar">
                                    
                                </div>
                                <!--end::Toolbar-->
                            </div>
                            <!--end::Header-->
                            <!--begin::Card body-->
                            <div class="card-body d-flex align-items-end ">
                                <!--begin::Chart-->
                                <canvas id="vendor_chart" width="400" height="200"></canvas>
                                <!--end::Chart-->
                            </div>
                            <!--end::Card body-->
                        </div>
                    </div>
                    

                </div>
              
                </div>
                <!--begin::Row-->
                
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
    
</div> 

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script> 
        //  Chart
        getMonthOrders();
        function getMonthOrders() {
            ctx = document.getElementById('vendor_chart').getContext('2d');
            orderChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels:  ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                        {
                            label: 'Monthly Orders',
                            data: {{json_encode($chart_data['month_orders'])}},
                            backgroundColor: [
                                'rgba(93, 173, 168, 0.6)',
                            ],
                            fill: true,
                            borderColor: [
                                'rgba(93, 173, 168, 1)',
                            ],
                            yAxisID: 'y',
                            borderWidth: 1, 
                            barPercentage: 0.6, 
                            borderRadius: 10,
                        },
                ]
            },
            options: {
             responsive: true,
           
            },
        });
            
        }
        // Pie Chart
        function getPieChart() {
            ctx = document.getElementById('pie_chart').getContext('2d');
            orderChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    'Closed Cases ',
                    'Total Cases ',
                ],
                datasets: [{
                    label: 'Order Stats',
                    data: [5, 20],
                    backgroundColor: [
                    'rgba(0, 0, 0, 0.8)',
                    'rgba(67, 67, 67, 0.8)',
                    ],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        // display: false
                    }
                }
            },
            });
            
        }
        document.addEventListener('livewire:load', function () {
            Livewire.on('redirect', function (url) {
                window.location.href = url;
            });
        });

    </script>
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('js/custom/widgets.js') }}"></script>
    

@endsection

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
                            <a href="../../demo1/dist/index.html" class="text-muted text-hover-primary">Home</a>
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
                    {{-- <a wire:click="exportData" class="btn btn-sm fw-bold bg-body btn-color-gray-700 btn-active-color-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_create_app">Report</a> --}}
                  
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
                        <a href="{{ url('admin/drivers') }}" class="card border border-left-dark shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-dark text-uppercase mb-2">
                                            Total Drivers </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['total_drivers']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-truck fs-2x text-gray-500"></i>
                                        {{-- <i class="fas fa-bike fs-2x text-gray-500"></i> --}}
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{ url('admin/partners') }}" class="card border border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold  text-primary text-uppercase mb-2">
                                            Total Partners </div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800">{{$stats['total_vendors']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-building fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{ url('admin/orders') }}" class="card border border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-danger text-uppercase mb-2">
                                            Total Orders </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['total_orders']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-copy fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{ url('admin/users') }}" class="card border border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold  text-success text-uppercase mb-2">
                                            Total Users</div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800">{{$stats['total_users']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-primary text-uppercase mb-2">
                                            New Orders </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['new_orders']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-star-of-life  fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-warning text-uppercase mb-2">
                                            Pending Orders </div>
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
                        <a href="{{url('admin/orders')}}" class="card border border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-success text-uppercase mb-2">
                                            Completed Orders </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{$stats['completed_orders']}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="far fa-check-square fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-dark shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-dark text-uppercase mb-2">
                                            Total Sale </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{env('CURRENCY')}} {{number_format($stats['total_sales'],2)}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-danger text-uppercase mb-2">
                                            Total Earnings </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{env('CURRENCY')}} {{number_format($stats['total_earnings'],2)}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hand-holding-usd fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-success text-uppercase mb-2">
                                            This Month Sale </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{env('CURRENCY')}} {{number_format($stats['month_sales'],2)}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-primary text-uppercase mb-2">
                                            This Month Earnings </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{env('CURRENCY')}} {{number_format($stats['month_earnings'],2)}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hand-holding-usd fs-2x text-gray-500"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <a href="{{url('admin/orders')}}" class="card border border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="fs-6 fw-bold text-warning text-uppercase mb-2">
                                            Avg Order Value </div>
                                        <div class="h2 mb-0 fw-bold text-gray-800">{{env('CURRENCY')}} {{number_format($stats['avg_order'],2)}}</div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hand-holding-usd fs-2x text-gray-500"></i>
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
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Orders per month</span>
                                </h3>
                                <div class="card-toolbar">
                                </div>
                            </div>
                            <!--begin::Card body-->
                            <div class="card-body d-flex align-items-end ">
                                <canvas id="orders_chart" width="400" height="200"></canvas>
                            </div>
                            <!--end::Card body-->
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-flush overflow-hidden">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <!--begin::Title-->
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">{{date('M, Y')}} Orders </span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">This Month Orders</span>
                                </h3>
                                <div class="card-toolbar">
                                </div>
                            </div>
                            <!--begin::Card body-->
                            <div class="card-body d-flex align-items-end ">
                                <canvas id="month_orders" width="400" height="200"></canvas>
                            </div>
                            <!--end::Card body-->
                        </div>
                    </div>

                 
                    <div class="col-md-6">
                        <div class="card card-flush overflow-hidden h-100">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Top Partners By Orders </span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Top 5 partners by orders delivered </span>
                                </h3>
                                <div class="card-toolbar">
                                </div>
                            </div>
                            <div class="card-body ">
                                <!--begin::Chart-->
                                    @foreach ($stats['top_partners'] as $key=> $item)
                                    @php
                                        $percent=($item->count/$stats['top_partners'][0]->count)*100;
                                        $bar_color = match($key) {
                                            0 => 'bg-success',
                                            1 => 'bg-danger',
                                            2 => 'bg-info',
                                            3 => 'bg-warning',
                                            4 => 'bg-dark',
                                            default => 'bg-primary',
                                        };
                                    @endphp
                                        <div class="row my-5">
                                            <div class="col-4">
                                                <img src="{{$item->vendor->picture}}" class="w-30px h-30px rounded-circle">   {{$item->vendor->business_name}}
                                            </div>
                                            <div class="col-8 my-auto">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{$bar_color}} fs-6 text-white" role="progressbar" style="width: {{$percent}}%;" aria-valuenow="{{$percent}}" aria-valuemin="0" aria-valuemax="100">
                                                       {{$item->count}} Orders</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                                                    
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card card-flush overflow-hidden h-100">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Top Users By Orders </span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Top 5 users by cases listed </span>
                                </h3>
                                <div class="card-toolbar">
                                </div>
                            </div>
                            <div class="card-body ">
                                <!--begin::Chart-->
                                    @foreach ($stats['top_users'] as $key=> $item)
                                    @php
                                        $percent=($item->count/$stats['top_users'][0]->count)*100;
                                        $bar_color = match($key) {
                                            0 => 'bg-success',
                                            1 => 'bg-danger',
                                            2 => 'bg-info',
                                            3 => 'bg-warning',
                                            4 => 'bg-dark',
                                            default => 'bg-primary',
                                        };
                                    @endphp
                                        <div class="row my-5">
                                            <div class="col-4">
                                                <img src="{{$item->user->picture}}" class="w-30px h-30px rounded-circle">   {{$item->user->first_name}}
                                            </div>
                                            <div class="col-8 my-auto">
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar {{$bar_color}} fs-6 text-white" role="progressbar" style="width: {{$percent}}%;" aria-valuenow="{{$percent}}" aria-valuemin="0" aria-valuemax="100">
                                                       {{$item->count}} Orders</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                                                    
                            </div>
                        </div>
                    </div>
                  
                    <div class="col-md-6">
                        <div class="card card-flush overflow-hidden">
                            <!--begin::Header-->
                            <div class="card-header pt-5">
                                <!--begin::Title-->
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Users Stats</span>
                                    <span class="text-gray-400 mt-1 fw-semibold fs-6">Users registered per month</span>
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
                                <canvas id="myChart" width="400" height="200"></canvas>
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
        // getPieChart();
        getOrdersChart();
        thisMonthOrders();
        getUsersChart();

        function getUsersChart() {
            ctx = document.getElementById('myChart').getContext('2d');
            myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels:  ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                        {
                            label: 'Users Registerd',
                            data:  {{json_encode($chart_data['month_users'])}},
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
        function getOrdersChart() {
            ctx = document.getElementById('orders_chart').getContext('2d');
            myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels:  ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                        {
                            label:  "{{date('Y')}} Orders",
                            data:  {{json_encode($chart_data['month_orders'])}},
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

        //  Chart
        function thisMonthOrders() {
            ctx = document.getElementById('month_orders').getContext('2d');
            orderChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels:  {{json_encode($chart_data['dates'])}},
                datasets: [
                        {
                            label: "{{date('M Y')}} Order",
                            data: {{json_encode($chart_data['orders'])}},
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
                    'Closed Orders ',
                    'Total Orders ',
                ],
                datasets: [{
                    label: 'Order Stats',
                    data: [5, 20],
                    backgroundColor: [
                    'rgba(93, 173, 168, 0.8)',
                    'rgba(93, 173, 168 0.8)',
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
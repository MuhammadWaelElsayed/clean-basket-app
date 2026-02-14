
@section('reportActive','active')


<div>
    

    <style>
     .legened{
        /* font-size:35px; */
        background: white;
     }   
     .legend-div{
        margin-top:-33px;
     }
     fieldset{
        margin-top:50px;
        border: 1px solid lightgray;
     }
     .red-box{
        padding: 0px 10px;
        margin: 0px 10px;
       background-color: orangered;
        border: 1px dashed black;
     }
     .green-box{
        padding: 0px 10px;
        margin: 0px 10px;
       background-color: green;
        border: 1px dashed black;
     }
     .text-red{
        color: red
     }
     .bg-green {
            background-color: greenyellow !important;
            print-color-adjust: exact; /* replace with your desired color */
        }
     @media print {
        .bg-green {
            background-color: greenyellow !important;
            print-color-adjust: exact; /* replace with your desired color */
        }
    }
    </style>

<div class="" id="print-div">
    <button class="btn btn-base float-end m-3" onclick="printReport()">Print</button>

    <div class="container">
        <div class="text-center"> 
            <img src="{{ asset('media/logo.png') }}" class="my-10" width="230px" alt="" srcset=""> <br> 
            <h1 class="display-4 font-bold">{{date('F Y',strtotime($monthYear))}}</h1>
            @php
                if($month==date('m')){
                    $lastDate=date('M d');
                }
                else{
                    $lastDate=date('M d',strtotime("last day of $monthYear"));
                }
                $timestamp = strtotime("$monthYear-01");
                $lastMonth = date('F', strtotime('-1 month', $timestamp));
            @endphp
            <p class="fs-1 font-light">{{date('M d',strtotime($monthYear))}}st - {{$lastDate}}st</p>

            <fieldset class="w-lg-75 mx-auto p-3">
                <div class="text-center legend-div">
                    <span class="legened display-6 text-base-dark font-bold">{{session('partner')->business_name}}</span>
                </div>
                <div class="row my-5 p-3">
                    <div class="col-6">
                        <h1 class=" font-bold">Total Orders</h1>
                        <h1 class="display-4 fw-light font-light">{{$data['total_orders']}}</h1>
                        <p class="fs-4"><span class="{{($percentage['orders']<0)?'text-danger':'text-base-dark'}}">{{round($percentage['orders'],2)}}%</span> <span class="text-gray-500"> since {{$lastMonth}}</span> </p>
                    </div>
                    <div class="col-6">
                        <h1 class=" font-bold">Total Revenue</h1>
                        <h1 class="display-4 fw-light font-light">AED {{$data['total_revenue']}}</h1>
                        <p class="fs-4"><span class="{{($percentage['revenue']<0)?'text-danger':'text-base-dark'}}">{{round($percentage['revenue'],2)}}%</span> <span class="text-gray-500"> since {{$lastMonth}}</span> </p>
                    </div>
                </div>
            </fieldset>
            <div class="w-lg-75 mx-auto p-3">
                <div class="row mt-5 p-5">
                    <div class="col-6 offset-3">
                        <h1 class=" font-bold">Partner Earning</h1>
                        <h1 class="display-4  fw-light font-light">AED {{$data['vendorEarning']}}</h1>
                        <p class="fs-4"><span class="{{($percentage['vendorEarning']<0)?'text-danger':'text-base-dark'}}">{{round($percentage['vendorEarning'],2)}}%</span> <span class="text-gray-500"> since {{$lastMonth}}</span> </p>
                    </div>
                    {{-- <div class="col-6">
                        <h1 class=" font-bold">Save Me Earning</h1>
                        <h1 class="display-4 fw-light font-light">AED {{$data['saveMeEarning']}}</h1>
                        <p class="fs-4"><span class="{{($percentage['saveMeEarning']<0)?'text-danger':'text-base-dark'}}">{{round($percentage['saveMeEarning'],2)}}%</span> <span class="text-gray-500"> since {{$lastMonth}}</span> </p>
                    </div> --}}
                </div>
            </div>

            <fieldset class="mx-auto mt-0">
                <div class="row my-5 p-5">
                    <div class="col-6">
                        <h2 class=" font-bold"><span class="green-box"></span> Delivery Orders</h2>
                        <h1 class=" font-light">0</h1>
                    </div>
                    {{-- <div class="col-3">
                        <h3 class=" font-light"><span class="red-box"></span> Cash Revenue</h3>
                        <h1 class=" font-light">AED {{$data['cashRevenue']}}</h1>
                    </div> --}}
                    <div class="col-6">
                        <h2 class=" font-bold"><span class="red-box"></span> Pickup Orders</h2>
                        <h1 class=" font-light">10</h1>
                    </div>
                    {{-- <div class="col-3">
                        <h3 class=" font-light"><span class="blue-box"></span> Card Revenue</h3>
                        <h1 class=" font-light">AED {{$data['cardRevenue']}}</h1>
                    </div> --}}
                    
                </div>
            
            </fieldset>
        </div>
    </div>
    
        
    <div class="p-5 my-5 bg-base">
        <div class="container text-center ">
            <div class="row">
                <div class="col-6">
                    <h1 class=" font-bold">Partner to SaveMe Due</h1>
                    <h1 class="display-4  fw-light font-light">AED 0</h1>
                </div>
                <div class="col-6">
                    <h1 class=" font-bold"> SaveMe to Partner Due</h1>
                    <h1 class="display-4 fw-light font-light">AED <span class="text-red">0</span> </h1>
                </div>
            </div>
        </div>
    </div>

    <footer class="my-5 container text-center">
        <p class="fs-4 font-light">The information contained in the report is confidential. For any additional questions about your
            report please reach out via <a href="mailto:contact@saveme.ae">contact@saveme.ae</a></p> <br>
    </footer>
</div>

@section('scripts')
<script>
    function printReport() {
        $('#kt_app_header').hide();
        $('#kt_app_sidebar').hide();
        $('#kt_app_footer').hide();
        window.print();
        $('#kt_app_header').show();
        $('#kt_app_sidebar').show();
        $('#kt_app_footer').show();
    }
    // window.print();
</script>
@endsection


</div>

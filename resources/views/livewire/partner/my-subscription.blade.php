
@section('subscriptionActive','active')



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
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">My Subscription</h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('partner.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">My Subscription</li>
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
                    
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
							<!--begin::Heading-->
							<div class="mb-8 text-center">
							</div>
							<!--end::Heading-->
							<div class="row g-9 mb-8">
                                <h2>My Subscription</h2>
                                <div class="col-md-6">
                                    @if ($package==null)
                                        <p class="alert alert-danger fw-bold fs-3">You have not purchase any subscription yet.
                                            <a href="{{url('subscription?vendor_id='.session('partner')->id)}}" class="btn btn-sm btn-danger p-2 fs-5">Buy Now</a> 
                                        </p>
                                    @else
                                        @php
                                            if($package->is_cancelled==1){
                                                $status="Cancelled";
                                                $bg_color="danger";
                                                $button="<a href='".url('subscription?vendor_id='.session('partner')->id)."' class='btn bg-white text-dark  mt-3'>Purchase Subscription</a>";
                                            }
                                            elseif ($package->expired_at < date('Y-m-d h:i')){
                                                $status="Expired";
                                                $bg_color="danger";
                                                $button="<a href='".url('subscription?vendor_id='.session('partner')->id)."' class='btn bg-white text-dark  mt-3'>Renew Subscription</a>";
                                            }else{
                                                $status="Active";
                                                $bg_color="success";
                                                if ($package->is_free_trial==0) {
                                                    $button="<button class='btn bg-white text-dark  mt-3' id='cancelBtn'>Cancel It</button>";
                                                }else{
                                                    $button="";
                                                }
                                            }
                                        @endphp
                                        
                                            <div class="card bg-dark text-white p-lg-10 p-5">
                                              
                                                <h1 class="display-6 text-white">{{$package->package->name}} <span class="ms-10 badge bg-{{$bg_color}} fs-4">{{$status}}</span></h1> 
                                                <p>
                                                    <ul>
                                                        @foreach ($package->package->features as $feature)
                                                            <li>{{$feature}}</li>  
                                                        @endforeach
                                                    </ul>
                                                </p>
                                                <h5 class="fs-4 text-white mt-3 ">Expired on: {{date('d M, Y',strtotime($package->expired_at))}}</h5>
                                                <span>
                                                    {!!$button!!}
                                                </span>
                                            </div>
                                        

                                    @endif
                                        
                              
                                </div>
                                <div class="col-md-6">
                                    <h2>Addons </h2> 
                                    @foreach ($addons as $item)
                                        <div class="card border border-dark  p-5 mb-4">
                                            <div class="row">
                                                <div class="col-3">
                                                    <img src="{{$item->image}}" alt="img" class="w-100px h-100px object-cover rounded">
                                                </div>
                                                <div class="col-9">
                                                    <h4>{{$item->name}} <span class="float-end">{{env('CURRENCY')}} {{$item->price}}</span></h4>
                                                    <ul>
                                                        @foreach ($item->features as $feature)
                                                            <li>{{$feature}}</li>
                                                        @endforeach
                                                    </ul>
                                                    <p class="float-start mb-0">{{$item->period}} {{$item->period_type}} plan</p>

                                                    @if (isset($my_addons[$item->id])) <br>
                                                        <b >Expired on: {{date('d M, Y',strtotime($my_addons[$item->id]['expired_at']))}}</b>
                                                        <span class="badge badge-{{($my_addons[$item->id]['status']=="Active")?'success':'danger'}}
                                                             float-end">{{$my_addons[$item->id]['status']}}</span> <br> <br>
                                                    @endif
                                                    
                                                    @if(!isset($my_addons[$item->id]) || $my_addons[$item->id]['status']=="Expired")
                                                        <a href="{{url('cards?plan='.$item->id.'&language=en&vendor_id='.session('partner')->id)}}"
                                                            class="btn btn-sm btn-base rounded-pill float-end">Buy Now</a>
                                                    @endif


                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                        
                              
                                </div>
							</div>
						
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
    
    var cancelBtn= document.getElementById('cancelBtn');
    cancelBtn.onclick = function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You want to cancel, your current subscription",
            icon: 'warning',
            showCancelButton: true,
            cancelButtonColor: '#5E6278',
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            }).then((result) => {
            if (result.isConfirmed) {
                // this.form.submit();
                Livewire.dispatch('cancel');
            }
            })
    };
        
</script>
    
@endsection

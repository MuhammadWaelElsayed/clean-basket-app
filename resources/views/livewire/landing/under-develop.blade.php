


@section('homeActive','active')
<div>
	<style>
		.vscomp-toggle-button {
		background-color: #f5f8fa;
	}
	</style>

	
	<!--begin::Landing hero-->
	<div class="d-flex flex-column bg-base-gradient flex-center w-100 min-h-300px min-h-lg-500px px-9">
		<!--begin::Heading-->
		<div class="container">
			<div class="row ">
			
				<div class="col-md-12 text-center"  >
					<div class=" mb-5 mb-lg-10 py-10" >
						<!--begin::Title-->
						<img src="{{asset('media/logo.png')}}" alt="logo" class="w-25" >

						<h1 class="text-base lh-base fw-bold fs-2x fs-lg-3x mb-5 " data-aos="fade-up"  >{!!__('Coming Soon...')!!}</h1>  
						<p class="fs-4 mt-10 text-gray-600" data-aos="fade-up">{!!__('Website is Under Development')!!}</p>

							<a href="{{url('admin/login')}}" class="btn btn-base">Admin Login</a>
						<br>
						<!--end::Action-->
					</div>
				</div>
			
			</div>
		</div>
	</div>


</div>	
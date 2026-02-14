

@section('privacyActive','active')
<div>

	<div class="d-flex flex-column bg-light flex-center w-100 min-h-200px z-index-n1 min-h-lg-350px px-9">
		<!--begin::Heading-->
		
		<div class="row w-100 mx-auto ">
			
			<div class="col-md-8 mx-auto text-center">
				<div class="  mb-lg-10 pt-10 py-lg-5" >
					<!--begin::Title-->
					<h1 class="text-dark lh-base fw-bold fs-1x fs-lg-3x mb-5 text-uppercase" >{!!__($page->title)!!}</h1>  <br>
					
					<!--end::Action-->
				</div>
			</div>
		</div>
		
		<!--end::Heading-->
	</div>

	<div class="mt-n10 mt-lg-n10 z-index-2 min-h-300px inner-box">
		<div class="container">
			<div class="row ">
				<div class="col-12 col-md-8 mx-auto shadow bg-white px-15 mb-15 inner-content "> 
						<p class="fs-5  mt-15 " >
							{!!__($page->content)!!}
						</p>
				</div>
			</div>
		</div>
	</div>
	
</div>	
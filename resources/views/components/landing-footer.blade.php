<footer class=" bg-base-black">
	<div class="container px-5">
		<div class="row ">
			<div class="col-md-9">
					<p class=" p-3 text-white mt-3 fs-lg-5"> ⓒ {{date('Y')}} {{env('APP_NAME')}} | {!!__('All rights reserved')!!}</p>
			</div>
			<div class="col-md-3 text-right">
				<nav class="navbar nav mt-3 fs-5 mx-auto w-sm-100" > 
					<li class="nav-item"> <a class="nav-link text-white" href="{{ url('/privacy-policy') }}">{!!__('Privacy Policy')!!}</a> </li>
					<li class="nav-item text-white"> |</li>
					<li class="nav-item"> <a class="nav-link text-white" href="{{ url('/terms-conditions') }}">{!!__('Terms & Conditions')!!}</a> </li>
					{{-- <li class="nav-item text-white"> |</li>
					<li class="nav-item"> <a class="nav-link text-white" href="{{ url('/help') }}">{!!__('Help')!!}</a> </li> --}}
				</nav>
				   
			</div>
		</div>
		   
	</div>

</footer>


<!DOCTYPE html>

<html lang="{{app()->getLocale()}}" dir="{{(app()->getLocale() == 'ar')?'rtl':'ltr'}}">
	<!--begin::Head-->
	<head><base href=""/>
		<title>{{env('APP_NAME')}} |  </title> 
		<meta charset="utf-8" />
		<meta name="description" content=" " />
		<meta name="keywords" content="" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content="{{env('APP_NAME')}} " />
		<meta property="og:url" content="https://master.devicebee.com" />
		<meta property="og:site_name" content="{{env('APP_NAME')}} " />
		<link rel="shortcut icon" href="{{ asset('media/favicon.png') }}" />

		<link href="{{ asset('plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
		{{-- AOS CDN --}}
		<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
		@livewireStyles
		
		<script src="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.js" integrity="sha256-Gsn2XyJGdUeHy0r4gaP1mJy1JkLiIWY6g6hJhV5UrIw=" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.css" integrity="sha256-KqTuc/vUgQsb5EMyyxWf62qYinMUXDpWELyNx+cCUr0=" crossorigin="anonymous">
		
	</head>

	<body id="kt_body" data-bs-spy="scroll" data-bs-target="#kt_landing_menu" class="bg-white position-relative app-blank">
	
		<div class="" id="kt_app_root">
			<!--begin::Header Section-->
			<div class="mb-0" id="home">
				<div class="bgi-no-repeat bgi-size-contain bgi-position-x-center bgi-position-y-bottom landing-light-bg" >
                    <x-landing-header/>
			</div>
			<!--end::Header Section-->
			{{$slot}}
			
			<x-landing-footer/>

			<!--begin::Scrolltop-->
			<div id="kt_scrolltop" class="scrolltop bg-base" data-kt-scrolltop="true">
				<span class="svg-icon">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
						<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
					</svg>
				</span>
			</div>
		</div>

		<!--begin::Javascript-->
		<script src=" {{ asset('plugins/global/plugins.bundle.js') }}"></script>
		<script src=" {{ asset('js/scripts.bundle.js') }}"></script>

		<!--begin::Custom Javascript(used for this page only)-->
		<script src="{{ asset('js/custom/landing.js')}}"></script>
		<!--end::Javascript-->
		<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
		

		<script>
			AOS.init({
				duration:1000,
				delay:300
			});
		  </script>

		@livewireScripts
		@yield('scripts')
		<x-landing-alert />
				
	</body>
	<!--end::Body-->
</html>


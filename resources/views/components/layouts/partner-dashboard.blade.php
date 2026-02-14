
<html lang="en">
	<!--begin::Head-->
	<head><base href=""/>
		<title>Laundary Dashboard | {{env('APP_NAME')}}</title> 
		<meta charset="utf-8" />
		<meta name="description" content="Legal Platform Portal" />
		<meta name="keywords" content="Legal Platform Portal, Vendors, Cases" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content=" {{env('APP_NAME')}} | Tagline Here" />
		<meta property="og:url" content="http://master.devicebee.com/iweft" />
		<meta property="og:site_name" content="DeviceBee | Metronic" />
		<link rel="canonical" href="http://master.devicebee.com/iweft" />
		<link rel="shortcut icon" href="{{ asset('media/favicon.png') }}" />
		<!--begin::Fonts(mandatory for all pages)-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
		<link href="{{ asset('plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('css/style.bundle.css')}}" rel="stylesheet" type="text/css" />
		@livewireStyles
		<script src="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.js" integrity="sha256-Gsn2XyJGdUeHy0r4gaP1mJy1JkLiIWY6g6hJhV5UrIw=" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/virtual-select-plugin@1.0.39/dist/virtual-select.min.css" integrity="sha256-KqTuc/vUgQsb5EMyyxWf62qYinMUXDpWELyNx+cCUr0=" crossorigin="anonymous">
		
		<!--end::Global Stylesheets Bundle-->
	</head>
	<style>
		.nav-tabs .nav-link.active{
			border-bottom:3px solid #5DADA8;
			color: #5DADA8 !important;
		}
		.nav-tabs .nav-link.active >i{
			color: #5DADA8 !important;
		}
		.nav-tabs .nav-link:hover{
			/* background-color: #EC5299; */
			border-bottom:3px solid #5DADA8;
		}
	</style>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_app_body" data-kt-app-layout="light-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
		<!--begin::App-->
		<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
			<!--begin::Page-->
			<div class="app-page flex-column flex-column-fluid" id="kt_app_page">
				<!--begin::Header-->
				<x-partner-topbar />
				<!--begin::Wrapper-->
				<div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
					<!--begin::Sidebar-->
					<x-partner-sidebar />
					<!--begin::Main-->
					{{-- @yield('main') --}}
					{{$slot}}
					<!--end:::Main-->
				</div>
				<x-footer />
				<!--end::Wrapper-->
			</div>
			<!--end::Page-->
		</div>
		<!--end::App-->
	
		<!--begin::Scrolltop-->
		<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
			<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
			<span class="svg-icon">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="currentColor" />
					<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="currentColor" />
				</svg>
			</span>
			<!--end::Svg Icon-->
		</div>

		

		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src=" {{ asset('plugins/global/plugins.bundle.js') }}"></script>
		<script src=" {{ asset('js/scripts.bundle.js') }}"></script>
        
		@livewireScripts
		
		@yield('scripts')

		<x-alert />
	</body>
	<!--end::Body-->
</html>
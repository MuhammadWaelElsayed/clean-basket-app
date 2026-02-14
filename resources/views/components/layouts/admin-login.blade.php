
<html lang="en">
	<!--begin::Head-->
	<head><base href=""/>
		<title>Admin Login | {{env('APP_NAME')}}</title> 
		<meta charset="utf-8" />
		<meta name="description" content="Legal Platform Portal" />
		<meta name="keywords" content="Legal Platform Portal, Vendors, Cases" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta property="og:locale" content="en_US" />
		<meta property="og:type" content="article" />
		<meta property="og:title" content=" {{env('APP_NAME')}} | Admin Login" />
		<meta property="og:url" content="http://master.devicebee.com/saveMe" />
		<meta property="og:site_name" content="Keenthemes | Metronic" />
		<link rel="canonical" href="http://master.devicebee.com/saveMe" />
		<link rel="shortcut icon" href="{{ asset('media/favicon.png') }}" />
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
		<link href="{{ asset('plugins/global/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('css/style.bundle.css')}}" rel="stylesheet" type="text/css" />

        @livewireStyles
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	
	<body id="kt_body" class="app-blank app-blank">
        
		{{$slot}}
        
        
		<!--begin::Global Javascript Bundle(mandatory for all pages)-->
		<script src="{{ asset('plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('js/scripts.bundle.js') }}"></script>
		<!--end::Global Javascript Bundle-->
		<!--end::Javascript-->
	
    
    @livewireScripts
	<x-alert/>
	
	</body>
	<!--end::Body-->
</html>
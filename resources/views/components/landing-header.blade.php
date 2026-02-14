<!--begin::Header-->

<div class="landing-header z-index-1 " data-kt-sticky="true" data-kt-sticky-name="landing-header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
	<!--begin::Container-->
	<div class="container">
		<!--begin::Wrapper-->
		<div class="d-flex align-items-center justify-content-between">
			<!--begin::Logo-->
			<div class="d-flex align-items-center flex-equal">
				<!--begin::Mobile menu toggle-->
				<button class="btn btn-icon btn-active-color-primary me-3 d-flex d-lg-none " id="kt_landing_menu_toggle">
					<!--begin::Svg Icon | path: icons/duotune/abstract/abs015.svg-->
					<span class="svg-icon svg-icon-2hx text-base">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="currentColor" />
							<path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="currentColor" />
						</svg>
					</span>
					<!--end::Svg Icon-->
				</button>
				<!--end::Mobile menu toggle-->
				<!--begin::Logo image-->
				<a href="{{ url('home') }}" class="mx-5">   
                    <h1 class="fs-1 fw-bold"><img src="{{ asset('media/logo.png') }}" alt="logo" class="w-100px"></h1>
				</a>
				<!--end::Logo image-->
			</div>
			<!--end::Logo-->
			<!--begin::Menu wrapper-->
			<div class="d-lg-block" id="kt_header_nav_wrapper">
				<div class="d-lg-block p-5 p-lg-0" data-kt-drawer="true" data-kt-drawer-name="landing-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="200px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_landing_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav_wrapper'}">
					<!--begin::Menu-->
					<div class="menu menu-column flex-nowrap menu-rounded menu-lg-row menu-title-gray-800 menu-state-title-dark nav nav-flush fs-5 " id="kt_landing_menu">
						<!--begin::Menu item-->
						<div class="menu-item">
							<!--begin::Menu link--> 
							<a class="menu-link nav-link py-3 px-4 px-xxl-6" href="{{ url('/') }}">{{__('Home')}}</a>
							<!--end::Menu link-->
						</div>
						<div class="menu-item">
							<!--begin::Menu link-->
							<a class="menu-link nav-link py-3 px-4 px-xxl-6 " href="{{ url('/#about') }}" >{{__('About Us')}}</a>
							<!--end::Menu link-->
						</div>
						
						<div class="menu-item">
							<!--begin::Menu link-->
							<a class="menu-link nav-link py-3 px-4 px-xxl-6 " href="{{ url('/#download') }}">{{__('Download')}}</a>
							<!--end::Menu link-->
						</div>
						<!--end::Menu item-->
						<!--begin::Menu item-->
						<div class="menu-item">
							<!--begin::Menu link-->
							<a class="menu-link nav-link py-3 px-4 px-xxl-6 " href="{{ url('/#contact') }}">{{__('Contact Us')}} </a>
							<!--end::Menu link-->
						</div>
						<div class="menu-item">
							@php
								$lang=app()->getLocale();
							@endphp
							@if ($lang=="ar")
								<a href="{{route('switchLan','en')}}" class=" me-3 btn btn-icon btn-language w-40px h-40px"  >
									<img src="{{ asset('media/english.png') }}" width="40px"  alt="eng">
								</a>
							@else
								<a href="{{route('switchLan','ar')}}" class=" me-3 btn btn-icon btn-language w-40px h-40px"  >
									<img src="{{ asset('media/arabic.png') }}" width="40px"  alt="arabic">
								</a>
							@endif
							
							{{-- <a href="{{route('switchLan','ar')}}" class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-success w-35px h-35px w-md-40px h-md-40px" data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
								<!--begin::Svg Icon | path: icons/duotune/general/gen060.svg-->
								<img src="{{ asset('media/flags/united-arab-emirates.svg') }}" width="20px"  alt="" srcset="">
								<!--end::Svg Icon--> 
							</a> --}}
							
						</div>
                        <div class="menu-item">
							<!--begin::Menu link-->
							{{-- <a class=" py-3 px-4 me-3 px-xxl-6 btn btn-white rounded-pill" href="{{ url('/become-partner') }}">{{__('Become a Partner')}} </a> --}}
							<a class=" py-3 px-4  px-xxl-6 btn " href="{{ url('/partner/login') }}">{{__('Partner Login')}} </a>
							<!--end::Menu link-->
						</div>
						<!--end::Menu item-->
					</div>
					<!--end::Menu-->
				</div>
			</div>
			<!--end::Menu wrapper-->
			<!--begin::Toolbar-->
			<div class="flex-half text-end ms-1">
			</div>
			<!--end::Toolbar-->
		</div>
		<!--end::Wrapper-->
	</div>
	<!--end::Container-->
</div>
<!--end::Header-->
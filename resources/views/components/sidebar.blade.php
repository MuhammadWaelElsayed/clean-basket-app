<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ url('admin/dashboard') }}" wire:navigate>
            <img alt="Logo" src="{{ asset('media/logo.png') }}"
                 class="h-60px text-center app-sidebar-logo-default"/>
            <img alt="Logo" src="{{ asset('media/favicon.png') }}" class="h-30px app-sidebar-logo-minimize"/>
        </a>

        <!--begin::Sidebar toggle-->
        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary body-bg h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <!--begin::Svg Icon | path: icons/duotune/arrows/arr079.svg-->
            <span class="svg-icon svg-icon-2 rotate-180">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                     xmlns="http://www.w3.org/2000/svg">
                    <path opacity="0.5"
                          d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                          fill="currentColor"/>
                    <path
                        d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                        fill="currentColor"/>
                </svg>
            </span>
            <!--end::Svg Icon-->
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y my-5"
             data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
             data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
             data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
             data-kt-scroll-save-state="true">
            <!--begin::Menu-->
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="#kt_app_sidebar_menu"
                 data-kt-menu="true" data-kt-menu-expand="false">

                <!--begin:Menu item-->
                <div class="menu-item ">
                    <!--begin:Menu link-->
                    <a class="menu-link  @yield('dashboardActive')" href="{{ url('admin/dashboard') }}" wire:navigate>
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: icons/duotune/general/gen025.svg-->
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="2" width="9" height="9" rx="2"
                                          fill="currentColor"/>
                                    <rect opacity="0.3" x="13" y="2" width="9" height="9" rx="2"
                                          fill="currentColor"/>
                                    <rect opacity="0.3" x="13" y="13" width="9" height="9" rx="2"
                                          fill="currentColor"/>
                                    <rect opacity="0.3" x="2" y="13" width="9" height="9" rx="2"
                                          fill="currentColor"/>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">Dashboard</span>
                    </a>
                    <!--end:Menu link-->
                </div>
                <!--begin:Menu item-->
                <div class="menu-item pt-5">
                    <!--begin:Menu content-->
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Navigation Links</span>
                    </div>
                    <!--end:Menu content-->
                </div>

                <!--end:Menu item-->
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('partnersActive')">
                        <span class="menu-link @yield('partnersActive')">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <i class="fas fa-building"></i>
                            </span>
                        </span>
                        <span class="menu-title">Partners Management</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            @can('list_partner')
                                <a class="menu-link" href="{{ url('admin/partners') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">All Partners</span>
                                </a>
                            @endcan

                        </div>
                        <div class="menu-item">
                            @can('list_working_hours')
                                <a class="menu-link" href="{{ route('admin.partners.working-hours') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Working Hours</span>
                                </a>
                            @endcan

                        </div>
                        <div class="menu-item">
                            @can('partners_map')
                                <a class="menu-link  @yield('partnersMapActive')"
                                   href="{{ route('admin.partners.map') }}" wire:navigate>
                                    {{-- <span class="menu-icon">
                                        <span class="svg-icon svg-icon-2">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                    </span> --}}
                                    <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Partners Map</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>

                @can('list_driver')
                    <div class="menu-item">
                        <a class="menu-link  @yield('driversActive')" href="{{ url('admin/drivers') }}" wire:navigate>
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <i class="fas fa-truck"></i>
                            </span>
                        </span>
                            <span class="menu-title">Drivers Management</span>
                        </a>
                    </div>
                @endcan

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('itemsActive')">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.5 11C8.98528 11 11 8.98528 11 6.5C11 4.01472 8.98528 2 6.5 2C4.01472 2 2 4.01472 2 6.5C2 8.98528 4.01472 11 6.5 11Z"
                                        fill="currentColor"></path>
                                    <path opacity="0.3"
                                          d="M13 6.5C13 4 15 2 17.5 2C20 2 22 4 22 6.5C22 9 20 11 17.5 11C15 11 13 9 13 6.5ZM6.5 22C9 22 11 20 11 17.5C11 15 9 13 6.5 13C4 13 2 15 2 17.5C2 20 4 22 6.5 22ZM17.5 22C20 22 22 20 22 17.5C22 15 20 13 17.5 13C15 13 13 15 13 17.5C13 20 15 22 17.5 22Z"
                                          fill="currentColor"></path>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">Items Pricing</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->
                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            @can('list_item')
                                <a class="menu-link" href="{{ url('admin/items') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">All Items</span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('create_item')
                                <a class="menu-link" href="{{ url('admin/items/create') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Add New Item</span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('list_services')
                                <a class="menu-link" href="{{ url('admin/items/services/manage') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Manage Services</span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('bulk_assign_services')
                                <a class="menu-link" href="{{ url('admin/items/services/bulk-assign') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Bulk Assign Services</span>
                                </a>
                            @endcan
                        </div>
                    </div>
                    <!--end:Menu sub-->
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('ordersActive')">
                    <!--begin:Menu link-->
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M21 10H13V11C13 11.6 12.6 12 12 12C11.4 12 11 11.6 11 11V10H3C2.4 10 2 10.4 2 11V13H22V11C22 10.4 21.6 10 21 10Z"
                                            fill="currentColor"></path>
                                        <path opacity="0.3"
                                              d="M12 12C11.4 12 11 11.6 11 11V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V11C13 11.6 12.6 12 12 12Z"
                                              fill="currentColor"></path>
                                        <path opacity="0.3"
                                              d="M18.1 21H5.9C5.4 21 4.9 20.6 4.8 20.1L3 13H21L19.2 20.1C19.1 20.6 18.6 21 18.1 21ZM13 18V15C13 14.4 12.6 14 12 14C11.4 14 11 14.4 11 15V18C11 18.6 11.4 19 12 19C12.6 19 13 18.6 13 18ZM17 18V15C17 14.4 16.6 14 16 14C15.4 14 15 14.4 15 15V18C15 18.6 15.4 19 16 19C16.6 19 17 18.6 17 18ZM9 18V15C9 14.4 8.6 14 8 14C7.4 14 7 14.4 7 15V18C7 18.6 7.4 19 8 19C8.6 19 9 18.6 9 18Z"
                                              fill="currentColor"></path>
                                    </svg>
                                </span>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">Orders Management</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <!--end:Menu link-->

                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion">
                        <div class="menu-item">
                            @can('list_order')
                                <a class="menu-link" href="{{ url('admin/orders') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">All Orders</span>
                                </a>
                            @endcan
                        </div>

                        <div class="menu-item">
                            @can('orders_map')
                                <a class="menu-link @yield('ordersMapActive')" href="{{ url('admin/orders/map') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Orders Map</span>
                                </a>
                            @endcan
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="{{ url('admin/trips') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Trips</span>
                            </a>
                        </div>

                        <div class="menu-item">
                            <a class="menu-link" href="{{ route('admin.order-driver-monitor') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">Driver request monitor</span>
                            </a>
                        </div>

                    </div>
                    <!--end:Menu sub-->
                </div>

                <div class="menu-item">
                    <!--begin:Menu link-->
                    @can('list_external_driver')
                        <a class="menu-link @yield('orderDriversActive')" href="{{ url('admin/order-drivers') }}"
                           wire:navigate>
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 1H5C3.89 1 3 1.89 3 3V21C3 22.11 3.89 23 5 23H19C20.11 23 21 22.11 21 21V9M19 9H14V4H5V21H19V9Z"
                                        fill="currentColor"/>
                                    <path opacity="0.3" d="M8 12H16V14H8V12ZM8 16H16V18H8V16Z" fill="currentColor"/>
                                </svg>
                            </span>
                        </span>
                            <span class="menu-title">External Drivers</span>
                        </a>
                    @endcan
                    <!--end:Menu link-->
                </div>


                <div class="menu-item">
                    @can('list_customer')
                        <a class="menu-link @yield('usersActive')" href="{{ url('admin/users') }}" wire:navigate>
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.28548 15.0861C7.34369 13.1814 9.35142 12 11.5304 12H12.4696C14.6486 12 16.6563 13.1814 17.7145 15.0861L19.3493 18.0287C20.0899 19.3618 19.1259 21 17.601 21H6.39903C4.87406 21 3.91012 19.3618 4.65071 18.0287L6.28548 15.0861Z"
                                        fill="currentColor"/>
                                    <rect opacity="0.3" x="8" y="3" width="8" height="8" rx="4"
                                          fill="currentColor"/>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                            <span class="menu-title">Customers Management</span>
                        </a>
                    @endcan
                    <!--end:Menu link-->
                </div>
                <div class="menu-item">
                    @can('list_discount')
                        <a class="menu-link @yield('codesActive')" wire:navigate href="{{ route('admin.codes') }}">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <i class="fas fa-percentage"></i>
                            </span>
                        </span>
                            <span class="menu-title">Discounts/Promos</span>
                        </a>
                    @endcan
                </div>


                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('dataShow')">
                    <!--begin:Menu link-->
                    <span class="menu-link @yield('vendorsActive')">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.3"
                                          d="M21.25 18.525L13.05 21.825C12.35 22.125 11.65 22.125 10.95 21.825L2.75 18.525C1.75 18.125 1.75 16.725 2.75 16.325L4.04999 15.825L10.25 18.325C10.85 18.525 11.45 18.625 12.05 18.625C12.65 18.625 13.25 18.525 13.85 18.325L20.05 15.825L21.35 16.325C22.35 16.725 22.35 18.125 21.25 18.525ZM13.05 16.425L21.25 13.125C22.25 12.725 22.25 11.325 21.25 10.925L13.05 7.62502C12.35 7.32502 11.65 7.32502 10.95 7.62502L2.75 10.925C1.75 11.325 1.75 12.725 2.75 13.125L10.95 16.425C11.65 16.725 12.45 16.725 13.05 16.425Z"
                                          fill="currentColor"></path>
                                    <path
                                        d="M11.05 11.025L2.84998 7.725C1.84998 7.325 1.84998 5.925 2.84998 5.525L11.05 2.225C11.75 1.925 12.45 1.925 13.15 2.225L21.35 5.525C22.35 5.925 22.35 7.325 21.35 7.725L13.05 11.025C12.45 11.325 11.65 11.325 11.05 11.025Z"
                                        fill="currentColor"></path>
                                </svg>
                            </span>
                        </span>
                        <span class="menu-title">Data Management </span>
                        <span class="menu-arrow"></span>
                    </span>

                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion @yield('servicesActive')">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            @can('list_service')
                                <a class="menu-link @yield('servicesActive')" href="{{ url('admin/services') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Services Management</span>
                                    </span>
                                </a>
                            @endcan
                        </div>

                        <div class="menu-item">
                            @can('list_city')
                                <a class="menu-link @yield('citiesActive')" href="{{ url('admin/cities') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Cities Management</span>
                                    </span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('list_page')
                                <a class="menu-link @yield('pagesActive')" href="{{ url('admin/pages') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Pages Management</span>
                                    </span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('integration_tokens')
                                <a class="menu-link @yield('integrationTokensActive')"
                                   href="{{ url('admin/integration-tokens') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Integration Tokens</span>
                                    </span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('service_fee_settings')
                                <a class="menu-link @yield('serviceFeeSettingsActive')"
                                   href="{{ url('admin/service-fee-settings') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Service Fee Settings</span>
                                    </span>
                                </a>
                            @endcan

                        </div>
                    </div>
                    <!--end:Menu sub-->
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('appShow')">
                    <!--begin:Menu link-->
                    <span class="menu-link @yield('appActive')">
                        <span class="menu-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </span>
                        <span class="menu-title">App Management </span>
                        <span class="menu-arrow"></span>
                    </span>

                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion @yield('appShow')">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            @can('list_banner')
                                <a class="menu-link @yield('bannersActive')" href="{{ url('admin/banners') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Banners Management</span>
                                    </span>
                                </a>
                            @endcan
                        </div>

                        <div class="menu-item">
                            @can('app_settings')
                                <a class="menu-link @yield('settingsActive')" href="{{ url('admin/settings') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">App Settings</span>
                                    </span>
                                </a>
                            @endcan
                        </div>
                        <div class="menu-item">
                            @can('list_onboard')
                                <a class="menu-link @yield('onboardActive')" href="{{ url('admin/onboard') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Onboard Management</span>
                                    </span>
                                </a>
                            @endcan
                        </div>

                    </div>
                    <!--end:Menu sub-->
                </div>

                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('basketShow')">
                    <!--begin:Menu link-->
                    <span class="menu-link @yield('basketShow')">
                        <span class="menu-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M21 10H13V11C13 11.6 12.6 12 12 12C11.4 12 11 11.6 11 11V10H3C2.4 10 2 10.4 2 11V13H22V11C22 10.4 21.6 10 21 10Z"
                                    fill="currentColor"></path>
                                <path opacity="0.3"
                                      d="M12 12C11.4 12 11 11.6 11 11V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V11C13 11.6 12.6 12 12 12Z"
                                      fill="currentColor"></path>
                                <path opacity="0.3"
                                      d="M18.1 21H5.9C5.4 21 4.9 20.6 4.8 20.1L3 13H21L19.2 20.1C19.1 20.6 18.6 21 18.1 21ZM13 18V15C13 14.4 12.6 14 12 14C11.4 14 11 14.4 11 15V18C11 18.6 11.4 19 12 19C12.6 19 13 18.6 13 18ZM17 18V15C17 14.4 16.6 14 16 14C15.4 14 15 14.4 15 15V18C15 18.6 15.4 19 16 19C16.6 19 17 18.6 17 18ZM9 18V15C9 14.4 8.6 14 8 14C7.4 14 7 14.4 7 15V18C7 18.6 7.4 19 8 19C8.6 19 9 18.6 9 18Z"
                                      fill="currentColor"></path>
                            </svg>
                        </span>
                        <span class="menu-title">Basket Management </span>
                        <span class="menu-arrow"></span>
                    </span>

                    <!--begin:Menu sub-->
                    <div class="menu-sub menu-sub-accordion @yield('basketShow')">
                        <!--begin:Menu item-->
                        <div class="menu-item">
                            @can('basket_requests')
                                <a class="menu-link @yield('requestsActive')" href="{{ url('admin/requests') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Basket Requests</span>
                                    </span>
                                </a>
                            @endcan
                        </div>

                        <div class="menu-item">
                            @can('basket_inventory')
                                <a class="menu-link @yield('inventoryActive')" href="{{ url('admin/inventory') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Basket Inventory</span>
                                    </span>
                                </a>
                            @endcan
                        </div>

                    </div>
                    <!--end:Menu sub-->
                </div>
                {{-- Wallet Management --}}
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('walletShow')">
                    <span class="menu-link @yield('walletShow')">
                        <span class="menu-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path opacity="0.3" d="M2 5H22V19H2V5Z" fill="currentColor"/>
                                <path d="M2 9H22V19H2V9Z" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="menu-title">Wallet Management</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion @yield('walletShow')">
                        <!-- Wallet Transactions -->
                        <div class="menu-item">
                            @can('wallet_transactions')
                                <a class="menu-link @yield('transactionsActive')"
                                   href="{{ url('admin/wallet/transactions') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Transactions</span>
                                </a>
                            @endcan
                        </div>

                        <!-- Manual Wallet Charge -->
                        <div class="menu-item">
                            @can('wallet_manual_charge')
                                <a class="menu-link @yield('manualChargeActive')"
                                   href="{{ url('admin/wallet/manual-charge') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Manual Charge</span>
                                </a>
                            @endcan
                        </div>

                        <!-- Manual Wallet Withdrawal -->
                        <div class="menu-item">
                            @can('wallet_manual_withdraw')
                                <a class="menu-link @yield('walletManualWithdrawal')"
                                   href="{{ url('admin/wallet/manual-withdrawal') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Manual Withdrawal</span>
                                </a>
                            @endcan
                        </div>

                        <!-- Wallet Settings -->
                        <div class="menu-item">
                            @can('wallet_settings')
                                <a class="menu-link @yield('walletSettingsActive')"
                                   href="{{ url('admin/wallet/settings') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Wallet Settings</span>
                                </a>
                            @endcan
                        </div>

                    </div>
                </div>

                {{-- Packages Reports --}}
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('reportsShow')">
                    <span class="menu-link @yield('reportsShow')">
                        <span class="menu-icon">
                            <i class="fas fa-chart-pie"></i>
                        </span>
                        <span class="menu-title">Packages</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion @yield('reportsShow')">

                        <div class="menu-item">
                            @can('manage_packages')
                                <a class="menu-link @yield('reportsManagePackages')"
                                   href="{{ route('admin.packages') }}"
                                   wire:navigate>
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Manage Packages</span>
                                </a>
                            @endcan
                        </div>

                        {{-- Financial Report --}}
                        <div class="menu-item">
                            @can('packages_finance')
                                <a class="menu-link @yield('reportsFinancial')"
                                   href="{{ route('admin.packages.financialReport') }}" wire:navigate>
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Financial</span>
                                </a>
                            @endcan
                        </div>

                        {{-- Usage Report --}}
                        <div class="menu-item">
                            @can('packages_reports')
                                <a class="menu-link @yield('reportsUsage')"
                                   href="{{ route('admin.packages.usageReport') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Usage Report</span>
                                </a>
                            @endcan

                        </div>

                        {{-- Premium Report --}}
                        <div class="menu-item">
                            @can('packages_reports')
                                <a class="menu-link @yield('reportsPremium')"
                                   href="{{ route('admin.packages.premiumReport') }}">
                                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                    <span class="menu-title">Premium Report</span>
                                </a>
                            @endcan

                        </div>


                    </div>
                </div>

                {{-- Vouchers Management --}}
                {{-- <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('reportsShow')">
                    <span class="menu-link @yield('reportsShow')">
                        <span class="menu-icon">
                            <i class="fas fa-ticket"></i>
                        </span>
                        <span class="menu-title">Vouchers</span>
                        <span class="menu-arrow"></span>
                    </span>

                    <div class="menu-sub menu-sub-accordion @yield('reportsShow')">
                        <div class="menu-item">
                            <a class="menu-link @yield('vouchersActive')" href="{{ route('admin.vouchers') }}"
                                wire:navigate>
                                <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                                <span class="menu-title">Vouchers Management</span>
                            </a>
                        </div>

                    <div class="menu-item">
                        <a class="menu-link @yield('voucherReport')" href="{{ route('admin.vouchers.voucherReport') }}">
                            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                            <span class="menu-title">Voucher Report</span>
                        </a>
                    </div>
                </div>


                </div> --}}



                {{-- <div class="menu-item">
                    <a class="menu-link @yield('requestsActive')" href="{{ url('admin/requests') }}">
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: icons/duotune/communication/com013.svg-->
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 10H13V11C13 11.6 12.6 12 12 12C11.4 12 11 11.6 11 11V10H3C2.4 10 2 10.4 2 11V13H22V11C22 10.4 21.6 10 21 10Z" fill="currentColor"></path>
                                <path opacity="0.3" d="M12 12C11.4 12 11 11.6 11 11V3C11 2.4 11.4 2 12 2C12.6 2 13 2.4 13 3V11C13 11.6 12.6 12 12 12Z" fill="currentColor"></path>
                                <path opacity="0.3" d="M18.1 21H5.9C5.4 21 4.9 20.6 4.8 20.1L3 13H21L19.2 20.1C19.1 20.6 18.6 21 18.1 21ZM13 18V15C13 14.4 12.6 14 12 14C11.4 14 11 14.4 11 15V18C11 18.6 11.4 19 12 19C12.6 19 13 18.6 13 18ZM17 18V15C17 14.4 16.6 14 16 14C15.4 14 15 14.4 15 15V18C15 18.6 15.4 19 16 19C16.6 19 17 18.6 17 18ZM9 18V15C9 14.4 8.6 14 8 14C7.4 14 7 14.4 7 15V18C7 18.6 7.4 19 8 19C8.6 19 9 18.6 9 18Z" fill="currentColor"></path>
                            </svg>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title"></span>
                    </a>
                    <!--end:Menu link-->
                </div> --}}

                <div class="menu-item">
                    @can('website_inquiry')
                        <a class="menu-link @yield('inqueriesActive')" href="{{ url('admin/inqueries') }}">
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: icons/duotune/communication/com013.svg-->
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.28548 15.0861C7.34369 13.1814 9.35142 12 11.5304 12H12.4696C14.6486 12 16.6563 13.1814 17.7145 15.0861L19.3493 18.0287C20.0899 19.3618 19.1259 21 17.601 21H6.39903C4.87406 21 3.91012 19.3618 4.65071 18.0287L6.28548 15.0861Z"
                                        fill="currentColor"/>
                                    <rect opacity="0.3" x="8" y="3" width="8" height="8" rx="4"
                                          fill="currentColor"/>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                            <span class="menu-title">Website Inqueries</span>
                        </a>
                    @endcan
                    <!--end:Menu link-->
                </div>

                @can('support')
                    {{-- Support Management --}}
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('supportShow')">
                    <span class="menu-link @yield('supportShow')">
                        <span class="menu-icon">
                            <i class="fas fa-headset"></i>
                        </span>
                        <span class="menu-title">Support Management</span>
                        <span class="menu-arrow"></span>
                    </span>

                        <div class="menu-sub menu-sub-accordion @yield('supportShow')">
                            <div class="menu-item">
                                <a class="menu-link @yield('ticketsActive')" href="{{ route('admin.tickets') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Tickets Management</span>
                                </a>
                            </div>

                            <div class="menu-item">
                                <a class="menu-link @yield('issueCategoriesActive')"
                                   href="{{ route('admin.issue-categories') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Issue Categories</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endcan

                @can('manage_b2b_clients')
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('listB2B')">
                    <span class="menu-link @yield('listB2B')">
                        <span class="menu-icon">
                            <i class="fas fa-user-group"></i>
                        </span>
                        <span class="menu-title">B2B</span>
                        <span class="menu-arrow"></span>
                    </span>

                        @can('manage_b2b_clients')
                            <div class="menu-sub menu-sub-accordion @yield('b2bClients')">
                                <div class="menu-item">
                                    <a class="menu-link @yield('b2bClients')" href="{{ route('b2b-clients.index') }}"
                                       wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                        <span class="menu-title">Clients</span>
                                    </a>
                                </div>
                            </div>
                        @endcan

                        @can('manage_b2b_pricing_tiers')
                            <div class="menu-sub menu-sub-accordion @yield('b2bPricing')">
                                <div class="menu-item">
                                    <a class="menu-link @yield('b2bClients')" href="{{ route('pricing-tiers.index') }}"
                                       wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                        <span class="menu-title">Pricing tiers</span>
                                    </a>
                                </div>
                            </div>
                        @endcan
                        @can('manage_b2b_orders')
                            <div class="menu-sub menu-sub-accordion @yield('b2bOrders')">
                                <div class="menu-item">
                                    <a class="menu-link @yield('b2bOrders')" href="{{ route('b2b-orders.index') }}"
                                       wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                        <span class="menu-title">Orders</span>
                                    </a>
                                </div>
                            </div>
                        @endcan

{{--                        @can('manage_b2b_partners')--}}
                            <div class="menu-sub menu-sub-accordion @yield('b2bPartners')">
                                <div class="menu-item">
                                    <a class="menu-link @yield('b2bPartners')" href="{{ route('b2b.partners') }}"
                                       wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                        <span class="menu-title">Partners</span>
                                    </a>
                                </div>
                            </div>
{{--                        @endcan--}}
                    </div>
                @endcan

                @can('manage_roles_and_permissions')
                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion @yield('PermissionShow')">
                    <span class="menu-link @yield('PermissionShow')">
                        <span class="menu-icon">
                            <i class="fas fa-headset"></i>
                        </span>
                        <span class="menu-title">Roles and permissions Management</span>
                        <span class="menu-arrow"></span>
                    </span>

                        <div class="menu-sub menu-sub-accordion @yield('AdminsShow')">
                            <div class="menu-item">
                                <a class="menu-link @yield('AdminsActive')" href="{{ route('admin-management') }}"
                                   wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Admins Management</span>
                                </a>
                            </div>

                            <div class="menu-item">
                                <a class="menu-link @yield('rolesActive')" href="{{ route('roles') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Roles Management</span>
                                </a>
                            </div>

                            <div class="menu-item">
                                <a class="menu-link @yield('permissionAssignActive')"
                                   href="{{ route('permission-assignment') }}" wire:navigate>
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                    <span class="menu-title">Permission assignment</span>
                                </a>
                            </div>
                        </div>

                    </div>
                @endcan

                <div class="menu-item">
                    <a class="menu-link @yield('accountActive')" href="{{ url('admin/account') }}" wire:navigate>
                        <span class="menu-icon">
                            <!--begin::Svg Icon | path: icons/duotune/communication/com013.svg-->
                            <span class="svg-icon svg-icon-2">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M6.28548 15.0861C7.34369 13.1814 9.35142 12 11.5304 12H12.4696C14.6486 12 16.6563 13.1814 17.7145 15.0861L19.3493 18.0287C20.0899 19.3618 19.1259 21 17.601 21H6.39903C4.87406 21 3.91012 19.3618 4.65071 18.0287L6.28548 15.0861Z"
                                        fill="currentColor"/>
                                    <rect opacity="0.3" x="8" y="3" width="8" height="8" rx="4"
                                          fill="currentColor"/>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">Account</span>
                    </a>
                    <!--end:Menu link-->
                </div>


                <div class="menu-item">
                    <!--begin:Menu link-->
                    <a class="menu-link" onclick="return confirm('Are you sure? you want to logout')"
                       href="{{ url('admin/logout') }}">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2 rotate-180">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path opacity="0.5"
                                          d="M14.2657 11.4343L18.45 7.25C18.8642 6.83579 18.8642 6.16421 18.45 5.75C18.0358 5.33579 17.3642 5.33579 16.95 5.75L11.4071 11.2929C11.0166 11.6834 11.0166 12.3166 11.4071 12.7071L16.95 18.25C17.3642 18.6642 18.0358 18.6642 18.45 18.25C18.8642 17.8358 18.8642 17.1642 18.45 16.75L14.2657 12.5657C13.9533 12.2533 13.9533 11.7467 14.2657 11.4343Z"
                                          fill="currentColor"></path>
                                    <path
                                        d="M8.2657 11.4343L12.45 7.25C12.8642 6.83579 12.8642 6.16421 12.45 5.75C12.0358 5.33579 11.3642 5.33579 10.95 5.75L5.40712 11.2929C5.01659 11.6834 5.01659 12.3166 5.40712 12.7071L10.95 18.25C11.3642 18.6642 12.0358 18.6642 12.45 18.25C12.8642 17.8358 12.8642 17.1642 12.45 16.75L8.2657 12.5657C7.95328 12.2533 7.95328 11.7467 8.2657 11.4343Z"
                                        fill="currentColor"></path>
                                </svg>
                            </span>
                            <!--end::Svg Icon-->
                        </span>
                        <span class="menu-title">Logout</span>
                    </a>
                    <!--end:Menu link-->
                </div>

                <!--end:Menu item-->

            </div>
            <!--end::Menu-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->

</div>

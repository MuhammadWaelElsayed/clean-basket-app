<div id="kt_app_sidebar" class="app-sidebar flex-column">
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <a href="{{ url('support/tickets') }}">
            <img alt="Logo" src="{{ asset('media/logo.png') }}" class="h-60px text-center app-sidebar-logo-default" />
        </a>
    </div>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div class="app-sidebar-wrapper hover-scroll-overlay-y my-5">
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="#kt_app_sidebar_menu">
                <div class="menu-item ">
                    <a class="menu-link" href="{{ url('support/tickets') }}">
                        <span class="menu-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </span>
                        <span class="menu-title">Tickets</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-5 px-3">
            <a class="btn btn-danger w-100" href="{{ route('support.logout') }}">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>
    </div>
</div>

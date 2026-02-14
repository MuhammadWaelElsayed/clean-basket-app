<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading text-dark fw-bold fs-3 my-0">Partners Map</h1>
                </div>

            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div class="app-container container-xxl">
                <div class="card">
                    <div class="card-body">
                        <div id="map" style="height: 600px; border:1px solid #ccc;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush


@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
     <script type="module" src="{{ asset('js/leaflet-vendors-map.js') }}" defer></script>


    <script type="module">
        import { initVendorsMap } from '/js/leaflet-vendors-map.js';

        const vendors = @json($vendors);
        initVendorsMap(vendors);
    </script>
@endpush


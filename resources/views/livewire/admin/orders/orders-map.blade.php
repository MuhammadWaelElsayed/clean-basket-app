<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading text-dark fw-bold fs-3 my-0">Orders Map</h1>
                    <span class="text-muted fw-semibold fs-7 my-1 ms-1"> View orders locations on the map</span>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div class="app-container container-xxl">

                <!-- Filters -->
                <div class="card mb-5">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Filters</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Status Filter -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="PLACED">PLACED</option>
                                    <option value="READY_TO_DELIVER">READY TO DELIVER</option>
                                </select>
                            </div>

                            <!-- Vendor Filter -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vendor (المغسلة)</label>
                                <select wire:model.live="vendorFilter" class="form-select">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->business_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area Filter -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Area (الحي)</label>
                                <select wire:model.live="areaFilter" class="form-select">
                                    <option value="">All Areas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Carpet Filter -->
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Carpet Orders (طلبات السجاد)</label>
                                <div class="form-check form-switch">
                                    <input wire:model.live="carpetFilter" class="form-check-input" type="checkbox" id="carpetFilter">
                                    <label class="form-check-label" for="carpetFilter">
                                        Show only carpet orders
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Search and Clear -->
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Search</label>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search by order code, customer name, or phone...">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button wire:click="clearFilters" class="btn btn-secondary">
                                    <i class="ki-duotone ki-cross fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Clear All Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Legend -->
                <div class="card mb-5">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Map Legend & Statistics</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="custom-marker placed me-3" style="background-color: #fbbf24; border-color: #f59e0b; width: 20px; height: 20px; font-size: 6px;"></div>
                                    <span class="fw-semibold">PLACED Orders {{ collect($orders)->where('status', 'PLACED')->count() }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="custom-marker ready me-3" style="background-color: #10b981; border-color: #059669; width: 20px; height: 20px; font-size: 6px;"></div>
                                    <span class="fw-semibold">READY TO DELIVER Orders {{ collect($orders)->where('status', 'READY_TO_DELIVER')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ki-duotone ki-shop me-3 fs-2 text-primary">
                                        <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                    </i>
                                    <span class="fw-semibold">Total Orders: {{ count($orders) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ki-duotone ki-carpet me-3 fs-2 text-warning">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-semibold">Carpet Orders: {{ collect($orders)->where('has_carpet', true)->count() }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ki-duotone ki-geolocation me-3 fs-2 text-info">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                    <span class="fw-semibold">Unique Areas: {{ collect($orders)->pluck('area_name')->filter()->unique()->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="fw-bold m-0">Orders Locations Map</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- أهم نقطة: wire:ignore -->
                        <div id="ordersMap" wire:ignore style="height: 700px; border:1px solid #ccc; border-radius: 8px;"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .custom-popup { font-family: Arial, sans-serif; }
    .custom-popup .order-code { font-weight: bold; color: #1e40af; margin-bottom: 5px; }
    .custom-popup .customer-info { margin-bottom: 3px; font-size: 12px; }
    .custom-popup .status { display:inline-block; padding:2px 6px; border-radius:3px; font-size:10px; font-weight:bold; margin-bottom:3px; }
    .custom-popup .status.placed { background-color:#fbbf24; color:#92400e; }
    .custom-popup .status.ready { background-color:#10b981; color:#064e3b; }
    .custom-popup .total { font-weight:bold; color:#059669; margin-top:5px; }
    .custom-popup .vendor-info { background-color:#e3f2fd; padding:3px 6px; border-radius:3px; font-size:11px; margin:2px 0; }
    .custom-popup .area-info { background-color:#f3e5f5; padding:3px 6px; border-radius:3px; font-size:11px; margin:2px 0; }
    .custom-popup .carpet-info { background-color:#fff3e0; padding:3px 6px; border-radius:3px; font-size:11px; margin:2px 0; color:#e65100; font-weight:bold; }

    .custom-marker { background:#fff; border:2px solid; border-radius:50%; width:35px; height:35px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:9px; color:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.4); text-shadow:1px 1px 2px rgba(0,0,0,0.5); }
    .custom-marker.placed { border-color:#f59e0b; }
    .custom-marker.ready { border-color:#059669; }
    .marker-text { font-size:7px; font-weight:bold; line-height:1; }
    .custom-div-icon { background:transparent!important; border:none!important; }

    .custom-zoom-control { background:#fff; border:2px solid #ccc; border-radius:4px; width:30px; height:30px; line-height:26px; text-align:center; font-size:18px; font-weight:bold; color:#333; cursor:pointer; margin-bottom:5px; box-shadow:0 1px 5px rgba(0,0,0,0.4); user-select:none; }
    .custom-zoom-control:hover { background:#f4f4f4; border-color:#999; }
    .custom-zoom-control:active { background:#e6e6e6; }
    .zoom-in { margin-bottom:5px; }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map;
    let markersLayer;

    document.addEventListener('DOMContentLoaded', () => {
        initOrdersMap();
    });

    document.addEventListener('livewire:navigated', () => {
        initOrdersMap();
    });

    window.addEventListener('ordersUpdated', (e) => {
        // البيانات القادمة من Livewire
        const orders = (e.detail && e.detail.orders) ? e.detail.orders : [];
        renderOrdersOnMap(orders);
    });

    function initOrdersMap() {
        const el = document.getElementById('ordersMap');
        if (!el) return;

        // لو سبق وتم إنشاء خريطة على عنصر مختلف، احذف القديمة
        if (map && map.getContainer() !== el) {
            map.remove();
            map = undefined;
        }

        if (!map) {
            map = L.map(el).setView([24.7136, 46.6753], 6);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            markersLayer = L.layerGroup().addTo(map);
            addCustomZoomControls();
        }

        // إصلاح القياس بعد أي تغيّر DOM
        setTimeout(() => map.invalidateSize(), 0);
    }

    function addCustomZoomControls() {
        const zoomInButton = L.control({position: 'topright'});
        const zoomOutButton = L.control({position: 'topright'});

        zoomInButton.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'custom-zoom-control zoom-in');
            div.innerHTML = '+';
            div.title = 'Zoom In';
            div.onclick = function() { map.zoomIn(); };
            return div;
        };

        zoomOutButton.onAdd = function(map) {
            const div = L.DomUtil.create('div', 'custom-zoom-control zoom-out');
            div.innerHTML = '−';
            div.title = 'Zoom Out';
            div.onclick = function() { map.zoomOut(); };
            return div;
        };

        zoomInButton.addTo(map);
        zoomOutButton.addTo(map);
    }

    function isWithinSaudiArabia(lat, lng) {
        return lat >= 16.0 && lat <= 32.2 && lng >= 34.5 && lng <= 55.7;
    }

    function renderOrdersOnMap(orders) {
        initOrdersMap();
        if (!map || !markersLayer) return;

        markersLayer.clearLayers();

        if (!Array.isArray(orders) || orders.length === 0) {
            map.setView([24.7136, 46.6753], 6);
            return;
        }

        const bounds = L.latLngBounds();

        orders.forEach(order => {
            const lat = parseFloat(order.lat);
            const lng = parseFloat(order.lng);
            if (!isFinite(lat) || !isFinite(lng) || !isWithinSaudiArabia(lat, lng)) return;

            const iconColor = order.status === 'PLACED' ? '#fbbf24' : '#10b981';
            const borderColor = order.status === 'PLACED' ? '#f59e0b' : '#059669';

            const customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="custom-marker ${order.status === 'PLACED' ? 'placed' : 'ready'}" style="background-color:${iconColor};border-color:${borderColor};">
                         <span class="marker-text">${order.order_code}</span>
                       </div>`,
                iconSize: [35, 35],
                iconAnchor: [17, 17]
            });

            const popupContent = `
                <div class="custom-popup">
                    <div class="order-code">${order.order_code}</div>
                    <div class="customer-info"><strong>Customer:</strong> ${order.customer_name ?? ''}</div>
                    <div class="customer-info"><strong>Phone:</strong> ${order.customer_phone ?? ''}</div>
                    <div class="status ${order.status === 'PLACED' ? 'placed' : 'ready'}">
                        ${order.status === 'PLACED' ? 'PLACED' : 'READY TO DELIVER'}
                    </div>
                    <div class="customer-info"><strong>Address:</strong> ${order.address ?? ''}</div>
                    ${order.vendor_name ? `<div class="vendor-info"><strong>Vendor:</strong> ${order.vendor_name}</div>` : ''}
                    ${order.area_name ? `<div class="area-info"><strong>Area:</strong> ${order.area_name}</div>` : ''}
                    ${order.has_carpet ? `<div class="carpet-info"><strong>Contains Carpet:</strong> Yes</div>` : ''}
                    <div class="total">Total: ${Number(order.grand_total ?? 0).toFixed(2)}</div>
                </div>
            `;

            const marker = L.marker([lat, lng], { icon: customIcon }).bindPopup(popupContent);
            markersLayer.addLayer(marker);
            bounds.extend([lat, lng]);
        });

        if (bounds.isValid()) map.fitBounds(bounds, { padding: [20, 20] });
    }
</script>
@endpush

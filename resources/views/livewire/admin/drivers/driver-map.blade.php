<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading text-dark fw-bold fs-3 my-0">Drivers Map</h1>
                    <span class="text-muted fw-semibold fs-7 my-1 ms-1"> View drivers locations on the map</span>
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
                                <label class="form-label fw-semibold">Role</label>
                                <select wire:model.live="role" class="form-select">
                                    <option value="">All</option>
                                    <option value="FREELANCE">FREELANCE</option>
                                    <option value="FULL_TIME">FULL TIME</option>
                                    <option value="THIRD_PARTY">THIRD PARTY</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Vehicle type</label>
                                <select wire:model.live="vehicle_type" class="form-select">
                                    <option value="">All</option>
                                    <option value="MOTORCYCLE">MOTORCYCLE</option>
                                    <option value="CAR">CAR</option>
                                    <option value="VAN">VAN</option>
                                    <option value="TRUCK">TRUCK</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    <!-- Search and Clear -->
                    <div class="row g-3 mt-3">
                        {{--                            <div class="col-md-6">--}}
                        {{--                                <label class="form-label fw-semibold">Search</label>--}}
                        {{--                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search by order code, customer name, or phone...">--}}
                        {{--                            </div>--}}
                        <div class="col-md-6 d-flex align-items-end">
                            <button wire:click="clearFilters" wire:loading.attr="disabled"
                                    class="btn btn-secondary">
                                    <span wire:loading wire:target="clearFilters">
                                        <span class="spinner-border spinner-border-sm" role="status"
                                              aria-hidden="true"></span>
                                    </span>
                                <i wire:loading.remove class="ki-duotone ki-cross fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="fw-bold m-0">Drivers Locations Map</h3>
                        <span class="text-muted fw-semibold fs-7 my-1 ms-1">{{ count($drivers) }} drivers located</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button wire:click="loadDrivers" class="btn btn-sm btn-primary">
                            <i class="ki-duotone ki-arrows-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Refresh Map
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- أهم نقطة: wire:ignore -->
                    <div id="ordersMap" wire:ignore
                         style="height: 700px; border:1px solid #ccc; border-radius: 8px;"></div>
                </div>
            </div>

        </div>
    </div>
</div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        .custom-popup {
            font-family: Arial, sans-serif;
        }

        .custom-popup .order-code {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .custom-popup .customer-info {
            margin-bottom: 3px;
            font-size: 12px;
        }

        .custom-popup .status {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .custom-popup .status.placed {
            background-color: #fbbf24;
            color: #92400e;
        }

        .custom-popup .status.ready {
            background-color: #10b981;
            color: #064e3b;
        }

        .custom-popup .total {
            font-weight: bold;
            color: #059669;
            margin-top: 5px;
        }

        .custom-popup .vendor-info {
            background-color: #e3f2fd;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin: 2px 0;
        }

        .custom-popup .area-info {
            background-color: #f3e5f5;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin: 2px 0;
        }

        .custom-popup .carpet-info {
            background-color: #fff3e0;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin: 2px 0;
            color: #e65100;
            font-weight: bold;
        }

        .custom-marker {
            background: #fff;
            border: 2px solid;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 9px;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .custom-marker.placed {
            border-color: #f59e0b;
        }

        .custom-marker.ready {
            border-color: #059669;
        }

        .marker-text {
            font-size: 7px;
            font-weight: bold;
            line-height: 1;
        }

        .custom-div-icon {
            background: transparent !important;
            border: none !important;
        }

        .custom-zoom-control {
            background: #fff;
            border: 2px solid #ccc;
            border-radius: 4px;
            width: 30px;
            height: 30px;
            line-height: 26px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            margin-bottom: 5px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4);
            user-select: none;
        }

        .custom-zoom-control:hover {
            background: #f4f4f4;
            border-color: #999;
        }

        .custom-zoom-control:active {
            background: #e6e6e6;
        }

        .zoom-in {
            margin-bottom: 5px;
        }
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

        window.addEventListener('driversUpdated', (e) => {
            // البيانات القادمة من Livewire
            const drivers = (e.detail && e.detail.drivers) ? e.detail.drivers : [];
            renderOrdersOnMap(drivers);
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

            zoomInButton.onAdd = function (map) {
                const div = L.DomUtil.create('div', 'custom-zoom-control zoom-in');
                div.innerHTML = '+';
                div.title = 'Zoom In';
                div.onclick = function () {
                    map.zoomIn();
                };
                return div;
            };

            zoomOutButton.onAdd = function (map) {
                const div = L.DomUtil.create('div', 'custom-zoom-control zoom-out');
                div.innerHTML = '−';
                div.title = 'Zoom Out';
                div.onclick = function () {
                    map.zoomOut();
                };
                return div;
            };

            zoomInButton.addTo(map);
            zoomOutButton.addTo(map);
        }

        function isWithinSaudiArabia(lat, lng) {
            return lat >= 16.0 && lat <= 32.2 && lng >= 34.5 && lng <= 55.7;
        }

        function renderOrdersOnMap(drivers) {
            initOrdersMap();
            if (!map || !markersLayer) return;

            markersLayer.clearLayers();

            if (!Array.isArray(drivers) || drivers.length === 0) {
                map.setView([24.7136, 46.6753], 6);
                return;
            }

            const bounds = L.latLngBounds();

            drivers.forEach(driver => {
                const lat = parseFloat(driver.lat);
                const lng = parseFloat(driver.lng);
                if (!isFinite(lat) || !isFinite(lng) || !isWithinSaudiArabia(lat, lng)) return;

                console.log('lat, lng = ', lat, lng,)
                const iconColor = '#10b981';
                const borderColor = '#059669';

                const customIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div class="custom-marker" style="background-color:${iconColor};border-color:${borderColor};">
                         <span class="marker-text">${driver.name}</span>

                       </div>`,
                    iconSize: [35, 35],
                    iconAnchor: [17, 17]
                });

                const popupContent = `
                <div class="custom-popup">
                    <div class="order-code">${driver.name}</div>
                    <div class="customer-info"><strong>Phone:</strong> ${driver.phone}</div>
                    <div class="customer-info"><strong>Vendor:</strong> ${driver.vendor_name}</div>
                </div>
            `;

                const marker = L.marker([lat, lng], {icon: customIcon}).bindPopup(popupContent);
                markersLayer.addLayer(marker);
                bounds.extend([lat, lng]);
            });

            if (bounds.isValid()) map.fitBounds(bounds, {padding: [20, 20]});
        }
    </script>
@endpush

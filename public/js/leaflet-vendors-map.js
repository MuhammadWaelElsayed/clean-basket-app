// public/js/leaflet-vendors-map.js

export function initVendorsMap(vendors, containerId = 'map') {
    const map = L.map(containerId).setView([24.7136, 46.6753], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const colors = [
        '#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
        '#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe',
        '#008080', '#e6beff', '#9a6324', '#fffac8', '#800000',
        '#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
    ];

    let bounds = L.latLngBounds();

    vendors.forEach((vendor, index) => {
        const lat = parseFloat(vendor.lat);
        const lng = parseFloat(vendor.lng);
        const color = colors[index % colors.length];

        // Add marker with link to edit page
        const marker = L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`<b>${vendor.business_name}</b><br><a href="/admin/partners/${vendor.id}/edit" class="text-primary">Edit Vendor</a>`);

        bounds.extend([lat, lng]);

        // Handle areas
        let areas = [];
        try {
            if (vendor.areas && typeof vendor.areas === 'string') {
                areas = JSON.parse(vendor.areas);
            } else if (Array.isArray(vendor.areas)) {
                areas = vendor.areas;
            }
        } catch (e) {
            console.warn(`❌ Error parsing areas for ${vendor.business_name}:`, e);
        }

        if (Array.isArray(areas) && areas.length > 0) {
            const polygonCoords = areas.map(p => [parseFloat(p.lat), parseFloat(p.lng)]);
            const polygon = L.polygon(polygonCoords, {
                color: color,
                fillColor: color,
                fillOpacity: 0.3,
            }).addTo(map);

            polygon.bindPopup(`<b>${vendor.business_name}</b><br>عدد النقاط: ${polygonCoords.length}`);
            polygonCoords.forEach(coord => bounds.extend(coord));
        }
    });

    if (!bounds.isEmpty()) {
        map.fitBounds(bounds, { padding: [20, 20] });
    }
}

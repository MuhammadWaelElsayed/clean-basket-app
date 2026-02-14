// resources/js/leaflet-service.js

export function initLeafletMap(
    containerId,
    locationInputId,
    latInputId,
    lngInputId,
    defaultLat = 24.7136,
    defaultLng = 46.6753,
    initialZone = []
  ) {
    // ——— 1) إنشاء الخريطة
    const map = L.map(containerId).setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // ——— 2) FeatureGroup + Draw Controls
    const drawnItems = new L.FeatureGroup().addTo(map);
    map.addControl(new L.Control.Draw({
      edit:   { featureGroup: drawnItems, remove: true },
      draw:   { polygon: true, polyline: false, rectangle: false, circle: false, marker: false }
    }));

    // ——— 3) رسم البوليغون الابتدائي
    if (Array.isArray(initialZone) && initialZone.length) {
      const poly = L.polygon(initialZone).addTo(drawnItems);
      map.fitBounds(poly.getBounds());
      console.log('[leaflet-service] initial polygon drawn:', initialZone);
    }

    // ——— 4) الحقول المخفية
    const locIn = document.getElementById(locationInputId);
    const latIn = document.getElementById(latInputId);
    const lngIn = document.getElementById(lngInputId);

    function updateInputs(lat, lng, address = '') {
      if (!latIn || !lngIn || !locIn) return;
      console.log('[leaflet-service] updateInputs →', { lat, lng, address });
      latIn.value = lat.toFixed(6);
      lngIn.value = lng.toFixed(6);
      locIn.value = address;
      [latIn, lngIn, locIn].forEach(el =>
        el.dispatchEvent(new Event('input', { bubbles: true }))
      );
    }

    // ——— ٥) عكس العنوان (Reverse Geocode)
    async function reverseGeocode(lat, lng) {
      try {
        const resp = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`,
          { headers: { 'Accept-Language': 'ar' } }
        );
        if (!resp.ok) throw new Error(resp.statusText);
        const js = await resp.json();
        return js.display_name || '';
      } catch (err) {
        console.error('[leaflet-service] reverseGeocode failed:', err);
        return '';
      }
    }

    // ——— ٦) Marker قابل للسحب + نقر الخريطة
    const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    async function handleMarker(lat, lng) {
      console.log('[leaflet-service] handleMarker →', { lat, lng });
      const address = await reverseGeocode(lat, lng);
      updateInputs(lat, lng, address);
    }

    marker.on('dragend', () => {
      const { lat, lng } = marker.getLatLng();
      console.log('📍 marker dragend at', lat, lng);
      handleMarker(lat, lng);
    });

    map.on('click', e => {
      const { lat, lng } = e.latlng;
      console.log('📍 map click at', lat, lng);
      marker.setLatLng(e.latlng);
      handleMarker(lat, lng);
    });

    // ——— ٧) إضافة Search Control (Geocoder)
    if (L.Control.Geocoder) {
      const geocoder = L.Control.Geocoder.nominatim({
        placeholder: 'ابحث عن موقع...',
        geocodingQueryParams: { polygon_geojson: 0 }
      });
      const control = L.Control.geocoder({
        query: '',
        geocoder: geocoder,
        defaultMarkGeocode: false
      })
        .on('markgeocode', e => {
          const { center, name } = e.geocode;
          console.log('[leaflet-service] markgeocode →', e.geocode);
          // حرك الخريطة و العلامة
          map.setView(center, 16);
          marker.setLatLng(center);
          handleMarker(center.lat, center.lng);
        })
        .addTo(map);
    } else {
      console.warn('[leaflet-service] Geocoder control not found');
    }

    // ——— ٨) إرسال إحداثيات البوليغون للـ Livewire
    function emitZone(coords) {
      const root = document.querySelector('[wire\\:id]');
      if (!root || !window.Livewire?.find) return;
      const cmp = root.getAttribute('wire:id');
      const formatted = coords.map(c => ({ lat: c[0], lng: c[1] }));
      console.log('[leaflet-service] emitZone →', formatted);
      Livewire.find(cmp).call('areasChanged', formatted);
    }

    map.on(L.Draw.Event.CREATED, e => {
      console.log('▶ draw:created');
      drawnItems.clearLayers();
      drawnItems.addLayer(e.layer);
      const coords = e.layer.getLatLngs()[0].map(pt => [pt.lat, pt.lng]);
      console.log('▶ created polygon coords →', coords);
      emitZone(coords);
    });

    map.on('draw:editstart', () => console.log('✏️ draw:editstart'));
    map.on('draw:edited', e => {
      console.log('✅ draw:edited');
      e.layers.eachLayer(layer => {
        const coords = layer.getLatLngs()[0].map(pt => [pt.lat, pt.lng]);
        console.log('✏️ edited polygon coords →', coords);
        emitZone(coords);
      });
    });
    map.on('draw:editstop', () => console.log('✏️ draw:editstop'));
    map.on(L.Draw.Event.DELETED, () => {
      console.log('✖ draw:deleted');
      emitZone([]);
    });

    // ——— ٩) إنشاء الدائرة الافتراضية
    const createDefaultCircleBtn = document.getElementById('createDefaultCircle');
    if (createDefaultCircleBtn) {
      createDefaultCircleBtn.addEventListener('click', () => {
        const center = marker.getLatLng();
        const radius = 6; // 6 كيلومترات
        const numPoints = 32;
        const points = [];

        for (let i = 0; i < numPoints; i++) {
          const angle = (i / numPoints) * 2 * Math.PI;
          const lat = center.lat + (radius / 111.32) * Math.cos(angle);
          const lng = center.lng + (radius / (111.32 * Math.cos(center.lat * Math.PI / 180))) * Math.sin(angle);
          points.push([lat, lng]);
        }

        // إغلاق الدائرة
        if (points.length > 0) {
          points.push(points[0]);
        }

        // مسح أي مناطق موجودة وإضافة الدائرة الجديدة
        drawnItems.clearLayers();
        const circle = L.polygon(points).addTo(drawnItems);
        map.fitBounds(circle.getBounds());

        // إرسال الإحداثيات للـ Livewire
        emitZone(points);

        // استدعاء Livewire لإنشاء الدائرة الافتراضية
        const root = document.querySelector('[wire\\:id]');
        if (root && window.Livewire?.find) {
          const cmp = root.getAttribute('wire:id');
          Livewire.find(cmp).call('createDefaultCircleFromUI');
        }

        console.log('[leaflet-service] Created default circle with radius 6km');
      });
    }

    // ——— ١٠) قيم مبدئية
    handleMarker(defaultLat, defaultLng);

    // إظهار زر إنشاء الدائرة الافتراضية عند تحديد موقع
    function showCreateCircleButton() {
      if (createDefaultCircleBtn) {
        createDefaultCircleBtn.style.display = 'inline-block';
      }
    }

    // إخفاء الزر عند وجود مناطق محددة
    function hideCreateCircleButton() {
      if (createDefaultCircleBtn) {
        createDefaultCircleBtn.style.display = 'none';
      }
    }

    // إظهار الزر عند تحديد موقع جديد
    marker.on('dragend', showCreateCircleButton);
    map.on('click', showCreateCircleButton);

    // إخفاء الزر عند رسم منطقة
    map.on(L.Draw.Event.CREATED, hideCreateCircleButton);
    map.on('draw:edited', hideCreateCircleButton);

    // إظهار الزر عند حذف المناطق
    map.on(L.Draw.Event.DELETED, showCreateCircleButton);
  }

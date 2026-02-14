function initMap() {
  // Initialize the map
  var map = new google.maps.Map(document.getElementById('address-map'), {
      center: { lat: 24.761066566987, lng: 46.65562388579 },
      zoom: 12
  });

  // Add the search box
  var input = document.getElementById('address-input');
  var searchBox = new google.maps.places.SearchBox(input);
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

  // Bias the SearchBox results towards current map's viewport.
  map.addListener('bounds_changed', function() {
      searchBox.setBounds(map.getBounds());
  });

  var markers = [];
  var borderPolygon = null;
  var initialPolygon = null;

 
  // Define the initial polygon coordinates - Default 1st time
  let triangleCoords = [
      { lat: 24.761066566987, lng: 46.65562388579 },
      { lat: 24.761066566987, lng: 46.590441636098 },
      { lat: 24.726168440105, lng: 46.590269974721 },
      { lat: 24.723829573321, lng: 46.65562388579 },
  ];

  // Create the initial polygon
  initialPolygon  = new google.maps.Polygon({
      paths: triangleCoords,
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#FF0000',
      fillOpacity: 0.35,
      editable: true
  });
  initialPolygon.setMap(map);
  addPolygonListeners(initialPolygon);
  Livewire.on('defaultAreas', (areas) => {
      newCords = areas[0];
      initialPolygon.setPaths(newCords);
      // Adjust the map to fit the initialPolygon
      var bounds = new google.maps.LatLngBounds();
      newCords.forEach(coord => {
          bounds.extend(coord);
      });
      map.fitBounds(bounds);
      addPolygonListeners(initialPolygon);
  });

   // Listen for the event fired when the user Search locoation is Search Box
  searchBox.addListener('places_changed', function() {
    var places = searchBox.getPlaces();

    if (places.length == 0) {
        return;
    }

    // Clear out the old markers.
    markers.forEach(function(marker) {
        marker.setMap(null);
    });
    markers = [];

    // Clear the previous border polygon or circle
    if (borderPolygon) {
        borderPolygon.setMap(null);
        borderPolygon = null;
    }
    // Remove the initial polygon if it exists
    if (initialPolygon) {
        initialPolygon.setMap(null);
        initialPolygon = null;
    }

    // For each place, get the icon, name and location.
    var bounds = new google.maps.LatLngBounds();
    places.forEach(function(place) {
        if (!place.geometry) {
            console.log("Returned place contains no geometry");
            return;
        }
        console.log(place.geometry.location.lat());
        Livewire.dispatch('addressChanged', {
            locationText: place.formatted_address,
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng()
        });

        // Create a marker for each place.
        markers.push(new google.maps.Marker({
            map: map,
            title: place.name,
            position: place.geometry.location
        }));

        // Draw polygon or circle around the place
        if (place.geometry.viewport) {
            // Use the viewport bounds to create a polygon
            bounds.union(place.geometry.viewport);

            var ne = place.geometry.viewport.getNorthEast();
            var sw = place.geometry.viewport.getSouthWest();

            var polygonCoords = [
                { lat: ne.lat(), lng: ne.lng() },
                { lat: ne.lat(), lng: sw.lng() },
                { lat: sw.lat(), lng: sw.lng() },
                { lat: sw.lat(), lng: ne.lng() }
            ];

            Livewire.dispatch('areasChanged', { areas: polygonCoords });

            borderPolygon = new google.maps.Polygon({
                paths: polygonCoords,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                editable: true
            });

            borderPolygon.setMap(map);
            addPolygonListeners(borderPolygon);
        } else {
            // Use the location as the center for a small circle
            bounds.extend(place.geometry.location);

            borderPolygon = new google.maps.Circle({
                center: place.geometry.location,
                radius: 500, // radius in meters
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.35,
                editable: true
            });

            borderPolygon.setMap(map);
        }
    });

    map.fitBounds(bounds);
});


  // Initialize the drawing manager
  var drawingManager = new google.maps.drawing.DrawingManager({
      drawingMode: null,
      drawingControl: true,
      drawingControlOptions: {
          position: google.maps.ControlPosition.TOP_CENTER,
          drawingModes: ['polygon']
      },
      polygonOptions: {
          editable: true
      }
  });
  drawingManager.setMap(map);

  // Add event listener for polygon completion
  google.maps.event.addListener(drawingManager, 'polygoncomplete', function(newPolygon) {
      polygon.setMap(null); // Remove the old polygon
      polygon = newPolygon; // Set the new polygon
      console.log(polygon.getPath().getArray()); // Get new coordinates
      addPolygonListeners(polygon);
  });

  // Function to add event listeners to polygon path
  function addPolygonListeners(polygon) {
      google.maps.event.addListener(polygon.getPath(), 'set_at', function() {
          console.log(polygon.getPath().getArray());
          var areas = polygon.getPath().getArray();
          Livewire.dispatch('areasChanged', { areas });
      });

      google.maps.event.addListener(polygon.getPath(), 'insert_at', function() {
          console.log(polygon.getPath().getArray());
      });

      google.maps.event.addListener(polygon.getPath(), 'remove_at', function() {
          console.log(polygon.getPath().getArray());
      });
  }
}

// google.maps.event.addDomListener(window, 'load', initMap);

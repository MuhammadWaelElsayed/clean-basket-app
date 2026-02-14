

function initialize() {

    $('.map-input').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
    const locationInputs = document.getElementsByClassName("map-input");

    const autocompletes = [];
    const geocoder = new google.maps.Geocoder;
    for (let i = 0; i < locationInputs.length; i++) {

        const input = locationInputs[i];
        const fieldKey = input.id.replace("-input", "");
        const isEdit = document.getElementById(fieldKey + "-latitude").value != '' && document.getElementById(fieldKey + "-longitude").value != '';

        const latitude = parseFloat(document.getElementById(fieldKey + "-latitude").value) || 21.4240968;
        const longitude = parseFloat(document.getElementById(fieldKey + "-longitude").value) || 39.81733639999999;

        const map = new google.maps.Map(document.getElementById(fieldKey + '-map'), {
            center: {lat: latitude, lng: longitude},
            zoom: 13
        });
        const marker = new google.maps.Marker({
            map: map,
            draggable:true,
            position: {lat: latitude, lng: longitude},
        });
        google.maps.event.addListener(marker, 'dragend', function() 
        {
            setLocationCoordinates('address', marker.position.lat(), marker.position.lng())
            
            $.ajax('https://maps.googleapis.com/maps/api/geocode/json?latlng='+marker.position.lat()+','+marker.position.lng()+'&key=AIzaSyC9knx_xaDauRuKe5cLJ8SAvzXxLRErJVE',   // request url
                {
                    success: function (data, status, xhr) {// success callback function
                        var location = data.results[0].geometry.location;
                        let lat = location.lat;
                        let lng = location.lng;
                        let locationText=data.results[0].formatted_address;
                        Livewire.dispatch('addressChanged',{locationText, lat, lng });

                        document.getElementById("address-input").value=data.results[0].formatted_address;
                }
            });
        });
        marker.setVisible(isEdit);

        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.key = fieldKey;
        autocompletes.push({input: input, map: map, marker: marker, autocomplete: autocomplete});

    }

    for (let i = 0; i < autocompletes.length; i++) {
        const input = autocompletes[i].input;
        const autocomplete = autocompletes[i].autocomplete;
        const map = autocompletes[i].map;
        const marker = autocompletes[i].marker;

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            marker.setVisible(false);
            const place = autocomplete.getPlace();

            geocoder.geocode({'placeId': place.place_id}, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    const locationText = results[0].formatted_address;
                    const lat = results[0].geometry.location.lat();
                    const lng = results[0].geometry.location.lng();

                    Livewire.dispatch('addressChanged',{locationText, lat, lng });
                    setLocationCoordinates(autocomplete.key, lat, lng);
                }
            });

            if (!place.geometry) {
                window.alert("No details available for input: '" + place.name + "'");
                input.value = "";
                return;
            }

            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

        });
    }
}

function setLocationCoordinates(key, lat, lng) {
    const latitudeField = document.getElementById(key + "-" + "latitude");
    const longitudeField = document.getElementById(key + "-" + "longitude");
    latitudeField.value = lat;
    longitudeField.value = lng;
}
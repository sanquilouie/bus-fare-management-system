<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map with Interactive Pins and Radius</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw/dist/leaflet.draw.css" />
    <style>
        #map {
            height: 600px;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw/dist/leaflet.draw.js"></script>
    <script>
        const map = L.map('map').setView([15.486759, 120.593508], 12);

        // Tile layer for the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Group to hold all the drawn items
        const drawnItems = new L.FeatureGroup().addTo(map);

        // Initialize the draw control
        const drawControl = new L.Control.Draw({
            draw: {
                circle: true,  // Allow drawing of circles
                marker: true,  // Allow drawing of markers
                polyline: false,
                polygon: false,
                rectangle: false
            },
            edit: {
                featureGroup: drawnItems,  // Group to hold all drawn items
                remove: true                // Enable removing objects
            }
        });
        map.addControl(drawControl);

        // Array for storing the stops data
        let stops = [
            { name: "CABANATUAN TERMINAL / LAKEWOOD AVE", lat: 15.482336, lng: 120.963543, radius: 1422.13 },
            { name: "LAKEWOOD/PACIFIC", lat: 15.463518, lng: 120.951269, radius: 1040.00 },
            { name: "SUMACAB", lat: 15.446907, lng: 120.942307, radius: 1040.24 },
            { name: "STA. ROSA INTERSECTION", lat: 15.424403, lng: 120.941106, radius: 1427.11 },
            { name: "LAFUENTE", lat: 15.429033, lng: 120.920953, radius: 750.00 },
            { name: "SAN JOSEPH", lat: 15.431681, lng: 120.907221, radius: 750.00 },
            { name: "DEEP WELL (STA ROSA)", lat: 15.434088, lng: 120.893279, radius: 750.00 },
            { name: "STO ROSARIO (SN. PEDRO)", lat: 15.435372, lng: 120.878870, radius: 800.00 },
            { name: "INSPECTOR", lat: 15.438229, lng: 120.864360, radius: 800.00 },
            { name: "RAJAL (SUR NORTE)", lat: 15.443359, lng: 120.849920, radius: 800.00 },
            { name: "RAJAL CENTRO", lat: 15.445013, lng: 120.834470, radius: 800.00 },
            { name: "MALABON", lat: 15.446675, lng: 120.819707, radius: 800.00 },
            { name: "H. ROMERO", lat: 15.447336, lng: 120.804945, radius: 800.00 },
            { name: "CARMEN (PANTOC)", lat: 15.449047, lng: 120.789871, radius: 800.00 },
            { name: "STA CRUZ", lat: 15.447006, lng: 120.774390, radius: 850.00 },
            { name: "ZARAGOZA (SN. ISIDRO)", lat: 15.443365, lng: 120.758596, radius: 850.00 },
            { name: "STO ROSARIO OLD", lat: 15.444027, lng: 120.742117, radius: 850.00 },
            { name: "CONTROL", lat: 15.444621, lng: 120.726495, radius: 850.00 },
            { name: "LAPAZ (SN. ISIDRO)", lat: 15.448766, lng: 120.711335, radius: 850.00 },
            { name: "CARAMUTAN", lat: 15.457213, lng: 120.697859, radius: 850.00 },
            { name: "LAUNGCUPANG", lat: 15.465145, lng: 120.684345, radius: 850.00 },
            { name: "AMUCAO", lat: 15.480365, lng: 120.684689, radius: 850.00 },
            { name: "BALINGCANAWAY", lat: 15.489629, lng: 120.671643, radius: 850.00 },
            { name: "SAN MANUEL", lat: 15.486982, lng: 120.640743, radius: 850.00 },
            { name: "SAN JOSE", lat: 15.491945, lng: 120.655850, radius: 850.00 },
            { name: "MALIWALO", lat: 15.482019, lng: 120.626324, radius: 850.00 },
            { name: "MATATALAIB", lat: 15.485659, lng: 120.610531, radius: 850.00 },
            { name: "TARLAC TERMINAL / ST. MARYS (METRO TOWN)", lat: 15.486759, lng: 120.593508, radius: 1000.00 },
        ];

        // Function to draw markers and circles
        function addStopMarker(stop, index) {
            const marker = L.marker([stop.lat, stop.lng], {
                draggable: true  // Allow the marker to be moved
            }).addTo(drawnItems);

            // Draw a circle around the marker based on the radius
            const circle = L.circle([stop.lat, stop.lng], {
                color: 'blue',
                fillColor: '#30f',
                fillOpacity: 0.2,
                radius: stop.radius
            }).addTo(drawnItems);

            // Bind a popup to the marker
            marker.bindPopup(`<b>${stop.name}</b><br>Radius: ${stop.radius} meters`).openPopup();

            // Update stop data when the marker or circle is moved or resized
            marker.on('dragend', function (e) {
                const updatedLatLng = e.target.getLatLng();
                stop.lat = updatedLatLng.lat;
                stop.lng = updatedLatLng.lng;
                circle.setLatLng(updatedLatLng);
                
                // Update the stops array
                stops[index] = { ...stop };  // Update the stop entry in the array
                console.log(stops);  // You can remove this line, just for debugging
            });

            // Update radius when the circle is resized
            circle.on('radiuschange', function (e) {
                stop.radius = e.target.getRadius();
                
                // Update the stops array
                stops[index] = { ...stop };  // Update the stop entry in the array
                console.log(stops);  // You can remove this line, just for debugging
            });
        }

        // Add all stops to the map
        stops.forEach((stop, index) => addStopMarker(stop, index));

        // Allow drawing of new markers and circles
        map.on(L.Draw.Event.CREATED, function (event) {
            const layer = event.layer;
            drawnItems.addLayer(layer);
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map with Interactive Polygons</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw/dist/leaflet.draw.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-editable@0.7.0/dist/leaflet.editable.css" />
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
    <script src="https://unpkg.com/leaflet-editable@0.7.0/dist/leaflet.editable.js"></script>
    <script>
        const map = L.map('map').setView([15.443365, 120.758596], 12);

        // Tile layer for the map
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Group to hold all the drawn items
        const drawnItems = new L.FeatureGroup().addTo(map);

        // Initialize the draw control
        const drawControl = new L.Control.Draw({
            draw: {
                polygon: true,  // Allow drawing of polygons
                marker: false,
                circle: false,
                polyline: false,
                rectangle: false
            },
            edit: {
                featureGroup: drawnItems,  // Group to hold all drawn items
                remove: true                // Enable removing objects
            }
        });
        map.addControl(drawControl);

        // Function to save polygon data to the server
        function savePolygonData(polygon) {
            const routeName = prompt("Enter route name:");  // Ask the user for the route name
            const coordinates = polygon.getLatLngs().map(latlng => [latlng.lng, latlng.lat]);

            fetch('../actions/save_route.php', {  // Replace with the correct path to your PHP script
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    route_name: routeName,
                    coordinates: coordinates,
                    route_lat: polygon.getCenter().lat,
                    route_long: polygon.getCenter().lng,
                    radius: 500  // Example fixed radius, adjust as needed
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Route saved:', data);
            })
            .catch(error => {
                console.error('Error saving route:', error);
            });
        }

        // Use this function when a polygon is drawn
        map.on(L.Draw.Event.CREATED, function (event) {
            const layer = event.layer;
            drawnItems.addLayer(layer);

            // Save the drawn polygon to the database
            savePolygonData(layer);
        });

        // Fetch saved route data from the server
        fetch('../actions/fetch_routes.php')  // Replace with your actual PHP file path
            .then(response => response.json())
            .then(data => {
                data.forEach(route => {
                    const latlngs = route.coordinates.map(coord => [coord[1], coord[0]]);
                    const polygon = L.polygon(latlngs, {
                        color: 'blue',
                        fillColor: '#30f',
                        fillOpacity: 0.2
                    }).addTo(map);

                    // Bind a popup with the route name
                    polygon.bindPopup(`<b>${route.route_name}</b><br>Radius: ${route.radius} meters`);
                });
            })
            .catch(error => {
                console.error('Error fetching route data:', error);
            });
    </script>
</body>
</html>

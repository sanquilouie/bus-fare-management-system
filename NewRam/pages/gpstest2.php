<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaflet Map with Polygon Edit</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        #map { width: 100%; height: 500px; }
    </style>
</head>
<body>
    <div id="map"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const map = L.map('map').setView([15.443365, 120.758596], 12);

        // Tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Initialize the drawing control
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems,
            },
            draw: {
                polygon: true,
                circle: false,
                rectangle: false,
                marker: false
            }
        });
        map.addControl(drawControl);

        // When a polygon is drawn
        map.on('draw:created', function (e) {
            const layer = e.layer;
            drawnItems.addLayer(layer); // Add the polygon to the drawnItems feature group

            // Use SweetAlert to prompt for the route name
            Swal.fire({
                title: 'Enter a name for this route:',
                input: 'text',
                inputPlaceholder: 'Route Name',
                showCancelButton: true,
                confirmButtonText: 'Save',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to enter a name!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const routeName = result.value; // Get the entered route name

                    if (routeName) {
                        // Get the coordinates of the polygon
                        const coordinates = JSON.stringify(layer.getLatLngs());

                        // Save the coordinates and name to the database (AJAX)
                        fetch('../actions/save_polygon.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                route_name: routeName,
                                coordinates: coordinates,
                                route_lat: layer.getBounds().getCenter().lat,
                                route_long: layer.getBounds().getCenter().lng,
                                radius: 0 // Set appropriately
                            })
                        })
                        .then(response => response.json())
                        .then(data => console.log('Polygon saved:', data))
                        .catch(error => console.error('Error:', error));
                    }
                }
            });
        });

        // Enable editing for existing polygons (on load)
        map.on('draw:edited', function (e) {
            const updatedLayers = e.layers;
            updatedLayers.eachLayer(function (layer) {
                const coordinates = JSON.stringify(layer.getLatLngs());

                // You don't need to ask for a new name, just use the current name
                const routeName = layer.route_name; // Keep the current route name

                // Update the database with the new coordinates
                fetch('../actions/update_polygon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        route_id: layer.route_id, // You must set this on the layer when it is first created or loaded
                        route_name: routeName, // Keep the old name
                        coordinates: coordinates
                    })
                })
                .then(response => response.json())
                .then(data => console.log('Polygon updated:', data))
                .catch(error => console.error('Error:', error));
            });
        });

        // Load polygons from the database
        fetch('../actions/load_polygons.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(route => {
                    const coordinates = JSON.parse(route.coordinates);
                    const polygon = L.polygon(coordinates).addTo(drawnItems);
                    polygon.route_id = route.route_id; // Set route_id for editing later
                    polygon.route_name = route.route_name; // Store the route name

                    // Bind popup with the route name
                    polygon.bindPopup(`<b>${route.route_name}</b>`);
                });
            })
            .catch(error => console.error('Error loading polygons:', error));
    </script>
</body>
</html>

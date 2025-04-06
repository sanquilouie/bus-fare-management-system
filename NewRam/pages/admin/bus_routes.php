<?php
session_start();
include '../../includes/connection.php';


if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Routes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        #map { width: 100%; height: 500px; }
    </style>
</head>
<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-10">
                <h2>Bus Routes</h2>
                    <div id="map"></div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        const map = L.map('map').setView([15.443365, 120.758596], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        const drawControl = new L.Control.Draw({
            edit: {
                featureGroup: drawnItems,
                remove: true
            },
            draw: {
                polygon: true,
                polyline: false,
                circle: false,
                circlemarker: false,
                rectangle: false,
                marker: false
            }
        });
        map.addControl(drawControl);
        map.on('draw:created', function (e) {
            const layer = e.layer;
            drawnItems.addLayer(layer); 

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
                    const routeName = result.value;

                    if (routeName) {
                        const coordinates = JSON.stringify(layer.getLatLngs());
                        fetch('../../actions/save_polygon.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                route_name: routeName,
                                coordinates: coordinates,
                                route_lat: layer.getBounds().getCenter().lat,
                                route_long: layer.getBounds().getCenter().lng,
                                radius: 0
                            })
                        })
                        .then(response => response.json())
                        .then(data => console.log('Polygon saved:', data))
                        .catch(error => console.error('Error:', error));
                    }
                }
            });
        });

        map.on('draw:edited', function (e) {
            const updatedLayers = e.layers;
            updatedLayers.eachLayer(function (layer) {
                const coordinates = JSON.stringify(layer.getLatLngs());

                const routeName = layer.route_name;

                fetch('../../actions/update_polygon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        route_id: layer.route_id,
                        route_name: routeName,
                        coordinates: coordinates
                    })
                })
                .then(response => response.json())
                .then(data => console.log('Polygon updated:', data))
                .catch(error => console.error('Error:', error));
            });
        });

        map.on('draw:deleted', function (e) {
            const deletedLayers = e.layers;
            const totalDeleted = deletedLayers.getLayers().length;

            if (totalDeleted > 1) {
                let timerInterval;
                Swal.fire({
                    icon: 'warning',
                    title: 'Delete One at a Time',
                    html: 'Reverting in <b></b> seconds...',
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: () => {
                        const b = Swal.getHtmlContainer().querySelector('b');
                        timerInterval = setInterval(() => {
                            b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                        }, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(() => {
                    location.reload(); // Revert deletion
                });

                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    deletedLayers.eachLayer(function (layer) {
                        const routeId = layer.route_id;
                        if (routeId) {
                            fetch('../../actions/delete_polygon.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ route_id: routeId })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Polygon deleted:', data);
                                Swal.fire('Deleted!', 'Route has been removed.', 'success');
                            })
                            .catch(error => {
                                console.error('Error deleting polygon:', error);
                                Swal.fire('Error!', 'Failed to delete route.', 'error');
                            });
                        }
                    });
                } else {
                    location.reload();
                }
            });
        });

        fetch('../../actions/load_polygons.php')
            .then(response => response.json())
            .then(data => {
                data.forEach(route => {
                    const coordinates = JSON.parse(route.coordinates);
                    const polygon = L.polygon(coordinates).addTo(drawnItems);
                    polygon.route_id = route.route_id;
                    polygon.route_name = route.route_name;

                    polygon.bindPopup(`<b>${route.route_name}</b>`);
                });
            })
            .catch(error => console.error('Error loading polygons:', error));
    </script>
</body>
</html>

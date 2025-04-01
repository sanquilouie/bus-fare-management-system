<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Fare System - Location Tracking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
        }
        h2 {
            color: #333;
        }
        #location, #coords {
            font-size: 20px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
<h1>Bus Fare System</h1>
<h2 id="location">Current Stop: Loading...</h2>
<h2 id="coords">Latitude: ---, Longitude: ---</h2>

<script>
    // Predefined bus stops with latitude, longitude, and radius
    const stops = [
        { name: "Terminal", lat: 15.4740, lng: 120.9609, radius: 1207 },
        { name: "Pacific", lat: 15.4573, lng: 120.9465, radius: 1207 },
        { name: "Sumacab", lat: 15.4425, lng: 120.9432, radius: 482.80 },
        { name: "Sta. Rosa", lat: 15.4319, lng: 120.9384, radius: 804.67 },
        { name: "La Fuente", lat: 15.4289, lng: 120.9283, radius: 965.60 },
        { name: "Louie", lat: 15.373645, lng: 120.922214, radius: 321.87 },
    ];

    // Function to calculate distance between two coordinates (Haversine formula)
    function getDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * (Math.PI / 180);
        const dLng = (lng2 - lng1) * (Math.PI / 180);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * (Math.PI / 180)) * Math.cos(lat2 * (Math.PI / 180)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c; // Distance in meters
    }

    // Function to check which stop the bus is near
    function getCurrentStop(lat, lng) {
        for (let stop of stops) {
            let distance = getDistance(lat, lng, stop.lat, stop.lng);
            if (distance < stop.radius) {
                return stop.name; // Bus is within the stop's radius
            }
        }
        return "Unknown Location"; // Not near any stop
    }

    // Function to get the bus's live GPS location
    function getBusLocation() {
        if (!navigator.geolocation) {
            document.getElementById("location").innerText = "Geolocation is not supported.";
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;
                let currentStop = getCurrentStop(latitude, longitude);

                console.log(`Latitude: ${latitude}, Longitude: ${longitude}`);
                console.log(`Current Stop: ${currentStop}`);

                // Update the webpage with the current stop and coordinates
                document.getElementById("location").innerText = `${currentStop}`;
                document.getElementById("coords").innerText = `Latitude: ${latitude}, Longitude: ${longitude}`;
            },
            (error) => {
                console.error("Error getting location:", error);
                document.getElementById("location").innerText = "Error getting location.";
                document.getElementById("coords").innerText = "Latitude: ---, Longitude: ---";
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    // Call function every 10 seconds to update location
    setInterval(getBusLocation, 10000);

    // Run the function once when the page loads
    getBusLocation();
</script>

</body>
</html>

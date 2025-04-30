<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Conductor' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$bus_number = isset($_SESSION['bus_number']) ? $_SESSION['bus_number'] : 'Unknown Bus Number';
$conductorac = isset($_SESSION['account_number']) ? $_SESSION['account_number'] : 'unknown conductor account number';
$driverName = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : 'unknown driver name';
$conductorName = isset($_SESSION['conductor_name']) ? $_SESSION['conductor_name'] : 'unknown conductor name';
$driverac = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : null;
$driverID = isset($_SESSION['driver_account_number']) ? $_SESSION['driver_account_number'] : null;


$conductorName = $firstname . ' ' . $lastname;
 
if (!isset($bus_number) || !isset($driverID)) {
    header("Location: set_bus.php");
    exit();
}

// Fetch routes
$routes = [];
$query = "SELECT * FROM fare_routes ORDER BY post ASC";
$result = $conn->query($query);
$balance = 0;

while ($row = $result->fetch_assoc()) {
    $routes[] = $row;
}

// Fetch base fare and additional fare from fare_settings table
$fareSettingsQuery = "SELECT * FROM fare_settings LIMIT 1"; // Assuming there's only one record
$fareSettingsResult = $conn->query($fareSettingsQuery);
$fareSettings = $fareSettingsResult->fetch_assoc();


$discountPercentage = $fareSettings['discount_percentage']; // Fetch discount percentage
$specialPercentage = $fareSettings['special_percentage'];

// Store passengers in a session to track those currently on the bus
if (!isset($_SESSION['passengers'])) {
    $_SESSION['passengers'] = [];
}

// Function to fetch balance based on RFID
function logPassengerEntry($rfid, $fromRoute, $toRoute, $fare, $conductorName, $conductorac, $driverac, $driverID, $busNumber, $transactionNumber, $conn)
{
    // First, insert the passenger log
    $query = "INSERT INTO passenger_logs (rfid, from_route, to_route, fare, conductor_name, conductor_id, driver_name, driver_id, bus_number, transaction_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssssss", $rfid, $fromRoute, $toRoute, $fare, $conductorName, $conductorac, $driverac, $driverID, $busNumber, $transactionNumber);
    $stmt->execute();
    $stmt->close();

    // Then, update the useracc points (5% of fare)
    $points = $fare * 0.05;
    $updateQuery = "UPDATE useracc SET points = points + ? WHERE account_number = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ds", $points, $rfid);
    $updateStmt->execute();
    $updateStmt->close();
}

function getUserBalance($rfid, $conn)
{
    $query = "SELECT balance FROM useracc WHERE account_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $rfid);
    $stmt->execute();
    $stmt->bind_result($balance);
    $result = $stmt->fetch();
    $stmt->close();

    // Return balance if found, otherwise return false
    return $result ? $balance : false;
}

// Function to deduct fare from user's balance
function deductFare($rfid, $fare, $conn)
{
    $query = "UPDATE useracc SET balance = balance - ? WHERE account_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ds", $fare, $rfid);
    $stmt->execute();
    $stmt->close();
}

// Handle the POST request to get the user balance and update balance after fare deduction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['direction'])) {
        $_SESSION['direction'] = $_POST['direction'];
        $bus_number = $_SESSION['bus_number']; 
    
        $query = "UPDATE businfo 
                  SET destination = ? 
                  WHERE bus_number = ?";
    
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ss", $_SESSION['direction'], $bus_number);
            if ($stmt->execute()) {
                echo "Destination updated successfully.";
            } else {
                echo "Error updating destination: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing query: " . $db->error;
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['fromRoute'], $data['toRoute'], $data['fareType'], $data['passengerQuantity'])) {
        $rfid = isset($data['rfid']) ? $data['rfid'] : ''; // RFID is optional for cash payments
        $fromRoute = $data['fromRoute'];
        $toRoute = $data['toRoute'];
        $fareType = $data['fareType'];
        $passengerQuantity = $data['passengerQuantity'];
        $transactionNumber = $data['transactionNumber'];

        // Fetch balance for RFID if not a cash payment
        if (!empty($rfid)) {
            $balance = getUserBalance($rfid, $conn);
            if ($balance === false) {
                echo json_encode(['status' => 'error', 'message' => 'RFID not found or invalid.']);
                exit;
            }
        } else {
            $balance = 0; // No balance check for cash payment
        }

        // Calculate distance
        $distance = abs($fromRoute['post'] - $toRoute['post']);
        $_SESSION['distance'] = $distance;
        $fare = $fareSettings['base_fare']; // Default fare for first 4 km
        if ($distance > 4) {
            $fare += ($distance - 4) * $fareSettings['additional_fare'];
        }

        // Calculate total fare with passenger quantity
        $totalFare = $fare * $passengerQuantity;

       // Apply discount if applicable
if ($fareType === 'discounted') {
    $totalFare *= (1 - ($discountPercentage / 100)); // Apply the discount based on the fetched percentage
}else if($fareType === 'special'){
    $totalFare *= (1 - ($specialPercentage / 100));
}

        if (empty($rfid)) { // Check if payment is made in cash
            $totalFare = round($totalFare); // Round to the nearest whole number
        }

        // If RFID and balance are sufficient, deduct the fare
        if (empty($rfid) || $balance >= $totalFare) {
            if (!empty($rfid)) {
                deductFare($rfid, $totalFare, $conn);
            }

            // Track the passenger
            $_SESSION['passengers'][] = [
                'rfid' => $rfid,
                'fromRoute' => $fromRoute,
                'toRoute' => $toRoute,
                'fare' => $totalFare, // Store the discounted fare
                'status' => 'onBoard',
                'quantity' => $passengerQuantity // Store the quantity of passengers
            ];

            $loggedRfid = !empty($rfid) ? $rfid : 'cash'; // Use 'cash' if payment is made in cash
            logPassengerEntry($loggedRfid, $fromRoute['route_name'], $toRoute['route_name'], $totalFare, $conductorName, $conductorac, $driverac,$driverID, $bus_number, $transactionNumber, $conn);

            echo json_encode([
                'status' => 'success',
                'message' => 'Fare deducted successfully.',
                'new_balance' => $balance - $totalFare,
                'fare' => $totalFare, // This should be the discounted fare
                'distance' => $distance
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient balance. Please load your account.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
    }
    exit;
}


// Driver dashboard functionality (for fetching passengers and their destinations)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['dashboard'])) {
    // Fetch passengers currently on board
    $passengers = $_SESSION['passengers'];

    // Group passengers by destination and count them
    $destinationCount = [];

    foreach ($passengers as $passenger) {
        $destination = $passenger['toRoute']['route_name'];
        $quantity = $passenger['quantity']; // Get the quantity of this passenger

        // Initialize the destination count if not already set
        if (!isset($destinationCount[$destination])) {
            $destinationCount[$destination] = 0;
        }

        // Add the quantity of this passenger to the destination count
        $destinationCount[$destination] += $quantity;

        // Check if this passenger has already been counted as gotten off
        if (isset($passenger['getOff']) && $passenger['getOff'] === true) {
            $destinationCount[$destination] -= $quantity; // Remove the quantity of this passenger
        }


        // Ensure the count doesn't go negative
        foreach ($destinationCount as $destination => $count) {
            if ($count < 0) {
                $destinationCount[$destination] = 0; // Reset to zero if negative
            }
        }
    }

    // Return the grouped data to the driver
    echo json_encode([
        'status' => 'success',
        'destination_count' => $destinationCount
    ]);
    exit;
}

// Handle passenger removal (for when they get off)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['removePassenger'])) {
    $destination = $_GET['destination'];

    // Logic to find and update the passenger's 'getOff' status
    $passengers = &$_SESSION['passengers'];
    $found = false; // Flag to check if we found a passenger

    foreach ($passengers as &$passenger) {
        if ($passenger['toRoute']['route_name'] === $destination && !isset($passenger['getOff'])) {
            $passenger['getOff'] = true; // Mark the passenger as gotten off
            $found = true; // Set the flag to true
            break; // Exit the loop after removing one passenger
        }
    }

    if ($found) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No passenger found to remove.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['removeAllPassengerDestination'])) {
    $destination = $_GET['destination'];

    // Logic to update all passengers' 'getOff' status for the destination
    $passengers = &$_SESSION['passengers'];
    $removedCount = 0; // Count how many passengers were removed

    foreach ($passengers as &$passenger) {
        if ($passenger['toRoute']['route_name'] === $destination && !isset($passenger['getOff'])) {
            $passenger['getOff'] = true; // Mark the passenger as gotten off
            $removedCount++; // Increment the count of removed passengers
        }
    }

    if ($removedCount > 0) {
        echo json_encode(['status' => 'success', 'message' => "$removedCount passengers removed."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No passengers found to remove.']);
    }
    exit;
}

$query = "SELECT base_fare, additional_fare FROM fare_settings WHERE id = 1"; // Change the WHERE clause as needed
$result = $conn->query($query);

// Check if the query returned a result
if ($result->num_rows > 0) {
    // Fetch the base_fare and additional_fare
    $row = $result->fetch_assoc();
    $base_fare = $row['base_fare'];
    $additional_fare = $row['additional_fare'];
} else {
    // Default values in case the query fails or no results
    $base_fare = 14;
    $additional_fare = 2;
}
// Close connection after all operations are done
// Handle removal of all passengers
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['removeAllPassengers'])) {
    // Clear the passengers session
    $_SESSION['passengers'] = [];
    echo json_encode(['status' => 'success']);
    exit;
}

//include '../../actions/bus_fare_config.php';

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Fare Calculator</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700,800,900">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="/NewRam/assets/js/NFCScanner.js"></script>
</head>
<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>   
    <div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-1">
    <button class="btn btn-link settings-btn" onclick="openSettings()" title="Settings">
        <i class="fas fa-cog fa-2xl"></i>
    </button>
        <h2>Bus Fare</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <form id="fareForm">
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 120px;">
                        <div class="card shadow-sm text-center p-3">
                            <h5 class="form-label mb-2" style="color: #007BFF;">Distance (KM)</h5>
                            <span id="kmLabel" class="h4 text-primary font-weight-bold">0 km</span>
                        </div>
                        <div class="card shadow-sm text-center p-3">
                            <h5 class="form-label mb-2" style="color: #007BFF;">Total Fare (₱)</h5>
                            <span id="fareLabel" class="h4 text-success font-weight-bold">₱0.00</span>
                        </div>
                    </div>
                    <!-- Route Selection -->
                    <div class="row mb-1">
                        <div class="col-md-6">
                            <label for="fromRoute" class="form-label" style="margin-bottom: .1rem; margin-left: .6rem; font-size: 1rem;">From</label>
                                <p id="fromRoute"></p>
                                <h1 id="displayRouteName" class="display-6 text-center fw-bold"></h1>
                                <h2 id="location"></h2>
                                <h2 id="isgeo"></h2>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select id="toRoute" name="toRoute" class="form-select">
                                    <option value="" disabled selected>Select Destination</option>
                                    <?php foreach ($routes as $route): ?>
                                        <option value="<?= htmlspecialchars(json_encode($route), ENT_QUOTES, 'UTF-8'); ?>">
                                            <?= htmlspecialchars($route['route_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="toRoute">To</label>
                            </div>
                        </div>
                    </div>

                    <!-- Fare Type and Passenger Quantity -->
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label for="passengerQuantity" class="form-label">Number of Passengers</label>
                            <div class="input-group">
                                <input type="number" id="passengerQuantity" name="passengerQuantity"
                                    class="form-control text-center" value="1" min="1" max="10">
                                <button class="btn btn-danger" type="button" onclick="updateValue(-1)">−</button>
                                <button class="btn btn-success" type="button" onclick="updateValue(1)">+</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="fareType" class="form-label">Fare Type</label>
                            <select id="fareType" name="fareType" class="form-select">
                                <option value="regular">Regular</option>
                                <option value="discounted">Student/Senior (<?= htmlspecialchars($discountPercentage); ?>% Off)</option>
                                <option value="special">Special (<?= htmlspecialchars($specialPercentage); ?>% Off)</option>
                            </select>
                        </div>  
                    </div>
                </form>
                

                 <form id="directionForm" method="POST" style="display: none;">
                    <input type="hidden" name="direction" id="directionInput">
                </form>
                <!-- Fare Result -->

                <div class="d-flex justify-content-center align-items-center mb-4">
                    <button class="btn btn-primary mx-1 form-control" onclick="processPayment('cash')">CASH</button>
                    <button class="btn btn-success mx-1 form-control" onclick="promptRFIDInput()">NFC</button>
                </div>

                <div class="card shadow-sm mb-5">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="mb-0">Passenger Destinations</h4>
                        <button class="btn btn-danger ms-auto" onclick="removeAllPassengers()">Remove All Passengers</button>
                    </div>
                    <div class="card-body">
                        <table id="destinationTable" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Destination</th>
                                    <th>Number of Passengers</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Destination rows will be inserted here dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    let currentDirection = "<?= $_SESSION['direction'] ?? '' ?>"; // make it mutable
    let receiptShown = false;

    function openSettings() {
        Swal.fire({
            title: 'Settings',
            html: `
                <div class="mb-2 text-start fw-bold">
                    <label for="tripMode" class="form-label">Trip Mode</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="btnradio" id="btnradio1" autocomplete="off">
                            <label class="btn btn-outline-primary w-100" for="btnradio1" onclick="window.location.href='busfare_manual.php'">Manual</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="btnradio" id="btnradio2" autocomplete="off"  checked>
                            <label class="btn btn-outline-primary w-100" for="btnradio2" onclick="window.location.href='busfare_auto.php'">Auto</label>
                        </div>
                    </div>
                </div>
                <div class="mb-2 text-start fw-bold">
                    <label for="directionDropdown" class="form-label">Direction</label>
                    <select class="form-select" id="directionDropdown">
                        <option disabled value="">Select Direction</option>
                        <option value="East to West">East to West</option>
                        <option value="West to East">West to East</option>
                    </select>
                </div>
            `,
            didOpen: () => {
                const dropdown = document.getElementById('directionDropdown');
                if (dropdown && currentDirection) {
                    dropdown.value = currentDirection;
                }

                dropdown.addEventListener('change', () => {
                    const selected = dropdown.value;

                    fetch('../../actions/update_direction.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `direction=${encodeURIComponent(selected)}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            currentDirection = selected; // update local variable
                            console.log('Direction updated successfully.');
                        } else {
                            console.error('Failed to update direction.');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            },
            showCloseButton: true,
            showConfirmButton: false,
            width: 500
        });
    }

    function updateValue(delta) {
        const input = document.getElementById('passengerQuantity');
        let currentValue = parseInt(input.value) || 0;
        const min = parseInt(input.min) || 1;
        const max = parseInt(input.max) || 10;

        let newValue = currentValue + delta;
        newValue = Math.max(min, Math.min(max, newValue));
        input.value = newValue;
    }


    function getRouteData() {
        return fetch('../../actions/load_polygon_to_busfare.php')
            .then(response => response.json())
            .then(data => {
                return data;
            })
            .catch(error => console.error("Error fetching route data:", error));
    }

    function isInsidePolygon(lat, lng, polygon) {
        let inside = false;
        let x = lat, y = lng;
        for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
            let xi = polygon[i].lat, yi = polygon[i].lng;
            let xj = polygon[j].lat, yj = polygon[j].lng;
            let intersect = ((yi > y) != (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    function getBusLocation() {
        if (navigator.geolocation) {
            console.log("Geolocation is supported.");
        } else {
            console.log("Geolocation is NOT supported.");
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                let latitude = position.coords.latitude;
                let longitude = position.coords.longitude;

                getRouteData().then(stops => {
                    let currentStop = "Unknown Location";

                    for (let stop of stops) {
                        if (stop.polygon && stop.polygon.length > 0) {
                            let polygon = stop.polygon[0].map(coord => ({
                                lat: parseFloat(coord.lat),
                                lng: parseFloat(coord.lng)
                            }));
                            if (isInsidePolygon(latitude, longitude, polygon)) {
                                currentStop = stop.route_name;
                                break;
                            }
                        }
                    }

                    //console.log(`Latitude: ${latitude}, Longitude: ${longitude}`);
                    //console.log(`Current Stop: ${currentStop}`);

                    fetch('../../actions/get_fare_routes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ currentStop: currentStop })
                    })
                    .then(response => response.json())
                    .then(data => {
                    console.log("Response Data:", data);
                    if (data.error) {
                        document.getElementById("fromRoute").innerText = {};
                        document.getElementById("fromRoute").style.display = 'none';
                        document.getElementById("displayRouteName").innerText = data.error;
                    } else {
                        const routeName = data.route_name || "Route name not available";

                        document.getElementById("fromRoute").innerText = JSON.stringify(data, null, 2);
                        document.getElementById("fromRoute").style.display = 'none';
                        document.getElementById("displayRouteName").innerText = routeName;

                        // Send the route name to the server to update the database
                        fetch('../../actions/update_route.php', {  // Replace '/update_route.php' with the actual endpoint
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                route_name: routeName
                            })
                        })
                        .then(response => {
                            console.log('Raw response:', response);
                            return response.json();  // This line may throw if response is not valid JSON
                        })
                        .then(responseData => {
                            if (responseData.success) {
                                console.log('Route name updated successfully!');
                            } else {
                                console.error('Failed to update route name.');
                            }
                        })
                        .catch(error => console.error('Error updating route name:', error));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });

                });
            },
            (error) => {
                console.error("Error getting location:", error);
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }
        setInterval(getBusLocation, 10000);
        getBusLocation();


        const baseFare = <?php echo $base_fare; ?>;
        const additionalFare = <?php echo $additional_fare; ?>;
        const driverName = "<?= $_SESSION['driver_name'] ?>";  // PHP variable for driver name

        document.getElementById('fromRoute').addEventListener('change', updateDistance);
        document.getElementById('toRoute').addEventListener('change', updateDistance);
        document.getElementById('fareType').addEventListener('change', updateDistance);
        document.getElementById('passengerQuantity').addEventListener('change', updateDistance);

        function updateDistance() {
            const fromRouteValue = document.getElementById('fromRoute').innerText;
            const toRouteValue = document.getElementById('toRoute').value;
            const kmLabel = document.getElementById('kmLabel');
            const fareLabel = document.getElementById('fareLabel');
            const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10); // Get passenger quantity
            console.log("From Route: ", fromRouteValue);
            console.log("To Route: ", toRouteValue);
            if (fromRouteValue && toRouteValue) {
                try {
                    const fromRoute = JSON.parse(fromRouteValue);
                    const toRoute = JSON.parse(toRouteValue);

                    // Calculate the distance in kilometers
                    const distance = Math.abs(fromRoute.post - toRoute.post);
                    kmLabel.textContent = `${distance} km`;

                    // Calculate the fare based on the distance
                    let totalFare = baseFare; // Start with the base fare for the first 4 km
                    if (distance > 4) {
                        // Add additional fare for kilometers beyond the first 4 km
                        totalFare += (distance - 4) * additionalFare;
                    }

                    // Apply discount if applicable
                    const fareType = document.getElementById('fareType').value;
                    if (fareType === 'discounted') {
                        totalFare *= 0.8; // Apply 20% discount
                    } else if (fareType === 'special'){
                        totalFare *= 0.5;
                    }

                    // Calculate total fare with passenger quantity
                    totalFare *= passengerQuantity; // Multiply by the number of passengers

                    fareLabel.textContent = `₱${totalFare.toFixed(2)}`;
                } catch (error) {
                    console.error('Error parsing route data:', error);
                    kmLabel.textContent = "Invalid route data";
                    fareLabel.textContent = "₱0.00";
                }
            } else {
                kmLabel.textContent = "0 km";
                fareLabel.textContent = "₱0.00";
            }
        }

        async function validateRoutesAndBus() {
            const fromRoute = document.getElementById('fromRoute').value;
            const toRoute = document.getElementById('toRoute').value;
            const direction = "<?= $_SESSION['direction'] ?? '' ?>";

            if (!fromRoute || !toRoute) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Missing Selection',
                    text: 'Please select both a starting point and a destination.',
                });
                return false;
            }

            if (!direction) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Missing Selection',
                    text: 'Please set direction first in settings.',
                });
                return false;
            }

            // Check conductor assignment from the server
            const response = await fetch('../../actions/check_conductor_businfo.php');
            const result = await response.json();

            if (!result.success) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Not Assigned',
                    text: result.error || 'You are not assigned to any bus',
                }).then(() => {
                    window.location.href = '../../auth/logout.php';
                });
                return false;;
            }

            return true;
        }

        async function fetchDashboardData() {
            try {
                const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>?dashboard=true', {
                    method: 'GET',
                });
                const data = await response.json();
                if (data.status === 'success') {
                    updateDashboard(data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to fetch destination data.',
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Something went wrong! Try again.',
                });
            }
        }

        function updateDashboard(data) {
            const tableBody = document.querySelector("#destinationTable tbody");
            tableBody.innerHTML = ''; // Clear existing rows

            // Iterate through the destinations and passenger count
            for (const [destination, count] of Object.entries(data.destination_count)) {
                if (count > 0) {
                    const row = document.createElement("tr");

                    row.innerHTML = `
                <td>${destination}</td>
                <td>${count}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="removePassenger('${destination}')">-</button>
                    <button class="btn btn-danger btn-sm" onclick="removeAllPassengerDestination('${destination}')">--</button>
                </td>
            `;
                    tableBody.appendChild(row);
                }
            }
        }

        async function removeAllPassengerDestination(destination) {
            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: `You are about to remove all passengers going to ${destination}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove all',
                cancelButtonText: 'Cancel'
            });

            if (confirmation.isConfirmed) {
                const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>?removeAllPassengerDestination=true&destination=' + destination, {
                    method: 'GET',
                });

                const data = await response.json();
                if (data.status === 'success') {
                    fetchDashboardData(); // Refresh the passenger list
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to remove all passengers.',
                    });
                }
            }
        }

        async function removePassenger(destination) {
            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: `You are about to remove a passenger going to ${destination}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel'
            });

            if (confirmation.isConfirmed) {
                const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>?removePassenger=true&destination=' + destination, {
                    method: 'GET',
                });

                const data = await response.json();
                if (data.status === 'success') {
                    fetchDashboardData(); // Refresh the passenger list
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to remove passenger.',
                    });
                }
            }
        }

        async function removeAllPassengers() {
            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to remove all passengers from the list.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove all',
                cancelButtonText: 'Cancel'
            });

            if (confirmation.isConfirmed) {
                const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>?removeAllPassengers=true', {
                    method: 'GET',
                });

                const data = await response.json();
                if (data.status === 'success') {
                    fetchDashboardData(); // Refresh the passenger list
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to remove passengers.',
                    });
                }
            }
        }

        // Automatically fetch data when modal is opened
        window.onload = function () {
            fetchDashboardData(); // Fetch data initially
            setInterval(fetchDashboardData, 5000); // Refresh data every 5 seconds
        };

        function generateTransactionNumber() {
            const timestamp = Date.now();
            const randomNum = Math.floor(Math.random() * (9999 - 1000 + 1)) + 1000;
            return `${timestamp}${randomNum}`;
        }

        async function promptRFIDInput() {
            const fromRouteValue = document.getElementById('fromRoute').innerText;
            const toRouteValue = document.getElementById('toRoute').value;
            const distance = Math.abs(fromRoute.post - toRoute.post);
            const transactionNumber = generateTransactionNumber();
            const paymentMethod = 'RFID';

            console.log("Generated Transaction Number:", transactionNumber); // Debugging line
            console.log("Distance:", distance); // Debugging line
            console.log("Payment Method:", paymentMethod);

            const isValid = await validateRoutesAndBus();
            if (!isValid) {
                return;
            }

            Swal.fire({
                title: 'Enter RFID',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                showConfirmButton: true, // ✅ Show Confirm button
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                inputPlaceholder: 'Scan your RFID here',
                didOpen: () => {
                    const inputField = Swal.getInput();
                    if (inputField) {
                        activeInput = inputField;
                        inputField.focus();

                        // Optional: still allow Enter key for quick submission
                        inputField.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter') {
                                Swal.clickConfirm();  // Simulate clicking the confirm button
                            }
                        });
                    }
                },
                preConfirm: () => {
                    const rfid = Swal.getInput().value.trim();
                    if (!rfid) {
                        Swal.showValidationMessage('Please scan or enter your RFID.');
                        return false;
                    }

                    const fromRoute = JSON.parse(document.getElementById('fromRoute').value);
                    const toRoute = JSON.parse(document.getElementById('toRoute').value);
                    const fareType = document.getElementById('fareType').value;
                    const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);

                    if (!fromRoute || !toRoute) {
                        Swal.fire('Error', 'Please select both starting point and destination.', 'error');
                        return false;
                    }

                    // Call your processing function
                    getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod);
                }
            });
        }

        async function processPayment(paymentType) {
            const isValid = await validateRoutesAndBus();
            if (!isValid) return;

            if (paymentType === 'cash') {
                // First prompt: Enter Cash Received
                Swal.fire({
                    title: 'Enter Cash Received',
                    input: 'number',
                    inputPlaceholder: 'e.g. 100',
                    inputAttributes: {
                        min: 0,
                        step: 1
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Next',
                    cancelButtonText: 'Cancel',
                    preConfirm: (value) => {
                        if (!value || isNaN(value) || parseFloat(value) <= 0) {
                            Swal.showValidationMessage('Please enter a valid amount');
                        }
                        return value;
                    }
                }).then((inputResult) => {
                    if (inputResult.isConfirmed) {
                        const cashReceived = parseFloat(inputResult.value);

                        // Second prompt: Confirm Payment
                        Swal.fire({
                            title: 'Confirm Cash Payment',
                            html: `You entered <strong>₱${cashReceived.toFixed(2)}</strong><br>Proceed with cash payment?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Proceed',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const rfid = '';
                                const fromRoute = JSON.parse(document.getElementById('fromRoute').innerText);
                                const toRoute = JSON.parse(document.getElementById('toRoute').value);
                                const fareType = document.getElementById('fareType').value;
                                const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);
                                const paymentMethod = 'Cash';
                                const transactionNumber = generateTransactionNumber();
                                const distance = Math.abs(fromRoute.post - toRoute.post);

                                getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod, cashReceived);
                            }
                        });
                    }
                });
            }
        }

        // Updated getUserBalance function to handle both RFID and cash payments
        async function getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, isCashPayment = false, transactionNumber, distance, paymentMethod, cashReceived) {
    const conductorName = "<?= $conductorName; ?>";
    const driverName = "<?= $_SESSION['driver_name'] ?>";
    try {
        const baseFare = <?php echo $base_fare; ?>;
        distance = Math.abs(fromRoute.post - toRoute.post); // compute distance if not passed
        let totalFare = baseFare * passengerQuantity;
        let totalChange = 0;

        if (isCashPayment) {
            totalChange = cashReceived - totalFare;
        }

        totalFare = totalFare.toFixed(2);

        const postData = {
            rfid: rfid,
            fromRoute: fromRoute,
            toRoute: toRoute,
            fareType: fareType,
            passengerQuantity: passengerQuantity,
            transactionNumber: transactionNumber,
            distance: distance,
            driverName: driverName
        };

        showReceipt(fromRoute, toRoute, fareType, totalFare, conductorName, transactionNumber, distance, paymentMethod, passengerQuantity, totalChange, postData);
    } catch (error) {
        console.error('Error preparing fare:', error);
        Swal.fire('Error', 'An error occurred while processing your payment. Please try again.', 'error');
    }
}

function abbreviateName(fullName) {
    const parts = fullName.trim().split(/\s+/);
    const lastName = parts.pop();
    const initials = parts.map(name => name[0].toUpperCase()).join(' ');
    return initials + ' ' + lastName;
}

function showReceipt(fromRoute, toRoute, fareType, totalFare, conductorName, transactionNumber, distance, paymentMethod, passengerQuantity, totalChange, postData) {
    if (receiptShown) return;

    receiptShown = true;
    const driverName = abbreviateName("<?= $_SESSION['driver_name'] ?>");
    const conductorNameFormatted = abbreviateName(conductorName);
    const busNumber = "<?= $bus_number; ?>";
    const direction = "<?= $_SESSION['direction'] ?? '' ?>";
    const date = new Date().toLocaleDateString();
    const time = new Date().toLocaleTimeString();

    let receiptText = `
     ZARAGOZA RAMSTAR
   TRANSPORT COOPERATIVE
DIRECTION       : ${direction}
BUS NO.         : ${busNumber}
DATE            : ${date}
TIME            : ${time}
FROM            : ${fromRoute.route_name}
TO              : ${toRoute.route_name}
DISTANCE        : ${distance} km
DRIV. NAME      : ${driverName}
COND. NAME      : ${conductorNameFormatted}
PASSENGER TYPE  : ${fareType}
PAYMENT METHOD  : ${paymentMethod}
PASSENGER(S)    : ${passengerQuantity}
TOTAL FARE      : ₱${totalFare}
`;

    if (paymentMethod === "Cash") {
        receiptText += `CHANGE          : ₱${totalChange}\n`;
    }

    receiptText += `
        ${transactionNumber}
 Thank you for riding with us!
`;

    Swal.fire({
        html: `<pre style="font-family: monospace; text-align: left;">${receiptText}</pre>`,
        showCancelButton: true,
        confirmButtonText: 'Print Receipt',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    body: JSON.stringify(postData),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.status === 'error') {
                    Swal.fire('Error', data.message, 'error');
                    return;
                }

                if (window.AndroidPrinter) {
                    AndroidPrinter.printText(
                        transactionNumber,
                        direction,
                        busNumber,
                        driverName,
                        conductorNameFormatted,
                        totalFare,
                        date,
                        time,
                        fromRoute.route_name,
                        toRoute.route_name,
                        distance,
                        fareType,
                        paymentMethod,
                        totalChange,
                        passengerQuantity
                    );
                } else {
                    console.error("AndroidPrinter interface not available");
                }

                setTimeout(() => location.reload(), 2000);

            } catch (error) {
                console.error("Failed to post transaction:", error);
                Swal.fire('Error', 'An error occurred while saving the transaction.', 'error');
            }
        } else {
            console.log("User canceled the receipt. Transaction not posted.");
            receiptShown = false;
        }
    });
}
    </script>
</body>

</html>
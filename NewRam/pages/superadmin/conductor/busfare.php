<?php
session_start();
include '../../../includes/connection.php';

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Conductor' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../.././index.php");
    exit();
}

$firstname = $_SESSION['firstname'];
$lastname = $_SESSION['lastname'];
$bus_number = isset($_SESSION['bus_number']) ? $_SESSION['bus_number'] : 'Unknown Bus Number';
$conductorac = isset($_SESSION['driver_account_number']) ? $_SESSION['driver_account_number'] : 'unknown conductor account number';
$driverName = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : 'unknown driver name';
$conductorName = isset($_SESSION['conductor_name']) ? $_SESSION['conductor_name'] : 'unknown conductor name';
$driverac = isset($_SESSION['driver_name']) ? $_SESSION['driver_name'] : null;  // Check if driver name is in session


$conductorName = $firstname . ' ' . $lastname;

// Fetch routes
$routes = [];
$query = "SELECT * FROM fare_routes";
$result = $conn->query($query);
$balance = 0;

while ($row = $result->fetch_assoc()) {
    $routes[] = $row;
}

// Fetch base fare and additional fare from fare_settings table
$fareSettingsQuery = "SELECT * FROM fare_settings LIMIT 1"; // Assuming there's only one record
$fareSettingsResult = $conn->query($fareSettingsQuery);
$fareSettings = $fareSettingsResult->fetch_assoc();

// Store passengers in a session to track those currently on the bus
if (!isset($_SESSION['passengers'])) {
    $_SESSION['passengers'] = [];
}

// Function to fetch balance based on RFID
// Function to log passenger entry
function logPassengerEntry($rfid, $fromRoute, $toRoute, $fare, $conductorName, $busNumber, $transactionNumber, $conn)
{
    $query = "INSERT INTO passenger_logs (rfid, from_route, to_route, fare, conductor_name, bus_number, transaction_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssss", $rfid, $fromRoute, $toRoute, $fare, $conductorName, $busNumber, $transactionNumber);
    $stmt->execute();
    $stmt->close();
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
            $totalFare *= 0.8; // 20% discount
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
            logPassengerEntry($loggedRfid, $fromRoute['route_name'], $toRoute['route_name'], $totalFare, $conductorName, $bus_number, $transactionNumber, $conn);

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


// Check if bus number and driver name are not set in the session
if (!$bus_number || !$driverac) {

    $bus_query = "SELECT bus_number FROM businfo WHERE status = 'available'";
    $bus_result = mysqli_query($conn, $bus_query);

    // Prepare options for SweetAlert
    $bus_options = "";
    while ($bus = mysqli_fetch_assoc($bus_result)) {
        $bus_options .= "<option value=\"" . $bus['bus_number'] . "\">" . $bus['bus_number'] . "</option>";
    }


    // Show the SweetAlert modal
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
    window.onload = function() {
        // Step 1: Select Bus
        Swal.fire({
            icon: 'question',
            title: 'Select Bus',
            html: '<form id=\"busForm\" method=\"POST\" action=\"../../../actions/select_bus.php\">' +
                  '<select name=\"bus_number\" id=\"bus_number\" required style=\"' + 
                  'width: 100%;' +
                  'padding: 10px;' +
                  'border: 2px solid #ddd;' +
                  'border-radius: 5px;' +
                  'font-size: 16px;' +
                  'box-sizing: border-box;' +
                  'background-color: #f9f9f9;' +
                  '\" class=\"swal2-input\">' + 
                  '" . $bus_options . "' +
                  '</select><br><br>' +
                  '</form>',
            showCancelButton: false,
            confirmButtonText: 'Next',
            preConfirm: function() {
                return new Promise((resolve) => {
                    const selectedBus = document.getElementById('bus_number').value;
                    if (selectedBus) {
                        resolve(selectedBus);
                    } else {
                        Swal.showValidationMessage('Please select a bus');
                    }
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const busNumber = result.value;

                // Step 2: Enter Driver Name
                Swal.fire({
                    icon: 'question',
                    title: 'Enter Driver Name',
                    input: 'text',
                    inputPlaceholder: 'Driver Name',
                    showCancelButton: false,
                    confirmButtonText: 'OK',
                    preConfirm: (driverName) => {
                        if (!driverName) {
                            Swal.showValidationMessage('Driver name is required');
                        }
                        return { busNumber, driverName };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const { busNumber, driverName } = result.value;

                        // Submit form with bus number and driver name
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '../../../actions/select_bus.php';

                        const busInput = document.createElement('input');
                        busInput.type = 'hidden';
                        busInput.name = 'bus_number';
                        busInput.value = busNumber;

                        const driverInput = document.createElement('input');
                        driverInput.type = 'hidden';
                        driverInput.name = 'driver_name';
                        driverInput.value = driverName;

                        form.appendChild(busInput);
                        form.appendChild(driverInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    };
    </script>";
}
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
    <link rel="stylesheet" href="../../../assets/css/sidebars.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Use full version -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
<?php
        include '../../../includes/topbar.php';
        include '../../../includes/superadmin_sidebar.php';
        include '../../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <h2>Bus Fare Calculator</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <form id="fareForm" class="mt-4">
                    <!-- KM Display -->
                    <div class="d-flex justify-content-center align-items-center mb-4" style="min-height: 120px;">
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fromRoute" class="form-label">From</label>
                            <select id="fromRoute" name="fromRoute" class="form-select">
                                <option value="" disabled selected>Select Starting Point</option>
                                <?php foreach ($routes as $route): ?>
                                    <option value="<?= htmlspecialchars(json_encode($route), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($route['route_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="toRoute" class="form-label">To</label>
                            <select id="toRoute" name="toRoute" class="form-select">
                                <option value="" disabled selected>Select Destination</option>
                                <?php foreach ($routes as $route): ?>
                                    <option value="<?= htmlspecialchars(json_encode($route), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars($route['route_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Fare Type and Passenger Quantity -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fareType" class="form-label">Fare Type</label>
                            <select id="fareType" name="fareType" class="form-select">
                                <option value="regular">Regular</option>
                                <option value="discounted">Student/Senior (20% Off)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="passengerQuantity" class="form-label">Number of Passengers</label>
                            <input type="number" id="passengerQuantity" name="passengerQuantity" class="form-control" value="1"
                                min="1" max="10">
                        </div>
                    </div>

                    <!-- RFID Scan Input 
                    <div class="row mb-3">
                        <div class="col-md-6 offset-md-3">
                            <label for="rfidInput" class="form-label">Scan RFID</label>
                            <input type="text" id="rfidInput" name="rfidInput" class="form-control"
                                placeholder="Scan your RFID here">
                        </div>
                    </div>
                    Calculate Button 
                    <div class="row">
                        <div class="col-md-6 offset-md-3">
                            <button type="button" id="calculateFare" class="btn btn-primary btn-lg shadow-sm">Calculate
                                Fare</button>
                        </div>
                    </div> -->
                </form>

                <div class="d-flex justify-content-center align-items-center mb-4" style="min-height: 120px;">
                    <!-- Card for Distance -->
                    <div class="card shadow-sm text-center p-3 mx-2">
                        <h5 class="form-label mb-2" style="color: #007BFF;">Payment</h5>
                        <button class="btn btn-primary mt-3" onclick="processPayment('cash')">Cash</button>
                    </div>

                    <!-- RFID Payment Button -->
                    <div class="card shadow-sm text-center p-3 mx-2">
                        <h5 class="form-label mb-2" style="color: #007BFF;">Payment</h5>
                        <button class="btn btn-success mt-3" onclick="promptRFIDInput()">RFID</button>
                    </div>
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

        const baseFare = <?php echo $base_fare; ?>;
        const additionalFare = <?php echo $additional_fare; ?>;
        const driverName = "<?= $_SESSION['driver_name']; ?>";  // PHP variable for driver name

        document.getElementById('fromRoute').addEventListener('change', updateDistance);
        document.getElementById('toRoute').addEventListener('change', updateDistance);
        document.getElementById('fareType').addEventListener('change', updateDistance);
        document.getElementById('passengerQuantity').addEventListener('change', updateDistance);

        function updateDistance() {
            const fromRouteValue = document.getElementById('fromRoute').value;
            const toRouteValue = document.getElementById('toRoute').value;
            const kmLabel = document.getElementById('kmLabel');
            const fareLabel = document.getElementById('fareLabel');
            const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10); // Get passenger quantity

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

        function validateRoutes() {
            const fromRoute = document.getElementById('fromRoute').value;
            const toRoute = document.getElementById('toRoute').value;

            if (!fromRoute || !toRoute) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Selection',
                    text: 'Please select both a starting point and a destination.',
                });
                return false;
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

        function promptRFIDInput() {
            const fromRouteValue = document.getElementById('fromRoute').value;
            const toRouteValue = document.getElementById('toRoute').value;
            const distance = Math.abs(fromRoute.post - toRoute.post);
            const transactionNumber = generateTransactionNumber();
            const paymentMethod = 'RFID';

            console.log("Generated Transaction Number:", transactionNumber); // Debugging line
            console.log("Distance:", distance); // Debugging line
            console.log("Payment Method:", paymentMethod);

            if (!validateRoutes()) {
                // Stop execution if routes are not selected
                return;
            }

            Swal.fire({
                title: 'Enter RFID',
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Cancel',
                inputPlaceholder: 'Scan your RFID here',
                didOpen: () => {
                    const inputField = Swal.getInput();
                    inputField.addEventListener('keydown', async (event) => {
                        // Check if the Enter key is pressed
                        if (event.key === 'Enter') {
                            const rfid = inputField.value.trim();
                            if (rfid) {
                                // If RFID is entered, automatically process the fare
                                const fromRoute = JSON.parse(document.getElementById('fromRoute').value);
                                const toRoute = JSON.parse(document.getElementById('toRoute').value);
                                const fareType = document.getElementById('fareType').value;
                                const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);

                                if (!fromRoute || !toRoute) {
                                    Swal.fire('Error', 'Please select both starting point and destination.', 'error');
                                    return;
                                }

                                console.log("Transaction Number before calling getUser Balance:", transactionNumber); // Debugging line

                                // Call the function to get user balance and process the fare
                                getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod);
                            }
                        }
                    });
                }
            });
        }



        // Function to get user balance based on RFID (account_number)
        function processPayment(paymentType) {
            if (!validateRoutes()) {
                return;
            }
            if (paymentType === 'cash') {
                Swal.fire({
                    title: 'Confirm Cash Payment',
                    text: 'Are you sure you want to pay in cash?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Proceed',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const rfid = '';
                        const fromRoute = JSON.parse(document.getElementById('fromRoute').value);
                        const toRoute = JSON.parse(document.getElementById('toRoute').value);
                        const fareType = document.getElementById('fareType').value;
                        const passengerQuantity = parseInt(document.getElementById('passengerQuantity').value, 10);
                        const paymentMethod = 'Cash';

                        // Generate transaction number
                        const transactionNumber = generateTransactionNumber();
                        const distance = Math.abs(fromRoute.post - toRoute.post);
                        getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, true, transactionNumber, distance, paymentMethod);
                    }
                });
            }
        }

        // Updated getUserBalance function to handle both RFID and cash payments
        async function getUserBalance(rfid, fromRoute, toRoute, fareType, passengerQuantity, isCashPayment = false, transactionNumber, distance, paymentMethod) {
            const conductorName = "<?= $conductorName; ?>";  // PHP variable
            try {
                const baseFare = <?php echo $base_fare; ?>;
                const distance = Math.abs(fromRoute.post - toRoute.post);
                let totalFare = 0;

                if (isCashPayment) {
                    // Cash payment logic
                    const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>', {
                        method: 'POST',
                        body: JSON.stringify({
                            rfid: rfid,
                            fromRoute: fromRoute,
                            toRoute: toRoute,
                            fareType: fareType,
                            passengerQuantity: passengerQuantity,
                            transactionNumber: transactionNumber,
                            distance: distance,
                            driverName: driverName
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();
                    if (data.status === 'error') {
                        Swal.fire('Error', data.message, 'error');
                        return;
                    }

                    totalFare = data.fare; // Use the fare returned from the server
                } else {
                    // RFID payment logic
                    const response = await fetch('<?= $_SERVER['PHP_SELF']; ?>', {
                        method: 'POST',
                        body: JSON.stringify({
                            rfid: rfid,
                            fromRoute: fromRoute,
                            toRoute: toRoute,
                            fareType: fareType,
                            passengerQuantity: passengerQuantity,
                            transactionNumber: transactionNumber,
                            distance: distance,
                            driverName: driverName
                        }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json(); // Await the JSON response here

                    if (data.status === 'error') {
                        Swal.fire('Error', data.message, 'error');
                        return;
                    }

                    totalFare = data.fare; // Use the fare returned from the server
                }

                totalFare = totalFare.toFixed(2); // Ensure it's formatted correctly
                showReceipt(fromRoute, toRoute, fareType, totalFare, conductorName, transactionNumber, distance, paymentMethod, passengerQuantity);
            } catch (error) {
                console.error('Error fetching balance and processing fare:', error);
                Swal.fire('Error', 'An error occurred while processing your payment. Please try again.', 'error');
            }
        }

        let receiptShown = false;

        function showReceipt(fromRoute, toRoute, fareType, totalFare, conductorName, transactionNumber, distance, paymentMethod, passengerQuantity) {
            if (receiptShown) return; // Prevent showing the receipt again

            receiptShown = true;
            const driverName = "<?= $_SESSION['driver_name']; ?>";  // PHP variable for driver name
            const busNumber = "<?= $bus_number; ?>";  // PHP variable for bus number

            Swal.fire({
                html: `
        <h3>Receipt</h3>
        <strong>Transaction Number:</strong> ${transactionNumber}<br>
        <strong>Bus No.:</strong> ${busNumber}<br>
        <strong>Date:</strong> ${new Date().toLocaleDateString()}<br>
        <strong>Time:</strong> ${new Date().toLocaleTimeString()}<br>
        <strong>From:</strong> ${fromRoute.route_name}<br>
        <strong>To:</strong> ${toRoute.route_name}<br>
        <strong>Distance:</strong> ${distance} km<br>
        <strong>Driver:</strong> ${driverName}<br> <!-- Added Driver Name -->
        <strong>CONDUCTOR:</strong> ${conductorName}<br>
        <strong>Passenger Type:</strong> ${fareType}<br>
        <strong>Payment Method:</strong> ${paymentMethod}<br>
         <strong>Passenger/s:</strong> ${passengerQuantity}<br> <!-- Added Passenger Quantity -->
        <div style="font-size: 22px; font-weight: bold;">₱${totalFare}</div><br>
        <p>Thank you for riding with us!</p>
        `,
                didClose: () => {
                    // Trigger the PHP print function here using an AJAX request

                    $.post('../../../actions/print_receipt.php', {
                        fromRoute: fromRoute,
                        toRoute: toRoute,
                        fareType: fareType,
                        totalFare: totalFare,
                        conductorName: conductorName,
                        driverName: driverName, // Pass driver name to print if needed
                        busNumber: busNumber,
                        transactionNumber: transactionNumber,
                        distance: distance,
                        paymentMethod: paymentMethod,
                        passengerQuantity: passengerQuantity
                    }, function (response) {
                        console.log("Receipt printed successfully!");
                        location.reload();
                    }).fail(function () {
                        console.error("Failed to print receipt.");
                    });
                }
            }).then(() => {
                //location.reload(); // Reload the page after the receipt is acknowledged
            });
        }

    </script>
</body>

</html>
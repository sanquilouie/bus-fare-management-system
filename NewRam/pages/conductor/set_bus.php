<?php
session_start();
include '../../includes/connection.php';

// Fetch driver names from useracc where role is 'Driver'
$drivers = [];
$driverQuery = "SELECT account_number, firstname, lastname FROM useracc WHERE role = 'Driver'AND driverStatus = 'notdriving'";
$driverResult = $conn->query($driverQuery);

while ($row = $driverResult->fetch_assoc()) {
    $drivers[] = $row;
}

    $bus_query = "SELECT bus_number FROM businfo WHERE status = 'available' AND statusofbus = 'active'";
    $bus_result = mysqli_query($conn, $bus_query);

    // Prepare options for SweetAlert
    $bus_options = "";
    while ($bus = mysqli_fetch_assoc($bus_result)) {
        $bus_options .= "<option value=\"" . $bus['bus_number'] . "\">" . $bus['bus_number'] . "</option>";
    }

    // Show the SweetAlert modal
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src='/NewRam/assets/js/NFCScanner.js'></script>
    <script>
    window.onload = function() {
        // Step 1: Select Bus
        Swal.fire({
            icon: 'question',
            title: 'Select Bus',
            html: '<form id=\"busForm\" method=\"POST\" action=\"../../actions/select_bus.php\">' +
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
    
                // Step 2: Select Driver Name
                Swal.fire({
            icon: 'question',
            title: 'Enter or Scan Driver Account Number',
            html: '<input id=\"driver_name\" required placeholder=\"Enter account number\" style=\"' +
                'width: 85%;' +
                'padding: 10px;' +
                'border: 2px solid #ddd;' +
                'border-radius: 5px;' +
                'font-size: 16px;' +
                'box-sizing: border-box;' +
                'background-color: #f9f9f9;\" class=\"swal2-input\">' +
                '<input type=\"hidden\" id=\"driver_fullname\" value=\"\">',
            showCancelButton: false,
            confirmButtonText: 'OK',
            preConfirm: async function () {
                const accountNumber = document.getElementById('driver_name').value;

                if (!accountNumber) {
                    Swal.showValidationMessage('Please enter an account number');
                    return false;
                }

                const drivers = " . json_encode($drivers) . ";
                const match = drivers.find(driver => driver.account_number === accountNumber);

                if (!match) {
                    Swal.showValidationMessage('No driver found with that account number');
                    return false;
                }

                // 🔍 Check if already assigned in the businfo table
                const res = await fetch('../../actions/check_driver_assigned.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ account_number: accountNumber })
                });
                const check = await res.json();

                if (check.assigned) {
                    Swal.showValidationMessage('This driver is already assigned to a bus.');
                    return false;
                }

                // ✅ Save session if not assigned
                await fetch('../../actions/store_driver_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ account_number: accountNumber })
                });

                document.getElementById('driver_fullname').value = match.firstname + ' ' + match.lastname;

                return match.firstname + ' ' + match.lastname;
            }
        }).then((result) => {
                    if (result.isConfirmed) {
                        const selectedDriver = result.value;
    
                        // Submit form with bus number and driver name
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '../../actions/select_bus.php';
    
                        const busInput = document.createElement('input');
                        busInput.type = 'hidden';
                        busInput.name = 'bus_number';
                        busInput.value = busNumber;
    
                        const driverInput = document.createElement('input');
                        driverInput.type = 'hidden';
                        driverInput.name = 'driver_name';
                        driverInput.value = selectedDriver;
    
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
?>
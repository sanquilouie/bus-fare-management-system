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
                title: 'Select Driver',
                html: '<select id=\"driver_name\" required style=\"' + 
                      'width: 100%;' +
                      'padding: 10px;' +
                      'border: 2px solid #ddd;' +
                      'border-radius: 5px;' +
                      'font-size: 16px;' +
                      'box-sizing: border-box;' +
                      'background-color: #f9f9f9;' +
                      '\" class=\"swal2-input\">' +
                      '" . implode('', array_map(function($driver) {
                          return "<option value=\"{$driver['firstname']} {$driver['lastname']}\">{$driver['firstname']} {$driver['lastname']}</option>";
                      }, $drivers)) . "' +
                      '</select><br><br>',
                showCancelButton: false,
                confirmButtonText: 'Next',
                preConfirm: function() {
                    return new Promise((resolve) => {
                        const selectedDriver = document.getElementById('driver_name').value;
                        if (selectedDriver) {
                            resolve(selectedDriver);
                        } else {
                            Swal.showValidationMessage('Please select a driver');
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const selectedDriver = result.value;

                    // Step 3: Select Direction
                    Swal.fire({
                        icon: 'question',
                        title: 'Select Direction',
                        html: '<select id=\"direction\" required style=\"' + 
                              'width: 100%;' +
                              'padding: 10px;' +
                              'border: 2px solid #ddd;' +
                              'border-radius: 5px;' +
                              'font-size: 16px;' +
                              'box-sizing: border-box;' +
                              'background-color: #f9f9f9;' +
                              '\" class=\"swal2-input\">' +
                              '<option value=\"South to North\">South to North</option>' +
                              '<option value=\"North to South\">North to South</option>' +
                              '</select><br><br>',
                        showCancelButton: false,
                        confirmButtonText: 'OK',
                        preConfirm: function() {
                            return new Promise((resolve) => {
                                const selectedDirection = document.getElementById('direction').value;
                                if (selectedDirection) {
                                    resolve(selectedDirection);
                                } else {
                                    Swal.showValidationMessage('Please select a direction');
                                }
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const selectedDirection = result.value;

                            // Submit form with bus number, driver name, and direction
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

                            const directionInput = document.createElement('input');
                            directionInput.type = 'hidden';
                            directionInput.name = 'direction';
                            directionInput.value = selectedDirection;

                            form.appendChild(busInput);
                            form.appendChild(driverInput);
                            form.appendChild(directionInput);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            });
        }
    });
};
</script>";
?>
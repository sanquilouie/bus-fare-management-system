<?php
session_start();
include '../../includes/connection.php';
//print_r($_SESSION);

if (!isset($_SESSION['email']) || ($_SESSION['role'] != 'Cashier' && $_SESSION['role'] != 'Superadmin')) {
    header("Location: ../../index.php");
    exit();
}

if (!isset($_SESSION['account_number'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$conductor_id = $_SESSION['account_number']; // Conductor's ID from session
$conductor_name = ''; // Initialize the conductor's name variable
$bus_number = '';
$total_load = 0; // Initialize the total load variable
$rfid_scan = ''; // Initialize the RFID variable

// Handle RFID scan and fetch data based on RFID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rfid_scan'])) {
    $rfid_scan = $_POST['rfid_scan'];

    // 1. Get conductor name and total load from transactions
    $stmt = $conn->prepare("SELECT u.firstname, u.lastname, SUM(t.amount) AS total_load
                                FROM useracc u
                                LEFT JOIN transactions t ON t.conductor_id = u.account_number
                                WHERE u.account_number = ? AND t.status NOT IN ('edited', 'remitted')
                                GROUP BY u.account_number;
                                ");
    $stmt->bind_param("s", $rfid_scan);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $total_load);

    if ($stmt->fetch()) {
        $conductor_name = $firstname . ' ' . $lastname;
    } else {
        $conductor_name = "Unknown Conductor";
        $total_load = 0;
    }
    $stmt->close();

    $stmt_bus = $conn->prepare("SELECT bus_number 
                                FROM passenger_logs 
                                WHERE conductor_id = ? 
                                AND status = 'notremitted'");
    $stmt_bus->bind_param("s", $rfid_scan);
    $stmt_bus->execute();
    $stmt_bus->bind_result($bus_number);
    $stmt_bus->fetch();
    $stmt_bus->close();

    if (!$bus_number) {
        $bus_number = "No Bus Assigned";
    }
    $stmt2 = $conn->prepare("SELECT SUM(fare) AS total_fare 
                             FROM passenger_logs 
                             WHERE conductor_id = ? 
                             AND bus_number = ? 
                             AND rfid = 'cash' 
                             AND DATE(timestamp) = CURDATE()");
    $stmt2->bind_param("ss", $rfid_scan, $bus_number);
    $stmt2->execute();
    $stmt2->bind_result($total_fare);
    $stmt2->fetch();
    $stmt2->close();

    if (!$total_fare) {
        $total_fare = 0;
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <title>Conductor Remittance</title>
</head>

<body>
    <?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
    ?>
    <div id="main-content" class="container-fluid mt-5">
        <h2>Conductor Remittance</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
                <form id="remittanceForm" method="POST" action="" onsubmit="return showPreview(event)">
                    <label for="rfid_scan" class="form-label">NFC Scan:</label>
                    <input type="text" class="form-control" id="rfid_scan" name="rfid_scan" placeholder="Scan RFID..." required onkeydown="handleRFIDKey(event)"
                        value="<?= htmlspecialchars($rfid_scan) ?>" class="form-label">

                    <label for="bus_no" class="form-label">Bus No:</label>
                    <input type="text" class="form-control" id="bus_no" name="bus_no" required value="<?= htmlspecialchars($bus_number) ?>" readonly>

                    <label for="conductor_name" class="form-label">Conductor Name:</label>
                    <input type="text" class="form-control" id="conductor_name" name="conductor_name" required
                        value="<?= htmlspecialchars($conductor_name) ?>" readonly>

                    <label for="total_fare" class="form-label">Total Fare (₱):</label>
                    <input type="number" class="form-control" id="total_fare" name="total_fare" step="0.01" readonly
                        value="<?= htmlspecialchars($total_fare) ?>">

                    <label for="total_load" class="form-label">Total Load (₱):</label>
                    <input type="number" class="form-control" id="total_load" name="total_load" step="0.01" readonly
                        value="<?= htmlspecialchars($total_load) ?>">

                    <div id="deductions-container">
                        <div class="text-center mt-1">
                            <button type="button" id="toggleDeductions" class="btn btn-primary w-100">+ Deductions</button>
                        </div>
                        <div id="deductions" style="display: none; margin-top: 10px;">
                            <h3>Deductions</h3>

                            <div class="text-center mt-1">
                                <button type="button" id="addDeduction" class="btn btn-secondary">Add Deduction</button>
                            </div>
                        </div>
                    </div>

                    <label for="net_amount" class="form-label">Net Amount (₱):</label>
                    <input type="number" class="form-control" id="net_amount" name="net_amount" step="0.01" readonly
                        value="<?= htmlspecialchars($total_load + $total_fare) ?>">
                        <div class="text-center mt-1">
                            <button type="submit" name="generate_remittance" id="remitButton" class="btn btn-primary w-100">Generate Remittance</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function showPreview(event) {
            event.preventDefault(); // Prevent actual form submission

            // Get all the form values
            const rfid = document.getElementById('rfid_scan').value;
            const busNo = document.getElementById('bus_no').value;
            const conductorName = document.getElementById('conductor_name').value;
            const totalFare = document.getElementById('total_fare').value;
            const totalLoad = document.getElementById('total_load').value; 
            const netAmount = document.getElementById('net_amount').value;

            // Gather deductions (if any)
            const deductions = [];
            const descs = document.querySelectorAll('input[name="deduction_desc[]"]');
            const amounts = document.querySelectorAll('input[name="deduction_amount[]"]');

            for (let i = 0; i < descs.length; i++) {
                const desc = descs[i].value.trim();
                const amount = amounts[i].value.trim();
                if (desc || amount) {
                    deductions.push(`${desc || 'No Description'}: ₱${amount || '0.00'}`);
                }
            }

            // Create the preview message
            let html = `
                <strong>RFID:</strong> ${rfid}<br>
                <strong>Bus No:</strong> ${busNo}<br>
                <strong>Conductor:</strong> ${conductorName}<br>
                <strong>Total Fare:</strong> ₱${totalFare}<br>
                <strong>Total Load:</strong> ₱${totalLoad}<br>
            `;

            if (deductions.length) {
                html += `<strong>Deductions:</strong><br><ul>`;
                deductions.forEach(d => {
                    html += `<li>${d}</li>`;
                });
                html += `</ul>`;
            }

            html += `<strong>Net Amount:</strong> ₱${netAmount}`;

            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Remittance?',
                html: html,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes, Submit',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (busNo.trim() === "No Bus Assigned" || conductorName.trim() === "Unknown Conductor") {
                        Swal.fire({
                            title: 'Notice',
                            text: 'Nothing to remit for this conductor. Please ensure a valid bus and conductor are assigned.',
                            icon: 'info',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                        return;
                    }

                    // Send data to the backend for printing
                    fetch('../../actions/print_remit.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            rfid: rfid,
                            bus_no: busNo,
                            conductor_name: conductorName,
                            total_fare: totalFare,
                            total_load: totalLoad,
                            net_amount: netAmount,
                            deductions: deductions
                        })
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log(data);
                        // Optionally submit form afterward
                        document.getElementById('remittanceForm').submit();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Could not print the receipt.', 'error');
                    });
                }
            });
            return false;
        }

        function handleRFIDKey(event) {
            if (event.key === "Enter") {
                event.preventDefault();
                document.getElementById("remittanceForm").onsubmit = null; // Bypass the Swal preview temporarily
                document.getElementById("remittanceForm").submit();     
            }
        }

        document.getElementById('toggleDeductions').addEventListener('click', function () {
            const deductions = document.getElementById('deductions');
            if (deductions.style.display === 'none') {
                deductions.style.display = 'block';
                this.textContent = '- Deductions';
            } else {
                deductions.style.display = 'none';
                this.textContent = '+ Deductions';
            }
        });
        
        document.getElementById('addDeduction').addEventListener('click', function () {
            const deductionRow = document.createElement('div');
            deductionRow.classList.add('deduction-row');
            deductionRow.innerHTML = `
                <div class="row g-2">
                    <div class="col-7">
                        <input type="text" class="form-control" name="deduction_desc[]" placeholder="Description">
                    </div>
                    <div class="col-5">
                        <input type="number" class="form-control deduction-amount" name="deduction_amount[]" step="0.01" placeholder="Amount (₱)">
                    </div>
                </div>
            `;
            document.getElementById('deductions').appendChild(deductionRow);
        });
        document.getElementById('remittanceForm').addEventListener('input', function (event) {
            if (event.target.classList.contains('deduction-amount')) {
                let totalLoad = parseFloat(document.getElementById('total_load').value) || 0;
                let totalFare = parseFloat(document.getElementById('total_fare').value) || 0;
                let totalDeductions = 0;

                document.querySelectorAll('.deduction-amount').forEach(function (deductionInput) {
                    let value = parseFloat(deductionInput.value);
                    if (!isNaN(value)) {
                        totalDeductions += value;
                    }
                });

                document.getElementById('net_amount').value = (totalFare + totalLoad - totalDeductions).toFixed(2);
            }
        });
    </script>
</body>

</html>
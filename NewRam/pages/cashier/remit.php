<?php
session_start();
include '../../includes/connection.php';

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

    // Fetch the conductor details and bus number based on RFID scan
    $stmt = $conn->prepare("SELECT u.firstname, u.lastname, t.bus_number, SUM(t.amount) AS total_load
                            FROM useracc u
                            LEFT JOIN transactions t ON t.conductor_id = u.account_number
                            WHERE u.account_number = ? GROUP BY u.account_number, t.bus_number");
    $stmt->bind_param("s", $rfid_scan);
    $stmt->execute();
    $stmt->bind_result($firstname, $lastname, $bus_number, $total_load);
    if ($stmt->fetch()) {
        $conductor_name = $firstname . ' ' . $lastname; // Combine first and last name
    } else {
        // If RFID not found, reset values
        $conductor_name = "Unknown Conductor";
        $bus_number = "No Bus Assigned";
        $total_load = 0;
    }
    $stmt->close();
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
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-6 col-xxl-8">
                <form id="remittanceForm" method="POST" action="">
                    <label for="rfid_scan" class="form-label">RFID Scan:</label>
                    <input type="text" class="form-control" id="rfid_scan" name="rfid_scan" placeholder="Scan RFID..." required
                        value="<?= htmlspecialchars($rfid_scan) ?>" class="form-label" oninput="fetchDetails()">>

                    <label for="bus_no" class="form-label">Bus No:</label>
                    <input type="text" class="form-control" id="bus_no" name="bus_no" required value="<?= htmlspecialchars($bus_number) ?>" readonly>

                    <label for="conductor_name" class="form-label">Conductor Name:</label>
                    <input type="text" class="form-control" id="conductor_name" name="conductor_name" required
                        value="<?= htmlspecialchars($conductor_name) ?>" readonly>

                    <label for="total_load" class="form-label">Total Load (₱):</label>
                    <input type="number" class="form-control" id="total_load" name="total_load" step="0.01" readonly
                        value="<?= htmlspecialchars($total_load) ?>">

                    <div id="deductions-container">
                        <div class="text-center mt-1">
                            <button type="button" id="toggleDeductions" class="btn btn-primary w-100">+ Deductions</button>
                        </div>
                        <div id="deductions" style="display: none; margin-top: 10px;">
                            <h3>Deductions</h3>
                            <div class="deduction-row">
                                <input type="text" class="form-control" name="deduction_desc[]" placeholder="Description">
                                <input type="number" class="form-control" name="deduction_amount[]" step="0.01" placeholder="Amount (₱)">
                            </div>
                            <div class="text-center mt-1">
                                <button type="button" id="addDeduction" class="btn btn-secondary">Add Deduction</button>
                            </div>
                        </div>
                    </div>

                    <label for="net_amount" class="form-label">Net Amount (₱):</label>
                    <input type="number" class="form-control" id="net_amount" name="net_amount" step="0.01" readonly
                        value="<?= htmlspecialchars($total_load) ?>">
                        <div class="text-center mt-1">
                            <button type="submit" name="generate_remittance" id="remitButton" class="btn btn-primary w-100">Generate Remittance</button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    <script>
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
                <input type="text" name="deduction_desc[]" placeholder="Description">
                <input type="number" name="deduction_amount[]" step="0.01" placeholder="Amount (₱)" class="deduction-amount">
            `;
            document.getElementById('deductions').appendChild(deductionRow);
        });

        document.getElementById('remittanceForm').addEventListener('input', function (event) {
            if (event.target.classList.contains('deduction-amount')) {
                let totalLoad = parseFloat(document.getElementById('total_load').value) || 0;
                let totalDeductions = 0;

                document.querySelectorAll('.deduction-amount').forEach(function (deductionInput) {
                    let value = parseFloat(deductionInput.value);
                    if (!isNaN(value)) {
                        totalDeductions += value;
                    }
                });

                document.getElementById('net_amount').value = (totalLoad - totalDeductions).toFixed(2);
            }
        });
    </script>
</body>

</html>
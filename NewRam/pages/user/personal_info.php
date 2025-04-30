<?php
$provinceData = json_decode(file_get_contents('../../libraries/PSGC/provinces.json'), true);
$municipalityData = json_decode(file_get_contents('../../libraries/PSGC/municipalities.json'), true);
$barangayData = json_decode(file_get_contents('../../libraries/PSGC/barangays.json'), true);

session_start();
include '../../includes/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['account_number']) || $_SESSION['role'] != 'User') {
    header("Location: ../../index.php");
    exit();
}

function getNameFromLocalData($data, $code) {
    foreach ($data as $item) {
        if ($item['code'] === $code) {
            return $item['name'];
        }
    }
    return 'Not found';
}

$user_id = $_SESSION['account_number'];
$query = "SELECT * FROM useracc WHERE account_number = '$user_id'";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn)); // This line will explain the SQL error
}

$user = mysqli_fetch_assoc($result);

$provinceCode = str_pad($user['province'], 9, '0', STR_PAD_LEFT);
$municipalityCode = str_pad($user['municipality'], 9, '0', STR_PAD_LEFT);
$barangayCode = str_pad($user['barangay'], 9, '0', STR_PAD_LEFT);

$provinceName = getNameFromLocalData($provinceData, $provinceCode);
$municipalityName = getNameFromLocalData($municipalityData, $municipalityCode);
$barangayName = getNameFromLocalData($barangayData, $barangayCode);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Info</title>
    <meta charset="utf-8">
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
</head>
<body>
<?php
        include '../../includes/topbar.php';
        include '../../includes/sidebar2.php';
        include '../../includes/footer.php';
        include '../..//includes/loader.php';
    ?>
<div id="main-content" class="container-fluid mt-5 <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? '' : 'sidebar-expanded'; ?>" class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-10 col-lg-8 col-xl-8 col-xxl-8">
            <h2>Account Information</h2>
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-start">
                    <h2 class="mb-0"><?php echo $user['account_number']; ?></h2>
                </div>
                <div class="card-body text-start">
                    <h4 class="card-title mb-3">
                        <?php echo $user['firstname'] . ' ' . $user['middlename'] . ' ' . $user['lastname'] . ' ' . $user['suffix']; ?>
                    </h4>

                    <p><strong>Birthday:</strong> <?php echo date('F d, Y', strtotime($user['birthday'])); ?></p>
                    <p><strong>Age:</strong> <?php echo $user['age']; ?> years</p>
                    <p><strong>Gender:</strong> <?php echo $user['gender']; ?></p>
                    <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                    <p><strong>Contact Number:</strong> <?php echo $user['contactnumber']; ?></p>
                    <p><strong>Address:</strong> <?php echo $user['address'] . ' ' . $barangayName . ' ' . $municipalityName . ' ' . $provinceName; ?></p>

                    <h5 class="mt-3">
                        <strong>Balance:</strong>
                        <span class="text-success">₱<?php echo number_format($user['balance'], 2); ?></span>
                    </h5>
                    <h5 class="mt-3">
                        <strong>Points:</strong>
                        <span class="text-success"><?php echo number_format($user['points'], 2); ?></span>
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Bootstrap 5 JS and Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>

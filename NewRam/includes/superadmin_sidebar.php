<?php
$role = $_SESSION['role'];
$currentPage = $_SERVER['REQUEST_URI'];

// Define menu items per role
$menuItems = [
    'Superadmin' => [
        'dropdown_superadmin' => [
            'Dashboard' => ['icon' => 'fa-home', 'url' => '/NewRam/pages/superadmin/dashboard.php'],
            'User Registration' => ['icon' => 'fa-users-cog', 'url' => '/NewRam/pages/superadmin/admin/register.php'],
            'Employees' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/superadmin/admin/regemployee.php'],
            'Accounts' => [
                'icon' => 'fa-user-tie', 
                'url' => '#',
                'submenu' => [
                    'Activate' => ['icon' => 'fa-user-check', 'url' => '/NewRam/pages/superadmin/admin/features/activate_users.php'],
                    'Deactivate' => ['icon' => 'fa-user-slash', 'url' => '/NewRam/pages/superadmin/admin/features/disable_users.php'],
                    'Transfer Funds' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/superadmin/admin/features/transfer_user_funds.php'],
                ]
            ],
            'Revenue' => ['icon' => 'fa-coins', 'url' => '/NewRam/pages/superadmin/admin/revenue.php'],
            'Remittance Logs' => ['icon' => 'fa-list-alt', 'url' => '/NewRam/pages/superadmin/admin/translogscon.php'],
            'Load Transaction' => ['icon' => 'fa-history', 'url' => '/NewRam/pages/superadmin/admin/load_trans.php'],
            'Fare Update' => ['icon' => 'fa-money-bill-wave', 'url' => '/NewRam/pages/superadmin/admin/fareupdate.php'],
            'Reg Bus Info' => ['icon' => 'fa-bus', 'url' => '/NewRam/pages/superadmin/admin/businfo.php'],
            'Todays Bus Info' => ['icon' => 'fa-bus-alt', 'url' => '/NewRam/pages/superadmin/admin/busviewinfo.php'],
            'Registered Bus Info' => ['icon' => 'fa-list', 'url' => '/NewRam/pages/superadmin/admin/viewregbus.php'],
            'Feedbacks' => ['icon' => 'fa-eye', 'url' => '/NewRam/pages/superadmin/admin/feedbackview.php'],
            'Settings' => ['icon' => 'fa-cog', 'url' => '/NewRam/pages/superadmin/admin/settings.php'],
            'Activity Log' => ['icon' => 'fa-history', 'url' => '/NewRam/pages/superadmin/admin/activity_logs.php'],
        ],

        'dropdown_cashier' => [
            'Load RFID' => ['icon' => 'fa-id-card', 'url' => '/NewRam/pages/superadmin/cashier/loadrfidadmin.php'],
            'Remit' => ['icon' => 'fa-wallet', 'url' => '/NewRam/pages/superadmin/cashier/remitcashier.php'],
            'Load Transaction' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/superadmin/cashier/translogscashier.php'],
            'Remit Logs' => ['icon' => 'fa-file-invoice-dollar', 'url' => '/NewRam/pages/superadmin/cashier/remit_logs.php'],
            'Load Revenue' => ['icon' => 'fa-coins', 'url' => '/NewRam/pages/superadmin/cashier/loadtranscashier.php'],
        ],
        'dropdown_conductor' => [
            'Bus Fare' => ['icon' => 'fa-money-check-alt', 'url' => '/NewRam/pages/superadmin/conductor/busfare.php'],
            'Load RFID' => ['icon' => 'fa-id-card', 'url' => '/NewRam/pages/superadmin/conductor/loadrfidconductor.php'],
            'Load Transaction' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/superadmin/conductor/translogscon.php'],
            'Load Revenue' => ['icon' => 'fa-coins', 'url' => '/NewRam/pages/superadmin/conductor/loadtranscon.php'],
        ],
        'dropdown_user' => [
            'Recent Trips' => ['icon' => 'fa-route', 'url' => '/NewRam/pages/superadmin/user/recent_trips.php'],
            'Convert Points' => ['icon' => 'fa-gift', 'url' => '/NewRam/pages/superadmin/user/convert_points.php'],
            'Update Password' => ['icon' => 'fa-key', 'url' => '/NewRam/pages/superadmin/user/updatepass.php'],
            'Transaction Logs' => ['icon' => 'fa-file-alt', 'url' => '/NewRam/pages/superadmin/user/transactionlogs.php'],
        ]
    ],
];


// Default to an empty array if role doesn't match
$menu = $menuItems[$role] ?? ['before' => [], 'dropdown' => [], 'after' => []];


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
    .wrapper {
        display: flex;
        min-height: 100vh;
    }
    #main-content {
        transition: margin-left 0.3s ease;
        padding-top: 60px;
        padding-bottom: 65px;
    }
    .sidebar {
        z-index: 100;
        width: 300px;
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 60px;
        transition: transform 0.3s;
        box-shadow: 5px 0 15px rgba(0, 0, 0, 0.05);
        overflow-y: auto;
    }

    .sidebar.collapsed {
        transform: translateX(-100%);
    }

    .toggle-btn {
        position: fixed;
        left: 310px;
        top: 60px;
        z-index: 1000;
        transition: left 0.3s;
    }

    .toggle-btn.collapsed {
        left: 20px;
    }

    .nav-link {
        color: #4b5563;
        padding: 1.50rem 1.25rem;
        transition: all 0.2s;
    }

    .nav-link:hover {
        background: #f3f4f6;
        color: #1f2937;
    }

    .nav-link.active {
        background: #e5e7eb;
        color: #1f2937;
        font-weight: 500;
    }

    .sidebar-header {
        border-bottom: 1px solid #e5e7eb;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
        }
        .toggle-btn {
            left: 20px;
        }
        .nav-link {
        color: #4b5563;
        padding: 1.0rem 1.25rem;
        transition: all 0.2s;
        }
        .top-bar h4 {
        color: #3e64ff;
  } 
    }

    </style>
</head>
<body>
    <button class="btn btn-light toggle-btn shadow-sm collapsed" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
    </button>

    <div class="sidebar collapsed" id="sidebar">
    <div class="sidebar-header p-3">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo $_SESSION['role'] ?> Panel</h5>
        </div>
    </div>

    <nav class="nav flex-column mt-2">
        <?php if (!empty($menu['dropdown_superadmin'])): ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#superadminMenu" aria-expanded="false">
                    <i class="fa fa-user-shield me-2"></i> Admin <i class="fa fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="superadminMenu">
                    <ul class="nav flex-column ms-3">
                        <?php foreach ($menu['dropdown_superadmin'] as $label => $item): ?>
                            <li class="nav-item">
                                <?php if (isset($item['submenu'])): ?>
                                    <!-- Parent Dropdown -->
                                    <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#<?= str_replace(' ', '', $label); ?>Menu" aria-expanded="false">
                                        <i class="fa <?= $item['icon']; ?> me-2"></i> <?= $label; ?> <i class="fa fa-chevron-down ms-auto"></i>
                                    </a>
                                    <div class="collapse" id="<?= str_replace(' ', '', $label); ?>Menu">
                                        <ul class="nav flex-column ms-3">
                                            <?php foreach ($item['submenu'] as $subLabel => $subItem): ?>
                                                <li class="nav-item">
                                                    <a class="nav-link <?= ($currentPage == $subItem['url']) ? 'active' : ''; ?>" href="<?= $subItem['url']; ?>">
                                                        <i class="fa <?= $subItem['icon']; ?>"></i> <?= $subLabel; ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <!-- Regular Menu Item -->
                                    <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                                        <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>
        <?php if (!empty($menu['dropdown_cashier'])): ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#cashierMenu" aria-expanded="false">
                    <i class="fa fa-cash-register me-2"></i> Cashier <i class="fa fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="cashierMenu">
                    <ul class="nav flex-column ms-3">
                        <?php foreach ($menu['dropdown_cashier'] as $label => $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                                    <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if (!empty($menu['dropdown_conductor'])): ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#conductorMenu" aria-expanded="false">
                    <i class="fa fa-user-tie me-2"></i> Conductor <i class="fa fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="conductorMenu">
                    <ul class="nav flex-column ms-3">
                        <?php foreach ($menu['dropdown_conductor'] as $label => $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                                    <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if (!empty($menu['dropdown_user'])): ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#userMenu" aria-expanded="false">
                    <i class="fa fa-user me-2"></i> User <i class="fa fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="userMenu">
                    <ul class="nav flex-column ms-3">
                        <?php foreach ($menu['dropdown_user'] as $label => $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                                    <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

    </nav>
</div>


    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.querySelector('.toggle-btn');
            sidebar.classList.toggle('collapsed');
            toggleBtn.classList.toggle('collapsed');
        }
    </script>
</body>
</html>
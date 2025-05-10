<?php
$role = $_SESSION['role'];
$currentPage = $_SERVER['REQUEST_URI'];

// Define menu items per role
$menuItems = [
    'SuperAdmin' => [
        'before' => [
            'Dashboard' => ['icon' => 'fa-home', 'url' => '/NewRam/pages/admin/dashboard.php'],
            'User Management' => ['icon' => 'fa-users', 'url' => '/NewRam/pages/admin/users.php'],
        ],
        'dropdown' => [
            'Activate Account' => ['icon' => 'fa-user-check', 'url' => '/NewRam/pages/admin/features/activate_users.php'],
            'Disable Account' => ['icon' => 'fa-user-slash', 'url' => '/NewRam/pages/admin/features/disable_users.php'],
            'Transfer User Funds' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/admin/features/transfer_user_funds.php']
        ],
        'after' => [
            'System Settings' => ['icon' => 'fa-cogs', 'url' => '/NewRam/pages/admin/settings.php'],
        ]
    ],
    'Admin' => [
        'before' => [
            'Dashboard' => ['icon' => 'fa-home', 'url' => '/NewRam/pages/admin/dashboard.php'],
            'User Registration' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/admin/register.php'],
            'Employees' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/admin/regemployee.php'],
            'Revenue' => ['icon' => 'fa-cogs', 'url' => '/NewRam/pages/admin/revenue.php'],
            'Bus Routes' => ['icon' => 'fa-bus', 'url' => '/NewRam/pages/admin/bus_routes.php'],
        ],
        'dropdown' => [
            'Activate Account' => ['icon' => 'fa-user-check', 'url' => '/NewRam/pages/admin/features/activate_users.php'],
            'Disable Account' => ['icon' => 'fa-user-slash', 'url' => '/NewRam/pages/admin/features/disable_users.php'],
            'Transfer User Funds' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/admin/features/transfer_user_funds.php']
        ],
        'after' => [
            'Remittance Logs' => ['icon' => 'fa-list-alt', 'url' => '/NewRam/pages/admin/translogscon.php'],
            'Load Transaction' => ['icon' => 'fa-history', 'url' => '/NewRam/pages/admin/load_trans.php'],
            'Fare Update' => ['icon' => 'fa-arrow-up-1-9', 'url' => '/NewRam/pages/admin/fareupdate.php'],
            'Reg Bus Info' => ['icon' => 'fa-bus', 'url' => '/NewRam/pages/admin/businfo.php'],
            'View Bus Info' => ['icon' => 'fa-eye', 'url' => '/NewRam/pages/admin/busviewinfo.php'],
            'Feedbacks' => ['icon' => 'fa-eye', 'url' => '/NewRam/pages/admin/feedbackview.php'],
            'Settings' => ['icon' => 'fa-cog', 'url' => '/NewRam/pages/admin/settings.php'],
            'Profile' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/admin/profile.php'],
        ]
    ],
    'Conductor' => [
        'before' => [
            'Dashboard' => ['icon' => 'fa-tachometer-alt', 'url' => '/NewRam/pages/conductor/dashboard.php'],
            'Bus Fare' => ['icon' => 'fa-money-bill-wave', 'url' => '/NewRam/pages/conductor/busfare_auto.php'],
            'Load Card' => ['icon' => 'fa-id-card', 'url' => '/NewRam/pages/conductor/loadrfidconductor.php'],
            'Load Transaction' => ['icon' => 'fa-list-alt', 'url' => '/NewRam/pages/conductor/translogscon.php'],
            'Profile' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/conductor/profile.php'],
            //'Load Revenue' => ['icon' => 'fa-chart-line', 'url' => '/NewRam/pages/conductor/loadtranscon.php'],
        ],
        'dropdown' => [],
        'after' => []
    ],
    'Cashier' => [
        'before' => [
            'Dashboard' => ['icon' => 'fa-tachometer-alt', 'url' => '/NewRam/pages/cashier/dashboard.php'],
            'Load Card' => ['icon' => 'fa-id-card', 'url' => '/NewRam/pages/cashier/loadrfidadmin.php'],
            'Remit' => ['icon' => 'fa-hand-holding-usd', 'url' => '/NewRam/pages/cashier/remit.php'],
            'Load Transaction' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/cashier/translogscashier.php'],
            'Remit Logs' => ['icon' => 'fa-clipboard-list', 'url' => '/NewRam/pages/cashier/remit_logs.php'],
            'Load Revenue' => ['icon' => 'fa-chart-line', 'url' => '/NewRam/pages/cashier/loadtranscashier.php'],
            'Profile' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/cashier/profile.php'],
        ],
        'dropdown' => [],
        'after' => []
    ],
    'User' => [
        'before' => [
            'Dashboard' => ['icon' => 'fa-home', 'url' => '/NewRam/pages/user/dashboard.php'],
            'Personal Info' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/user/personal_info.php'],
            'Recent Trips' => ['icon' => 'fa-route', 'url' => '/NewRam/pages/user/recent_trips.php'],
            //'Bus In Transit' => ['icon' => 'fa-map-marker-alt', 'url' => '/NewRam/pages/user/bus_current_location.php'],
            'Convert Points' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/user/convert_points.php'],
            'Update Password' => ['icon' => 'fa-key', 'url' => '/NewRam/pages/user/update_pass.php'],
            'Transaction Logs' => ['icon' => 'fa-file-alt', 'url' => '/NewRam/pages/user/transaction_logs.php'],
        ],
        'after' => [],
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
    margin-left: 0; /* Default margin */
}

.sidebar {
    z-index: 1001;
    width: 300px;
    background: #ffffff;
    border-right: 1px solid #e5e7eb;
    height: calc(100vh - 60px); /* Adjust height to account for top bar */
    overflow-y: auto;
    position: fixed;
    left: 0;
    top: 60px;
    transition: transform 0.3s, width 0.3s;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.05);
    overflow-y: auto;
}

.sidebar.collapsed {
    transform: translateX(-100%);
    width: 0; /* Sidebar collapsed width */
}

#main-content.sidebar-expanded {
    margin-left: 150px; /* When sidebar is expanded */
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
        background: #499dde;
        color: #1f2937;
    }

    .nav-link.active {
        background: #f1c40f;
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
        #main-content {
            margin-left: 0 !important; /* Ensure it doesn't shift when sidebar is collapsed */
        }
    }

    </style>
</head>
<body>
    <button class="btn btn-light toggle-btn shadow-sm <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? 'collapsed' : ''; ?>" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
    </button>


    <div class="sidebar <?php echo ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Cashier') ? 'collapsed' : ''; ?>" id="sidebar">
    <div class="sidebar-header p-3">
        <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0"><?php echo $_SESSION['role'] ?> Panel</h5>
        </div>
    </div>

    <nav class="nav flex-column mt-2">
        <?php foreach ($menu['before'] as $label => $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                    <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                </a>
            </li>
        <?php endforeach; ?>

        <?php if (!empty($menu['dropdown'])): ?>
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center" href="#" data-bs-toggle="collapse" data-bs-target="#accountsMenu" aria-expanded="false">
                    <i class="fa fa-sticky-note"></i> Accounts <i class="fa fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="accountsMenu">
                    <ul class="nav flex-column ms-3">
                        <?php foreach ($menu['dropdown'] as $label => $item): ?>
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

        <?php foreach ($menu['after'] as $label => $item): ?>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                    <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </nav>
</div>


    
    <script>
        function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.querySelector('.toggle-btn');
    const mainContent = document.getElementById('main-content');
    
    sidebar.classList.toggle('collapsed');
    toggleBtn.classList.toggle('collapsed');
    
    if (sidebar.classList.contains('collapsed')) {
        mainContent.classList.remove('sidebar-expanded');
    } else {
        mainContent.classList.add('sidebar-expanded');
    }
}

    </script>
</body>
</html>
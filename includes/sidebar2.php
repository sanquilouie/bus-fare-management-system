<?php
$role = $_SESSION['role'];
$currentPage = $_SERVER['REQUEST_URI']; 

$menuItemsBefore = [
    'Dashboard' => ['icon' => 'fa-home', 'url' => '/NewRam/pages/admin/dashboard.php'],
    'Registration' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/admin/register.php'],
    'Reg Employee' => ['icon' => 'fa-user', 'url' => '/NewRam/pages/admin/regemployee.php'],
    'Revenue' => ['icon' => 'fa-cogs', 'url' => '/NewRam/pages/admin/revenue.php'],
];

$menuItemsAfter = [
    'Fare Update' => ['icon' => 'fa-arrow-up-1-9', 'url' => '/NewRam/pages/admin/fareupdate.php'],
    'Reg Bus Info' => ['icon' => 'fa-bus', 'url' => '/NewRam/pages/admin/businfo.php'],
    'View Bus Info' => ['icon' => 'fa-eye', 'url' => '/NewRam/pages/admin/busviewinfo.php'],
    'Feedbacks' => ['icon' => 'fa-eye', 'url' => '/NewRam/pages/admin/feedbackview.php'],
];

$dropdownItems = [
    'Activate Account' => ['icon' => 'fa-user-check', 'url' => '/NewRam/pages/admin/features/activate_users.php'],
    'Disable Account' => ['icon' => 'fa-user-slash', 'url' => '/NewRam/pages/admin/features/disable_users.php'],
    'Transfer User Funds' => ['icon' => 'fa-exchange-alt', 'url' => '/NewRam/pages/admin/features/transfer_user_funds.php']
];

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

    .main-content {
        flex: 1;
        padding: 20px;
        transition: margin-left 0.3s;
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
    }

    </style>
</head>
<body>
    <button class="btn btn-light toggle-btn shadow-sm" onclick="toggleSidebar()">
        <i class="bi bi-list fs-5"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header p-3">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Admin Panel</h5>
            </div>
        </div>

        <nav class="nav flex-column mt-2">
            <?php if ($role == 'Admin'): ?>
                <?php foreach ($menuItemsBefore as $label => $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                            <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($currentPage, array_column($dropdownItems, 'url')) ? 'active' : ''; ?>" 
                    href="#" id="accountsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-sticky-note"></i> Accounts
                    </a>
                    <div class="dropdown-menu" aria-labelledby="accountsDropdown">
                        <?php foreach ($dropdownItems as $label => $item): ?>
                            <a class="dropdown-item <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                                <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <?php foreach ($menuItemsAfter as $label => $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                            <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
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
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
    'Activate Account' => '/NewRam/pages/admin/features/activate_users.php',
    'Disable Account' => '/NewRam/pages/admin/features/disable_users.php',
    'Transfer User Funds' => '/NewRam/pages/admin/features/transfer_user_funds.php'
];
?>

<nav class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
        <?php if ($role == 'Admin'): ?>
            <!-- Before Accounts -->
            <?php foreach ($menuItemsBefore as $label => $item): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                        <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                    </a>
                </li>
                <div class="sidebar-divider"></div>
            <?php endforeach; ?>

            <!-- Accounts Dropdown (Placed in the middle) -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?= in_array($currentPage, $dropdownItems) ? 'active' : ''; ?>" href="#" id="accountsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-sticky-note"></i> Accounts
                </a>
                <ul class="dropdown-menu" aria-labelledby="accountsDropdown">
                    <?php foreach ($dropdownItems as $label => $url): ?>
                        <li><a class="dropdown-item <?= ($currentPage == $url) ? 'active' : ''; ?>" href="<?= $url; ?>"> <?= $label; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <div class="sidebar-divider"></div>

            <!-- After Accounts -->
            <?php foreach ($menuItemsAfter as $label => $item): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentPage == $item['url']) ? 'active' : ''; ?>" href="<?= $item['url']; ?>">
                        <i class="fa <?= $item['icon']; ?>"></i> <?= $label; ?>
                    </a>
                </li>
                <div class="sidebar-divider"></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</nav>

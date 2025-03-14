<?php
$role = $_SESSION['role'];
?>
<nav class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
        <?php if ($role == 'Admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/dashboard.php') ? 'active' : ''; ?>"
                    href="/NewRam/pages/admin/dashboard.php">
                    <i class="fa fa-home"></i> Dashboard
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/register.php') ? 'active' : ''; ?>" 
                    href="/NewRam/pages/admin/register.php">
                    <i class="fa fa-user"></i> Registration
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/regemployeee.php') ? 'active' : ''; ?>" 
                href="/NewRam/pages/admin/regemployee.php">
                    <i class="fa fa-user"></i> Reg Employee
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle <?php echo (
                   $currentPage == '/NewRam/pages/admin/features/activate_users.php' 
                || $currentPage == '/NewRam/pages/admin/features/disable_users.php' 
                || $currentPage == '/NewRam/pages/admin/features/transfer_user_funds.php') ? 'active' : ''; ?>" 
                href="#" id="accountsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-sticky-note"></i> Accounts
                </a>
                <ul class="dropdown-menu" aria-labelledby="accountsDropdown">
                    <li><a class="dropdown-item <?php echo ($currentPage == '/NewRam/pages/admin/features/activate_users.php') ? 'active' : ''; ?>" 
                    href="/NewRam/pages/admin/features/activate_users.php">Activate Account</a></li>
                    <li><a class="dropdown-item <?php echo ($currentPage == '/NewRam/pages/admin/features/disable_users.php') ? 'active' : ''; ?>" 
                    href="/NewRam/pages/admin/features/disable_users.php">Disable Account</a></li>
                    <li><a class="dropdown-item <?php echo ($currentPage == '/NewRam/pages/admin/features/transfer_user_funds.php') ? 'active' : ''; ?>" 
                    href="/NewRam/pages/admin/features/transfer_user_funds.php">Transfer User Funds</a></li>
                </ul>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/revenue.php') ? 'active' : ''; ?>" 
                href="/NewRam/pages/admin/revenue.php">
                    <i class="fa fa-cogs"></i> Revenue
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/fareupdate.php') ? 'active' : ''; ?>" 
                href="/NewRam/pages/admin/fareupdate.php">
                    <i class="fa fa-arrow-up-1-9"></i> Fare Update
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/businfo.php') ? 'active' : ''; ?>" 
                href="/NewRam/pages/admin/businfo.php">
                    <i class="fa fa-bus"></i> Reg Bus Info
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/busviewinfo.php') ? 'active' : ''; ?>"
                    href="/NewRam/pages/admin/busviewinfo.php">
                    <i class="fa fa-eye"></i> View Bus Info
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == '/NewRam/pages/admin/feedbackview.php') ? 'active' : ''; ?>"
                    href="/NewRam/pages/admin/feedbackview.php">
                    <i class="fa fa-eye"></i> Feedbacks
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
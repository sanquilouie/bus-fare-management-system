<?php
$role = $_SESSION['role'];
?>
<nav class="sidebar">
    <h4>Admin Panel</h4>
    <ul class="nav flex-column">
        <?php if ($role == 'Admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>"
                    href="dashboard.php">
                    <i class="fa fa-home"></i> Dashboard
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'register.php') ? 'active' : ''; ?>" href="register.php">
                    <i class="fa fa-user"></i> Registration
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'regemploye.php') ? 'active' : ''; ?>" href="regemploye.php">
                    <i class="fa fa-user"></i> Reg Employee
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'activate.php') ? 'active' : ''; ?>" href="activate.php">
                    <i class="fa fa-sticky-note"></i> Accounts
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'revenue.php') ? 'active' : ''; ?>" href="revenue.php">
                    <i class="fa fa-cogs"></i> Revenue
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'fareupdate.php') ? 'active' : ''; ?>" href="fareupdate.php">
                    <i class="fa fa-arrow-up-1-9"></i> Fare Update
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'businfo.php') ? 'active' : ''; ?>" href="businfo.php">
                    <i class="fa fa-bus"></i> Reg Bus Info
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'busviewinfo.php') ? 'active' : ''; ?>"
                    href="busviewinfo.php">
                    <i class="fa fa-eye"></i> View Bus Info
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link <?php echo ($currentPage == 'feedbackview.php') ? 'active' : ''; ?>"
                    href="feedbackview.php">
                    <i class="fa fa-eye"></i> Feedbacks
                </a>
            </li>
            <div class="sidebar-divider"></div>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="fa fa-sign-out-alt"></i> Logout
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
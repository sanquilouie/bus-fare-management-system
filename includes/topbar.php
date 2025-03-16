<div class="top-bar d-flex justify-content-between align-items-center p-3">
    <div class="d-flex align-items-center">
        <img src="/NewRam/assets/images/logo.png" alt="Ramstar Logo" class="me-2" width="80">
        <h4 class="m-0 d-none d-md-block">Ramstar</h4> <!-- Hide on small screens -->
    </div>
    <div class="profile d-flex align-items-center">
        <i class="fas fa-user-circle fa-2x d-none d-md-inline"></i> <!-- Hide on small screens -->
        <span class="ms-2"><?php echo $_SESSION['role'] ?></span> <!-- Always visible -->
        <a href="../../auth/logout.php" class="btn btn-sm btn-light ms-2 d-md-none">
            <i class="fas fa-sign-out-alt"></i> <!-- Show icon only on small screens -->
        </a>
        <a href="../../auth/logout.php" class="btn btn-sm btn-light ms-2 d-none d-md-inline">
            Logout
        </a> <!-- Show text only on larger screens -->
    </div>
</div>

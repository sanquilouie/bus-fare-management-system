<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        top: 20px;
        z-index: 1000;
        transition: left 0.3s;
    }

    .toggle-btn.collapsed {
        left: 20px;
    }

    .nav-link {
        color: #4b5563;
        padding: 0.75rem 1.25rem;
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

    .notification-badge {
        background: #ef4444;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
    }

    .user-status {
        width: 10px;
        height: 10px;
        background: #10b981;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
        }
        .toggle-btn {
            left: 20px;
        }
    }

    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Main Content -->
        <div class="main-content">
            <h1 class="mb-4">Main Content Area</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            <!-- Add more content here -->
        </div>

        <!-- Toggle Button -->
        <button class="btn btn-light toggle-btn shadow-sm" onclick="toggleSidebar()">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Dashboard</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-light">
                            <i class="bi bi-gear"></i>
                        </button>
                        <button class="btn btn-sm btn-light">
                            <i class="bi bi-bell"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="p-3 border-bottom">
                <div class="d-flex align-items-center">
                    <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="User">
                    <div>
                        <h6 class="mb-0">John Doe</h6>
                        <small class="text-muted">
                            <span class="user-status"></span>
                            Online
                        </small>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="nav flex-column mt-2">
                <a href="#" class="nav-link active">
                    <i class="bi bi-house me-2"></i>
                    Dashboard
                </a>
                <a href="#" class="nav-link">
                    <i class="bi bi-person me-2"></i>
                    Profile
                </a>
                <a href="#" class="nav-link d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-chat me-2"></i>
                        Messages
                    </div>
                    <span class="notification-badge">3</span>
                </a>
                <a href="#" class="nav-link">
                    <i class="bi bi-calendar me-2"></i>
                    Calendar
                </a>
                <a href="#" class="nav-link">
                    <i class="bi bi-graph-up me-2"></i>
                    Analytics
                </a>
                <a href="#" class="nav-link">
                    <i class="bi bi-folder me-2"></i>
                    Projects
                </a>
            </nav>

            <!-- Recent Activity -->
            <div class="p-3 mt-3">
                <h6 class="text-muted mb-3">Recent Activity</h6>
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-light rounded p-2 me-2">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div>
                        <small class="d-block">Updated Project Files</small>
                        <small class="text-muted">2 hours ago</small>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="bg-light rounded p-2 me-2">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <small class="d-block">Team Meeting</small>
                        <small class="text-muted">4 hours ago</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
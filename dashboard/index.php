<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SchedulinkXM</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #6c757d;
            --success: #2ecc71;
            --info: #00b4d8;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #f1f3f5;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: white;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.03);
            z-index: 100;
            transition: all 0.3s;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--dark);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .sidebar-nav {
            padding: 1.5rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--gray);
            border-radius: 8px;
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Header */
        .header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-weight: 600;
        }

        .user-name {
            font-weight: 500;
            margin-right: 1rem;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-icon.primary {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .stat-icon.success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }

        .stat-icon.warning {
            background-color: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        .stat-icon.danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger);
        }

        .stat-title {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        /* Main Cards */
        .main-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .main-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 1px 15px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin: 0;
        }

        .card-action {
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .card-action i {
            margin-left: 0.25rem;
            font-size: 0.75rem;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1rem;
        }

        .feature-text {
            flex: 1;
        }

        .feature-name {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .feature-desc {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .btn-feature {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-feature.primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-feature.primary:hover {
            background-color: #3a56d4;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <span>SchedulinkXM</span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="manage_exams.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Manage Exams</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="manage_lecturers.php" class="nav-link">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Manage Lecturers</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="manage_halls.php" class="nav-link">
                    <i class="fas fa-building"></i>
                    <span>Manage Halls</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="view_allocations.php" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    <span>View Allocations</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="reports.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="header">
            <div>
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="user-menu">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <a href="../auth/logout.php" class="btn btn-sm btn-outline-danger ms-2">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <div class="page-header">
                <h1 class="page-title">Dashboard Overview</h1>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary">Today</button>
                    <button class="btn btn-sm btn-outline-secondary">Week</button>
                    <button class="btn btn-sm btn-outline-primary">Month</button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <p class="stat-title">Upcoming Exams</p>
                            <h3 class="stat-value">24</h3>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <p class="stat-title">Active Lecturers</p>
                            <h3 class="stat-value">18</h3>
                        </div>
                        <div class="stat-icon success">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <p class="stat-title">Available Halls</p>
                            <h3 class="stat-value">12</h3>
                        </div>
                        <div class="stat-icon warning">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div>
                            <p class="stat-title">Allocation Rate</p>
                            <h3 class="stat-value">96%</h3>
                        </div>
                        <div class="stat-icon danger">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Cards -->
            <div class="main-cards">
                <div class="main-card">
                    <div class="card-header">
                        <h2 class="card-title">Quick Actions</h2>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-name">Schedule New Exam</div>
                                <div class="feature-desc">Create a new examination event</div>
                            </div>
                            <button class="btn-feature primary">Add</button>
                        </li>
                        <li class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-name">Add Lecturer</div>
                                <div class="feature-desc">Register new faculty member</div>
                            </div>
                            <button class="btn-feature primary">Add</button>
                        </li>
                        <li class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-print"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-name">Generate Report</div>
                                <div class="feature-desc">Create system status report</div>
                            </div>
                            <button class="btn-feature primary">Generate</button>
                        </li>
                    </ul>
                </div>

                <div class="main-card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Activities</h2>
                        <a href="#" class="card-action">
                            View All <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <ul class="feature-list">
                        <li class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-name">Exam Scheduled</div>
                                <div class="feature-desc">Final Exams - Computer Science</div>
                            </div>
                            <small class="text-muted">2h ago</small>
                        </li>
                        <li class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-name">Lecturer Updated</div>
                                <div class="feature-desc">Dr. Smith's profile</div>
                            </div>
                            <small class="text-muted">1d ago</small>
                        </li>
                        <li class="feature-item">
                            <div class="feature-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="feature-text">
                                <div class="feature-name">Hall Added</div>
                                <div class="feature-desc">Main Auditorium (Capacity: 300)</div>
                            </div>
                            <small class="text-muted">3d ago</small>
                        </li>
                    </ul>
                </div>

                <div class="main-card">
                    <div class="card-header">
                        <h2 class="card-title">System Features</h2>
                    </div>
                    <div class="row row-cols-2 g-3">
                        <div class="col">
                            <a href="manage_exams.php" class="card h-100 feature-card">
                                <div class="card-body text-center">
                                    <div class="stat-icon primary mb-2">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <h6 class="mb-0">Exams</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="manage_lecturers.php" class="card h-100 feature-card">
                                <div class="card-body text-center">
                                    <div class="stat-icon success mb-2">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                    <h6 class="mb-0">Lecturers</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="manage_halls.php" class="card h-100 feature-card">
                                <div class="card-body text-center">
                                    <div class="stat-icon warning mb-2">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <h6 class="mb-0">Halls</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="reports.php" class="card h-100 feature-card">
                                <div class="card-body text-center">
                                    <div class="stat-icon danger mb-2">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h6 class="mb-0">Reports</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>

</html>
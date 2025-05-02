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
                <a href="payment_management.php" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    <span>Payment Management</span>
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
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row">
        <div class="col-md-4 mb-3">
            <a href="manage_exams.php" class="btn btn-primary w-100">Manage Exams</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="manage_lecturers.php" class="btn btn-secondary w-100">Manage Lecturers</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="manage_halls.php" class="btn btn-info w-100">Manage Halls</a>
        </div>
        <div class="col-md-4 mb-3">
            <a href="view_allocations.php" class="btn btn-success w-100">View Allocations</a>
        </div>
       
        <div class="col-md-4 mb-3">
            <a href="reports.php" class="btn btn-warning w-100">Reports</a>
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
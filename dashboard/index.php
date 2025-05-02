<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/bootstrap/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">SHECODERess Admin</span>
        <div class="d-flex">
            <span class="text-white me-3">Welcome, <?php echo $_SESSION['username']; ?></span>
            <a class="btn btn-outline-light" href="../auth/logout.php">Logout</a>
        </div>
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
</div>
</body>
</html>

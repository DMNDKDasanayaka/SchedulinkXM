<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SchedulinkXM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS (optional if already included elsewhere) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Internal CSS for Navbar -->
    <style>
        /* Navbar Base */
        .navbar {
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.3rem;
            letter-spacing: 0.5px;
        }

        .navbar-nav .nav-link {
            color: #ffffff;
            margin-right: 10px;
            transition: background-color 0.2s ease, color 0.2s ease;
            border-radius: 4px;
            padding: 8px 12px;
        }

        .navbar-nav .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .navbar-nav .nav-link.active,
        .navbar-nav .nav-link:focus {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%28255,255,255,0.8%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
        }

        @media (max-width: 992px) {
            .navbar-nav .nav-link {
                margin-right: 0;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="/">SchedulinkXM</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="exams/list.php">Exams</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="lecturers/list.php">Lecturers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="halls/list.php">Halls</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/reports/allocations">Reports</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/logout">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Optional Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

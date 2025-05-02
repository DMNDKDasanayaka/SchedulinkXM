<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SchedulinkXM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Internal Styles -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
        }

        footer {
            background-color: #ffffff;
            text-align: center;
            padding: 16px 0;
            margin-top: 50px;
            border-top: 1px solid #e0e0e0;
            color: #555555;
            font-size: 16px;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
        }

        footer p {
            margin: 0;
        }

        @media (max-width: 576px) {
            footer {
                font-size: 14px;
                padding: 12px 10px;
            }
        }
    </style>
</head>
<body>

    <!-- Your page content -->

    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">SchedulinkXM - Linking Duties, Halls, and Exams Seamlessly &copy; <?= date('Y') ?></p>
        </div>
    </footer>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/script.js"></script>
</body>
</html>

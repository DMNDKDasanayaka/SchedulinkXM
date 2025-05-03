<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/db_connect.php';

// Function to format date for display
function formatDate($date) {
    return date('Y.m.d', strtotime($date));
}

// Function to format time for display
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

// Handle CSV download (Excel-compatible)
if (isset($_POST['download_csv'])) {
    $report_type = $_POST['report_type'];
    $filename = $report_type . '_report_' . date('Ymd') . '.csv';

    // Set headers to force the browser to download the file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');

    // Open output stream
    $output = fopen('php://output', 'w');

    switch ($report_type) {
        case 'exam_schedule':
            // Fetch exam schedule data
            $sql = "SELECT e.*, eh.hall_name, l.name AS supervisor 
                    FROM exams e
                    LEFT JOIN allocations a ON e.exam_id = a.exam_id
                    LEFT JOIN exam_halls eh ON a.hall_id = eh.hall_id
                    LEFT JOIN lecturers l ON a.supervisor_id = l.lecturer_id
                    ORDER BY e.date, e.start_time";
            $result = mysqli_query($conn, $sql);

            // Add column headers
            fputcsv($output, [
                'Exam ID', 'Subject Code', 'Subject Name', 'Degree', 
                'Date', 'Day', 'Start Time', 'End Time', 
                'Regular Students', 'Repeaters', 'Total Students',
                'Hall', 'Supervisor'
            ]);

            // Add data rows
            while ($row = mysqli_fetch_assoc($result)) {
                $day = date('l', strtotime($row['date']));
                $total = $row['student_count'] + $row['repeaters'];
                
                fputcsv($output, [
                    $row['exam_id'],
                    $row['subject_code'],
                    $row['subject_name'],
                    $row['degree'],
                    formatDate($row['date']),
                    $day,
                    formatTime($row['start_time']),
                    formatTime($row['end_time']),
                    $row['student_count'],
                    $row['repeaters'],
                    $total,
                    $row['hall_name'] ?? 'Not assigned',
                    $row['supervisor'] ?? 'Not assigned'
                ]);
            }
            break;

        case 'lecturer_allocation':
            // Fetch lecturer allocation data
            $sql = "SELECT l.lecturer_id, l.name, l.designation, l.department,
                    COUNT(DISTINCT a.allocation_id) AS exam_count,
                    GROUP_CONCAT(DISTINCT e.subject_name SEPARATOR '; ') AS subjects,
                    GROUP_CONCAT(DISTINCT CONCAT(e.date, ' (', e.start_time, ')') SEPARATOR '; ') AS exam_dates
                    FROM lecturers l
                    LEFT JOIN allocations a ON l.lecturer_id = a.supervisor_id
                    LEFT JOIN exams e ON a.exam_id = e.exam_id
                    GROUP BY l.lecturer_id
                    ORDER BY l.department, l.name";
            $result = mysqli_query($conn, $sql);

            // Add column headers
            fputcsv($output, [
                'Lecturer ID', 'Name', 'Designation', 'Department',
                'Exam Count', 'Assigned Subjects', 'Exam Dates/Times'
            ]);

            // Add data rows
            while ($row = mysqli_fetch_assoc($result)) {
                fputcsv($output, [
                    $row['lecturer_id'],
                    $row['name'],
                    $row['designation'],
                    $row['department'],
                    $row['exam_count'],
                    $row['subjects'] ?? 'None',
                    $row['exam_dates'] ?? 'None'
                ]);
            }
            break;

        case 'hall_allocation':
            // Fetch hall allocation data
            $sql = "SELECT eh.hall_id, eh.hall_name, eh.capacity, eh.location,
                    COUNT(DISTINCT a.allocation_id) AS exam_count,
                    SUM(e.student_count + e.repeaters) AS total_students,
                    GROUP_CONCAT(DISTINCT e.subject_name SEPARATOR '; ') AS subjects,
                    GROUP_CONCAT(DISTINCT CONCAT(e.date, ' (', e.start_time, ')') SEPARATOR '; ') AS exam_dates
                    FROM exam_halls eh
                    LEFT JOIN allocations a ON eh.hall_id = a.hall_id
                    LEFT JOIN exams e ON a.exam_id = e.exam_id
                    GROUP BY eh.hall_id
                    ORDER BY eh.hall_name";
            $result = mysqli_query($conn, $sql);

            // Add column headers
            fputcsv($output, [
                'Hall ID', 'Hall Name', 'Capacity', 'Location',
                'Exam Count', 'Total Students', 'Utilization %',
                'Assigned Subjects', 'Exam Dates/Times'
            ]);

            // Add data rows
            while ($row = mysqli_fetch_assoc($result)) {
                $utilization = ($row['capacity'] > 0) ? round(($row['total_students'] / $row['capacity']) * 100, 2) : 0;
                
                fputcsv($output, [
                    $row['hall_id'],
                    $row['hall_name'],
                    $row['capacity'],
                    $row['location'],
                    $row['exam_count'],
                    $row['total_students'] ?? 0,
                    $utilization,
                    $row['subjects'] ?? 'None',
                    $row['exam_dates'] ?? 'None'
                ]);
            }
            break;
    }

    fclose($output);
    exit();
}

// Handle report generation
$report_html = '';
if (isset($_POST['report_type'])) {
    $report_type = $_POST['report_type'];
    
    switch ($report_type) {
        case 'exam_schedule':
            $sql = "SELECT e.*, eh.hall_name, l.name AS supervisor 
                    FROM exams e
                    LEFT JOIN allocations a ON e.exam_id = a.exam_id
                    LEFT JOIN exam_halls eh ON a.hall_id = eh.hall_id
                    LEFT JOIN lecturers l ON a.supervisor_id = l.lecturer_id
                    ORDER BY e.date, e.start_time";
            $result = mysqli_query($conn, $sql);

            $report_html = '<h3 class="mt-4">Exam Schedule Report</h3>';
            $report_html .= '<div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Exam ID</th>
                                        <th>Subject</th>
                                        <th>Code</th>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Students</th>
                                        <th>Hall</th>
                                        <th>Supervisor</th>
                                    </tr>
                                </thead>
                                <tbody>';

            while ($row = mysqli_fetch_assoc($result)) {
                $day = date('l', strtotime($row['date']));
                $total = $row['student_count'] + $row['repeaters'];
                
                $report_html .= '<tr>
                                <td>' . $row['exam_id'] . '</td>
                                <td>' . htmlspecialchars($row['subject_name']) . '</td>
                                <td>' . htmlspecialchars($row['subject_code']) . '</td>
                                <td>' . formatDate($row['date']) . '</td>
                                <td>' . $day . '</td>
                                <td>' . formatTime($row['start_time']) . ' - ' . formatTime($row['end_time']) . '</td>
                                <td>' . $total . ' (P: ' . $row['student_count'] . ', R: ' . $row['repeaters'] . ')</td>
                                <td>' . htmlspecialchars($row['hall_name'] ?? 'Not assigned') . '</td>
                                <td>' . htmlspecialchars($row['supervisor'] ?? 'Not assigned') . '</td>
                            </tr>';
            }

            $report_html .= '</tbody></table></div>';
            break;

        case 'lecturer_allocation':
            $sql = "SELECT l.lecturer_id, l.name, l.designation, l.department,
                    COUNT(DISTINCT a.allocation_id) AS exam_count,
                    GROUP_CONCAT(DISTINCT e.subject_name SEPARATOR '; ') AS subjects
                    FROM lecturers l
                    LEFT JOIN allocations a ON l.lecturer_id = a.supervisor_id
                    LEFT JOIN exams e ON a.exam_id = e.exam_id
                    GROUP BY l.lecturer_id
                    ORDER BY l.department, l.name";
            $result = mysqli_query($conn, $sql);

            $report_html = '<h3 class="mt-4">Lecturer Allocation Report</h3>';
            $report_html .= '<div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Lecturer ID</th>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Exam Count</th>
                                        <th>Assigned Subjects</th>
                                    </tr>
                                </thead>
                                <tbody>';

            while ($row = mysqli_fetch_assoc($result)) {
                $report_html .= '<tr>
                                <td>' . $row['lecturer_id'] . '</td>
                                <td>' . htmlspecialchars($row['name']) . '</td>
                                <td>' . htmlspecialchars($row['designation']) . '</td>
                                <td>' . htmlspecialchars($row['department']) . '</td>
                                <td>' . $row['exam_count'] . '</td>
                                <td>' . htmlspecialchars($row['subjects'] ?? 'None') . '</td>
                            </tr>';
            }

            $report_html .= '</tbody></table></div>';
            break;

        case 'hall_allocation':
            $sql = "SELECT eh.hall_id, eh.hall_name, eh.capacity, eh.location,
                    COUNT(DISTINCT a.allocation_id) AS exam_count,
                    SUM(e.student_count + e.repeaters) AS total_students,
                    GROUP_CONCAT(DISTINCT e.subject_name SEPARATOR '; ') AS subjects
                    FROM exam_halls eh
                    LEFT JOIN allocations a ON eh.hall_id = a.hall_id
                    LEFT JOIN exams e ON a.exam_id = e.exam_id
                    GROUP BY eh.hall_id
                    ORDER BY eh.hall_name";
            $result = mysqli_query($conn, $sql);

            $report_html = '<h3 class="mt-4">Hall Allocation Report</h3>';
            $report_html .= '<div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Hall ID</th>
                                        <th>Hall Name</th>
                                        <th>Capacity</th>
                                        <th>Location</th>
                                        <th>Exam Count</th>
                                        <th>Total Students</th>
                                        <th>Utilization %</th>
                                        <th>Assigned Subjects</th>
                                    </tr>
                                </thead>
                                <tbody>';

            while ($row = mysqli_fetch_assoc($result)) {
                $utilization = ($row['capacity'] > 0) ? round(($row['total_students'] / $row['capacity']) * 100, 2) : 0;
                $utilization_class = ($utilization > 100) ? 'table-danger' : (($utilization > 80) ? 'table-warning' : '');
                
                $report_html .= '<tr class="' . $utilization_class . '">
                                <td>' . $row['hall_id'] . '</td>
                                <td>' . htmlspecialchars($row['hall_name']) . '</td>
                                <td>' . $row['capacity'] . '</td>
                                <td>' . htmlspecialchars($row['location']) . '</td>
                                <td>' . $row['exam_count'] . '</td>
                                <td>' . ($row['total_students'] ?? 0) . '</td>
                                <td>' . $utilization . '%</td>
                                <td>' . htmlspecialchars($row['subjects'] ?? 'None') . '</td>
                            </tr>';
            }

            $report_html .= '</tbody></table></div>';
            break;
    }
}
?>

<div class="container mt-4">
    <h1>Generate Reports</h1>
    <p>Generate detailed reports on exam schedules, lecturer allocations, and hall allocations.</p>

    <!-- Form for generating reports -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="report_type" class="form-label">Select Report Type</label>
                <select class="form-select" id="report_type" name="report_type" required>
                    <option value="">-- Select Report Type --</option>
                    
                    <option value="lecturer_allocation" <?= isset($_POST['report_type']) && $_POST['report_type'] == 'lecturer_allocation' ? 'selected' : '' ?>>Lecturer Allocation</option>
                    <option value="hall_allocation" <?= isset($_POST['report_type']) && $_POST['report_type'] == 'hall_allocation' ? 'selected' : '' ?>>Hall Allocation</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end mb-3">
                <button type="submit" class="btn btn-primary me-2">Generate Report</button>
                
                <!-- Download button (only shown when a report is generated) -->
                <?php if (isset($_POST['report_type'])): ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="report_type" value="<?= htmlspecialchars($_POST['report_type']) ?>">
                        <button type="submit" name="download_csv" class="btn btn-success">
                            <i class="bi bi-download"></i> Download CSV
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Display generated report -->
    <?= $report_html ?>
</div>

<?php include '../includes/footer.php'; ?>
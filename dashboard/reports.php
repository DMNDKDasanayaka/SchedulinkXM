<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/db_connect.php';

// Handle CSV download (Excel-compatible)
if (isset($_POST['download_csv'])) {
    $report_type = $_POST['report_type'];

    if ($report_type == 'exam_schedule') {
        // Fetch exam schedule data from the database
        $sql = "SELECT * FROM exams";
        $result = mysqli_query($conn, $sql);

        // Set headers to force the browser to download the file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="exam_schedule.csv"');

        // Open output stream (the file that will be downloaded)
        $output = fopen('php://output', 'w');

        // Add column headers to CSV
        fputcsv($output, ['Subject Code', 'Subject Name', 'Degree', 'Date', 'Start Time', 'End Time', 'Student Count']);

        // Loop through the result and write each row to the CSV
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, [
                $row['subject_code'],
                $row['subject_name'],
                $row['degree'],
                $row['date'],
                $row['start_time'],
                $row['end_time'],
                $row['student_count']
            ]);
        }

        // Close the output stream
        fclose($output);
        exit();
    }
}
?>

<div class="container mt-4">
    <h1>Generate Reports</h1>
    <p>Generate detailed reports on exam schedules, lecturer allocations, and hall allocations.</p>

    <!-- Form for generating reports -->
    <form method="POST">
        <div class="mb-3">
            <label for="report_type" class="form-label">Select Report Type</label>
            <select class="form-select" id="report_type" name="report_type" required>
                <option value="exam_schedule">Exam Schedule</option>
                <option value="lecturer_allocation">Lecturer Allocation</option>
                <option value="hall_allocation">Hall Allocation</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Generate Report</button>
    </form>

    <!-- Form for downloading CSV -->
    <form method="POST" class="mt-4">
        <input type="hidden" name="report_type" value="exam_schedule"> <!-- Hidden input for report type -->
        <div class="mb-3">
            <button type="submit" name="download_csv" class="btn btn-success">Download CSV (Excel)</button>
        </div>
    </form>
</div>
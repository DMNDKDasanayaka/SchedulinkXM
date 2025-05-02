<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/db_connect.php';

// Handle adding new exam
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $degree = $_POST['degree'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $student_count = $_POST['student_count'];

    $sql = "INSERT INTO exams (subject_code, subject_name, degree, date, start_time, end_time, student_count) 
            VALUES ('$subject_code', '$subject_name', '$degree', '$date', '$start_time', '$end_time', $student_count)";
    
    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>Exam added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch exams
$sql = "SELECT * FROM exams ORDER BY date";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h1>Manage Exams</h1>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="subject_code" class="form-label">Subject Code</label>
            <input type="text" class="form-control" id="subject_code" name="subject_code" required>
        </div>
        <div class="mb-3">
            <label for="subject_name" class="form-label">Subject Name</label>
            <input type="text" class="form-control" id="subject_name" name="subject_name" required>
        </div>
        <div class="mb-3">
            <label for="degree" class="form-label">Degree</label>
            <input type="text" class="form-control" id="degree" name="degree" required>
        </div>
        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>
        <div class="mb-3">
            <label for="start_time" class="form-label">Start Time</label>
            <input type="time" class="form-control" id="start_time" name="start_time" required>
        </div>
        <div class="mb-3">
            <label for="end_time" class="form-label">End Time</label>
            <input type="time" class="form-control" id="end_time" name="end_time" required>
        </div>
        <div class="mb-3">
            <label for="student_count" class="form-label">Student Count</label>
            <input type="number" class="form-control" id="student_count" name="student_count" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Exam</button>
    </form>

    <h2 class="mt-4">Current Exams</h2>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Degree</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Student Count</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['subject_code'] ?></td>
                    <td><?= $row['subject_name'] ?></td>
                    <td><?= $row['degree'] ?></td>
                    <td><?= $row['date'] ?></td>
                    <td><?= $row['start_time'] ?></td>
                    <td><?= $row['end_time'] ?></td>
                    <td><?= $row['student_count'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

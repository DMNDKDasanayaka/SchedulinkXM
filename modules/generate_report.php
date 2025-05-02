<?php
include '../includes/db_connect.php';

// Generate a simple report of exams and their allocations
$sql = "SELECT e.exam_id, e.subject_name, e.date, e.start_time, e.end_time, eh.hall_name, l.name AS supervisor 
        FROM exams e
        JOIN allocations a ON e.exam_id = a.exam_id
        JOIN exam_halls eh ON a.hall_id = eh.hall_id
        JOIN lecturers l ON a.supervisor_id = l.lecturer_id
        ORDER BY e.date, e.start_time";
$result = mysqli_query($conn, $sql);

echo "<h2>Exam Schedule Report</h2>";
echo "<table border='1'>
        <tr>
            <th>Exam ID</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Hall</th>
            <th>Supervisor</th>
        </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>" . $row['exam_id'] . "</td>
            <td>" . $row['subject_name'] . "</td>
            <td>" . $row['date'] . "</td>
            <td>" . $row['start_time'] . "</td>
            <td>" . $row['end_time'] . "</td>
            <td>" . $row['hall_name'] . "</td>
            <td>" . $row['supervisor'] . "</td>
          </tr>";
}

echo "</table>";
?>

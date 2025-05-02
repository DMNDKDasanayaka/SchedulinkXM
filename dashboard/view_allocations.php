<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/db_connect.php';

// Fetch allocations
$sql = "SELECT a.allocation_id, e.subject_name, eh.hall_name, l.name AS supervisor_name 
        FROM allocations a
        JOIN exams e ON a.exam_id = e.exam_id
        JOIN exam_halls eh ON a.hall_id = eh.hall_id
        JOIN lecturers l ON a.supervisor_id = l.lecturer_id";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h1>View Allocations</h1>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>Allocation ID</th>
                <th>Subject Name</th>
                <th>Hall Name</th>
                <th>Supervisor Name</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['allocation_id'] ?></td>
                    <td><?= $row['subject_name'] ?></td>
                    <td><?= $row['hall_name'] ?></td>
                    <td><?= $row['supervisor_name'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

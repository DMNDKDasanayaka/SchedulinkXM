<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/db_connect.php';

// Handle adding new lecturer
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $rank_level = $_POST['rank_level'];
    $availability = $_POST['availability'];

    $sql = "INSERT INTO lecturers (name, designation, department, rank_level, availability) 
            VALUES ('$name', '$designation', '$department', $rank_level, '$availability')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>Lecturer added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch lecturers
$sql = "SELECT * FROM lecturers ORDER BY name";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h1>Manage Lecturers</h1>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="name" class="form-label">Lecturer Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="designation" class="form-label">Designation</label>
            <input type="text" class="form-control" id="designation" name="designation" required>
        </div>
        <div class="mb-3">
            <label for="department" class="form-label">Department</label>
            <input type="text" class="form-control" id="department" name="department" required>
        </div>
        <div class="mb-3">
            <label for="rank_level" class="form-label">Rank Level</label>
            <input type="number" class="form-control" id="rank_level" name="rank_level" required>
        </div>
        <div class="mb-3">
            <label for="availability" class="form-label">Availability</label>
            <textarea class="form-control" id="availability" name="availability" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Lecturer</button>
    </form>

    <h2 class="mt-4">Current Lecturers</h2>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>Name</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Rank Level</th>
                <th>Availability</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['designation'] ?></td>
                    <td><?= $row['department'] ?></td>
                    <td><?= $row['rank_level'] ?></td>
                    <td><?= $row['availability'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

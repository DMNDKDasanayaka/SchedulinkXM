<?php
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/db_connect.php';

// Handle adding new hall
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hall_name = $_POST['hall_name'];
    $capacity = $_POST['capacity'];
    $location = $_POST['location'];

    $sql = "INSERT INTO exam_halls (hall_name, capacity, location) 
            VALUES ('$hall_name', $capacity, '$location')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<div class='alert alert-success'>Hall added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}

// Fetch halls
$sql = "SELECT * FROM exam_halls ORDER BY hall_name";
$result = mysqli_query($conn, $sql);
?>

<div class="container mt-4">
    <h1>Manage Halls</h1>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label for="hall_name" class="form-label">Hall Name</label>
            <input type="text" class="form-control" id="hall_name" name="hall_name" required>
        </div>
        <div class="mb-3">
            <label for="capacity" class="form-label">Capacity</label>
            <input type="number" class="form-control" id="capacity" name="capacity" required>
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" class="form-control" id="location" name="location" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Hall</button>
    </form>

    <h2 class="mt-4">Current Halls</h2>
    <table class="table mt-3">
        <thead>
            <tr>
                <th>Hall Name</th>
                <th>Capacity</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['hall_name'] ?></td>
                    <td><?= $row['capacity'] ?></td>
                    <td><?= $row['location'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

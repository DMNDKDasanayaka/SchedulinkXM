<?php
include '../includes/db_connect.php';

// Add lecturer
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $rank_level = $_POST['rank_level'];
    $availability = $_POST['availability'];

    $sql = "INSERT INTO lecturers (name, designation, department, rank_level, availability) 
            VALUES ('$name', '$designation', '$department', $rank_level, '$availability')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Lecturer added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch lecturers
$sql = "SELECT * FROM lecturers";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo "Lecturer ID: " . $row['lecturer_id'] . "<br>";
    echo "Name: " . $row['name'] . "<br>";
    echo "Designation: " . $row['designation'] . "<br>";
    echo "Department: " . $row['department'] . "<br>";
    echo "<hr>";
}
?>

<?php
include '../includes/db_connect.php';

// Add hall
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hall_name = $_POST['hall_name'];
    $capacity = $_POST['capacity'];
    $location = $_POST['location'];

    $sql = "INSERT INTO exam_halls (hall_name, capacity, location) 
            VALUES ('$hall_name', $capacity, '$location')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Hall added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch halls
$sql = "SELECT * FROM exam_halls";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo "Hall ID: " . $row['hall_id'] . "<br>";
    echo "Hall Name: " . $row['hall_name'] . "<br>";
    echo "Capacity: " . $row['capacity'] . "<br>";
    echo "Location: " . $row['location'] . "<br>";
    echo "<hr>";
}
?>

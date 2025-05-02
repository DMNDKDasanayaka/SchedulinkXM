<?php
$host = 'localhost';           // Your MySQL host, usually localhost
$db = 'SchedulinkXM';          // Corrected database name
$user = 'root';                // Your MySQL username (default is 'root' for XAMPP)
$pass = '';                    // Your MySQL password (default is empty for XAMPP)

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
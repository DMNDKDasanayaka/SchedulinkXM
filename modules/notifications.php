<?php
include '../includes/db_connect.php';

// Send notification (example for email)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_id = $_POST['recipient_id'];
    $message = $_POST['message'];

    // For this example, assume the notification type is always email
    $sql = "INSERT INTO notifications (recipient_id, type, message) 
            VALUES ($recipient_id, 'email', '$message')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Notification sent successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Fetch notifications
$sql = "SELECT * FROM notifications";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo "Notification ID: " . $row['notification_id'] . "<br>";
    echo "Recipient ID: " . $row['recipient_id'] . "<br>";
    echo "Message: " . $row['message'] . "<br>";
    echo "Status: " . $row['status'] . "<br>";
    echo "<hr>";
}
?>

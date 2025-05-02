<?php
// filepath: c:\xampp\htdocs\SchedulinkXM\SchedulinkXM\app\handlers\login.php

session_start(); // Start the session

require_once '../../config/database.php'; // Include database connection
require_once '../models/UserModel.php'; // Include UserModel

// Create a new PDO instance (assuming database.php initializes $pdo)
$userModel = new UserModel($pdo);

// Get the username and password from the POST request
$username = $_POST['username'];
$password = $_POST['password'];

// Debugging line (remove in production)
echo "Username: $username, Password: $password";

// Authenticate the user
$user = $userModel->authenticate($username, $password);

if ($user) {
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Redirect to dashboard or home page
    header("Location: /dashboard");
    exit;
} else {
    // Invalid credentials
    $_SESSION['error'] = "Invalid username or password.";
    header("Location: /login");
    exit;
}
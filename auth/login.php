<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            header("Location: ../dashboard/index.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that username.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - YourSystem</title>
  <link rel="stylesheet" href="login.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="logo">
        <!-- Replace with your logo -->
        <h2>YourSystem</h2>
      </div>
      <h3>Sign in to your account</h3>
      <form action="login.php" method="POST">
        <div class="input-group">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" required>
        </div>
        <div class="input-group">
          <label for="password">
            Password
            <a href="#" class="forgot">Forgot?</a>
          </label>
          <input type="password" name="password" id="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
      </form>
      <p class="footer-text">Don't have an account? <a href="../auth/register.php" class="text-primary">Sign up</a></small>
    </div>
  </div>
</body>
</html>

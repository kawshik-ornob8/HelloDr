<?php
// admin_signup.php

// Include the database connection
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert data into the admins table
    $sql = "INSERT INTO admins (full_name, username, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $full_name, $username, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin registration successful!'); window.location.href = 'admin_login.php';</script>";
    } else {
        echo "<script>alert('Error: Could not register admin.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <link rel="stylesheet" href="css/admin_signup.css">
</head>
<body>
    <div class="signup-container">
        <h2>Admin Signup</h2>
        <form action="admin_signup.php" method="POST">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an admin account? <a href="admin_login.php">Login here</a>.</p>
    </div>
</body>
</html>

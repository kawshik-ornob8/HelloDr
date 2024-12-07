<?php
session_start();
include('../config.php'); // Include the database configuration file

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare SQL query to find admin
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if an admin with the username exists
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin_dashboard"); // Redirect to the admin dashboard
            exit;
        } else {
            $error = "Invalid password. Please try again.";
        }
    } else {
        $error = "Invalid username. Please try again.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="css/admin_login.css">
    <link rel="icon" type="image/x-icon" href="../images/favicon.png">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if (isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>
        <form action="admin_login" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <p><a href="../index">Return to Home</a></p>
    </div>
</body>
</html>

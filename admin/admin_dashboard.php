<?php
session_start();
include('../config.php'); // Include the database configuration file

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch admin details from the database
$admin_id = $_SESSION['admin_id'];
$query = "SELECT full_name, email FROM admins WHERE admin_id = $admin_id";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    $admin_name = $admin['full_name'];
    $admin_email = $admin['email'];
} else {
    // Handle error if admin details are not found
    $admin_name = "Admin";
    $admin_email = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container {
            text-align: center;
            margin-top: 50px;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome to Admin Dashboard</h2>
        
        <!-- Display Admin Full Name and Email -->
        <p><strong>Admin Name:</strong> <?php echo htmlspecialchars($admin_name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_email); ?></p>

        <a href="../logout.php" class="button">Logout</a>
        <p>Choose an option below:</p>

        <!-- Links to Team and Patient Management pages -->
        <a href="team.php" class="button">Update Team Members</a>
        <a href="manage_patients.php" class="button">Manage Patients</a>
    </div>
</body>
</html>

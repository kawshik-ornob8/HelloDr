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
<link rel="icon" type="image/x-icon" href="../images/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .dashboard-container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            margin: 10px 0;
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
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</h2>

        <!-- Display Admin Details -->
        <p><strong>Admin Name:</strong> <?php echo htmlspecialchars($admin_name); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_email); ?></p>

        <!-- Navigation Buttons -->
        <p>Choose an option below:</p>
        <a href="manage_patients.php" class="button">Manage Patients</a>
        <a href="manage_doctors.php" class="button">Manage Doctors</a>
        <a href="team.php" class="button">Update Team Members</a>
        <a href="admin_signup.php" class="button">Add New Admin</a>
        <a href="../logout.php" class="button">Logout</a>
    </div>
</body>
</html>

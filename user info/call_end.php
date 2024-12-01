<?php
session_start();
require '../config.php';

// Check if user is logged in as doctor or patient
if (!isset($_SESSION['doctor_id']) && !isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize doctor name to 'Unknown'
$doctorName = "Unknown";

// Fetch the doctor's name if doctor_id is provided
if (isset($_GET['doctor_id'])) {
    $doctorID = intval($_GET['doctor_id']);
    
    // Fetch doctor's full name from database
    $stmt = $conn->prepare("SELECT full_name FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("i", $doctorID);
    $stmt->execute();
    $stmt->bind_result($doctorName);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Ended</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .message-box h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }
        .message-box p {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        .message-box a {
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }
        .message-box a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h1>Call Ended</h1>
        <p>The video call with Dr. <strong><?php echo htmlspecialchars($doctorName, ENT_QUOTES, 'UTF-8'); ?></strong> has been ended.</p>
        <p>Thank you for using our service.</p>
        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>
</html>

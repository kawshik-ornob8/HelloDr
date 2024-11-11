<?php
session_start();
include('../config.php');

// Check if doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit;
}

// Get the appointment ID
if (isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    // Prepare the SQL update query to change the status to "Approved"
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Approved' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        // If successful, redirect to the appointments page
        header("Location: view_appointments.php");
        exit;
    } else {
        echo "Error updating appointment status: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<?php
session_start();
include('../config.php');

// Check if doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login");
    exit;
}

// Check if appointment_id is passed through POST request
if (isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    // Prepare the SQL query to update the status to 'Cancelled'
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        // If the update is successful, redirect to the appointments page
        header("Location: view_appointments");
        exit;
    } else {
        // If there's an error with the database query
        echo "Error updating appointment status: " . $stmt->error;
    }

    $stmt->close();
} else {
    // If appointment_id is not provided, redirect back to appointments page
    header("Location: view_appointments");
    exit;
}

$conn->close();
?>

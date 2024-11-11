<?php
session_start();
include('../config.php');

// Ensure user is logged in as doctor
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit;
}

// Validate POST data
$appointment_id = $_POST['appointment_id'] ?? null;
$action = $_POST['action'] ?? null;

if ($appointment_id && $action) {
    if ($action === 'approve') {
        // Update the appointment status to 'approved'
        $stmt = $conn->prepare("UPDATE appointments SET status = 'approved' WHERE appointment_id = ?");
    } elseif ($action === 'delete') {
        // Update the appointment status to 'deleted'
        $stmt = $conn->prepare("UPDATE appointments SET status = 'deleted' WHERE appointment_id = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to view_appointments.php
header("Location: view_appointments.php");
exit();
?>

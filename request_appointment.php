<?php
session_start();
include('config.php');

// Ensure the patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: user/user_login");
    exit();
}

$patient_id = $_SESSION['patient_id'];

// Retrieve and validate form data
$doctor_id = $_POST['doctor_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$appointment_time = $_POST['appointment_time'] ?? null;

if (!$doctor_id || !$appointment_date || !$appointment_time) {
    die("All fields are required.");
}

// Insert the appointment into the database
$query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status)
          VALUES (?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("Database error: " . $conn->error);
    die("Failed to prepare the statement.");
}

$stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);

if ($stmt->execute()) {
    // Redirect to email notification script
    header("Location: request_appointment_action?doctor_id=$doctor_id&appointment_date=$appointment_date&appointment_time=$appointment_time");
    exit();
} else {
    error_log("Failed to insert appointment: " . $stmt->error);
    die("Failed to request the appointment. Please try again later.");
}

$stmt->close();
$conn->close();
?>

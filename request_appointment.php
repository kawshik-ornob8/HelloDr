<?php
session_start();
include('config.php');

// Ensure patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: user_login.php");
    exit();
}
$patient_id = $_SESSION['patient_id'];

// Retrieve data from the form
$doctor_id = $_POST['doctor_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$appointment_time = $_POST['appointment_time'] ?? null;

// Validate input
if (!$doctor_id || !$appointment_date || !$appointment_time) {
    die("All fields are required.");
}

// Prepare and execute the insert statement
$query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing the query: " . $conn->error);
}

$stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);

if ($stmt->execute()) {
    // Redirect to a confirmation page
    header("Location: appointment_confirmation.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

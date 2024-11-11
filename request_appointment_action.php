<?php
session_start();
include('config.php');

// Check if the user is logged in (patient)
if (!isset($_SESSION['patient_id'])) {
    // Save the current page URL to the session before redirecting
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header("Location: user%20info/user_login.php"); // Redirect to login page if user is not logged in
    exit();
}

// Get patient ID from session
$patient_id = $_SESSION['patient_id'];

// Get data from POST request
$doctor_id = $_POST['doctor_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$appointment_time = $_POST['appointment_time'] ?? null;

// Validate inputs
if (!$doctor_id || !$appointment_date || !$appointment_time) {
    die("All fields are required.");
}

// Prepare the SQL query to insert appointment
$query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);

// Check if the query was prepared successfully
if ($stmt === false) {
    die("Error preparing the query: " . $conn->error);
}

// Bind parameters and execute the query
$stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);
$stmt->execute();

// Check if the appointment was successfully requested
if ($stmt->affected_rows > 0) {
    echo "Appointment requested successfully!";
} else {
    echo "Failed to request appointment. Please try again.";
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

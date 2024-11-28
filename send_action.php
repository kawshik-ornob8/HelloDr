<?php
session_start();
include('config.php');

// Get the form data
$doctor_id = $_POST['doctor_id'];
$patient_id = $_POST['patient_id'];
$message = $_POST['message'];
$sender = $_POST['sender']; // 1 for patient, 0 for doctor

// Insert the new message into the database
$sql = "INSERT INTO messages (doctor_id, patient_id, message, sender) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisi", $doctor_id, $patient_id, $message, $sender);

if ($stmt->execute()) {
    // Redirect to conversation page on success
    header("Location: conversation.php?doctor_id=$doctor_id");
    exit(); // Ensure the script stops after redirection
} else {
    // Handle error, optionally redirect to an error page or show a message
    header("Location: error_page.php?error=Message sending failed");
    exit();
}

$stmt->close();
?>

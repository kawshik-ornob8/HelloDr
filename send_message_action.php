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
    // Message successfully inserted
    echo json_encode(['success' => true]);
} else {
    // Error inserting message
    echo json_encode(['success' => false]);
}

$stmt->close();
?>

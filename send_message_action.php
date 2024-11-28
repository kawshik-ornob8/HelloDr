<?php
include('config.php');
session_start();

if (!isset($_POST['message']) || !isset($_POST['doctor_id']) || !isset($_POST['patient_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$message = $_POST['message'];
$doctor_id = intval($_POST['doctor_id']);
$patient_id = intval($_POST['patient_id']);
$sender = intval($_POST['sender']);

$sql = "INSERT INTO messages (doctor_id, patient_id, sender, message, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiis", $doctor_id, $patient_id, $sender, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
?>

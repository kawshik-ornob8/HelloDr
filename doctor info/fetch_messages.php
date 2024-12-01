<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([]);
    exit;
}

// Get doctor ID
$doctor_id = $_SESSION['doctor_id'];

// Get patient ID from request
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Fetch messages for the doctor and patient
$sql = "
    SELECT m.message_id, m.message, m.created_at, m.sender, m.is_read, m.image_path
    FROM messages m
    WHERE m.doctor_id = ? AND m.patient_id = ?
    ORDER BY m.created_at ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $doctor_id, $patient_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>

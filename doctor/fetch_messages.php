<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode([]);
    exit;
}

// Get doctor ID
$doctor_id = intval($_SESSION['doctor_id']);

// Get patient ID from request
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

// Validate patient ID
if ($patient_id <= 0) {
    echo json_encode(['error' => 'Invalid patient ID.']);
    exit;
}

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

// Mark all unread messages from the patient as read
$update_sql = "
    UPDATE messages
    SET is_read = 1
    WHERE doctor_id = ? AND patient_id = ? AND sender = 1 AND is_read = 0
";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $doctor_id, $patient_id);
$update_stmt->execute();

echo json_encode($messages);

$stmt->close();
$update_stmt->close();
$conn->close();
?>

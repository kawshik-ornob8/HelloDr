<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    http_response_code(403); // Forbidden access
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Get the logged-in doctor's ID
$doctor_id = intval($_SESSION['doctor_id']);

// Check if patient_id is provided in the request
if (!isset($_GET['patient_id'])) {
    http_response_code(400); // Bad request
    echo json_encode(["error" => "Patient ID is required"]);
    exit;
}

$patient_id = intval($_GET['patient_id']);

// Fetch messages for the selected patient and logged-in doctor
$message_sql = "
    SELECT message, created_at, is_read
    FROM messages
    WHERE doctor_id = ? AND patient_id = ?
    ORDER BY created_at ASC
";
$message_stmt = $conn->prepare($message_sql);
$message_stmt->bind_param("ii", $doctor_id, $patient_id);
$message_stmt->execute();
$message_result = $message_stmt->get_result();

$messages = [];
while ($row = $message_result->fetch_assoc()) {
    $messages[] = [
        'message' => htmlspecialchars($row['message']),
        'created_at' => $row['created_at'],
        'is_read' => (bool)$row['is_read']
    ];
}

// Mark all unread messages as read after fetching
$update_sql = "
    UPDATE messages
    SET is_read = 1
    WHERE doctor_id = ? AND patient_id = ? AND is_read = 0
";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $doctor_id, $patient_id);
$update_stmt->execute();

header('Content-Type: application/json');
echo json_encode($messages);
?>

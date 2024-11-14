<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Get doctor ID and input data
$doctor_id = intval($_SESSION['doctor_id']);
$data = json_decode(file_get_contents("php://input"), true);

// Ensure patient_id, message, and sender are set
if (!isset($data['patient_id']) || !isset($data['message']) || !isset($data['sender'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid input"]);
    exit;
}

$patient_id = intval($data['patient_id']);
$message = htmlspecialchars($data['message']);
$sender = intval($data['sender']); // 0 for doctor, 1 for patient

// Insert the message into the messages table
$insert_sql = "
    INSERT INTO messages (doctor_id, patient_id, message, sender, created_at, is_read)
    VALUES (?, ?, ?, ?, NOW(), 0)
";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iisi", $doctor_id, $patient_id, $message, $sender);

if ($insert_stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to send message"]);
}
?>

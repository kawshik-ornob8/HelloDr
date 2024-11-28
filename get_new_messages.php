<?php
include('config.php');
session_start();

if (!isset($_SESSION['patient_id'])) {
    echo json_encode([]);
    exit();
}

$patient_id = $_SESSION['patient_id'];
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$last_timestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : '1970-01-01 00:00:00';

if ($doctor_id <= 0) {
    echo json_encode([]);
    exit();
}

// Fetch new messages after the last timestamp
$sql = "SELECT m.message, m.created_at, m.sender 
        FROM messages m
        WHERE m.doctor_id = ? AND m.patient_id = ? AND m.created_at > ?
        ORDER BY m.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $doctor_id, $patient_id, $last_timestamp);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
echo json_encode($messages);
?>

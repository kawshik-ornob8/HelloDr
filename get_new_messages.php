<?php
include('config.php');

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$patient_id = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;
$last_timestamp = isset($_GET['last_timestamp']) ? $_GET['last_timestamp'] : '1970-01-01 00:00:00';

$sql = "SELECT message, created_at, sender, image_path
        FROM messages
        WHERE doctor_id = ? AND patient_id = ? AND created_at > ?
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $doctor_id, $patient_id, $last_timestamp);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>

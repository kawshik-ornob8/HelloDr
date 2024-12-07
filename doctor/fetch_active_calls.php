<?php
session_start();
include('../config.php');

if (!isset($_SESSION['doctor_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch active video calls for the logged-in doctor
$stmt = $conn->prepare("
    SELECT vr.room_id, vr.created_at, vr.patient_id, vr.doctor_id, p.full_name AS patient_name
    FROM video_rooms vr
    JOIN patients p ON vr.patient_id = p.patient_id
    WHERE vr.doctor_id = ? AND vr.status = 'active'
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$calls = [];
while ($row = $result->fetch_assoc()) {
    $calls[] = $row;
}

$stmt->close();
echo json_encode($calls);
?>

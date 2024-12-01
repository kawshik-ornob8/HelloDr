<?php
session_start();
include('../config.php');

if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Initialize response data
$data = [
    'total_appointments' => 0,
    'unread_messages' => 0,
    'active_calls' => [],
];

// Today's Total Appointments
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) AS total_appointments FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$data['total_appointments'] = $stmt->get_result()->fetch_assoc()['total_appointments'] ?? 0;
$stmt->close();

// Unread Messages
$stmt = $conn->prepare("SELECT COUNT(*) AS unread_messages FROM messages WHERE doctor_id = ? AND is_read = 0");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$data['unread_messages'] = $stmt->get_result()->fetch_assoc()['unread_messages'] ?? 0;
$stmt->close();

// Active Calls
$stmt = $conn->prepare("
    SELECT vr.room_id, p.full_name AS patient_name
    FROM video_rooms vr
    JOIN patients p ON vr.patient_id = p.patient_id
    WHERE vr.doctor_id = ? AND vr.status = 'active'
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $data['active_calls'][] = $row;
}
$stmt->close();

echo json_encode($data);
?>

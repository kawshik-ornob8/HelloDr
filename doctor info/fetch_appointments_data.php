<?php
session_start();
include('../config.php');

// Redirect to login if not logged in as a doctor
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Initialize response data
$data = [
    'appointments' => [],
    'active_calls' => []
];

// Fetch appointments for the logged-in doctor where the date is not in the past
$stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, p.full_name, p.mobile_number, p.email, a.status
                        FROM appointments a
                        JOIN patients p ON a.patient_id = p.patient_id
                        WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
                        ORDER BY a.appointment_date, a.appointment_time");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$appointments_result = $stmt->get_result();
while ($row = $appointments_result->fetch_assoc()) {
    $data['appointments'][] = $row;
}
$stmt->close();

// Fetch active video calls for the doctor
$stmt = $conn->prepare("
    SELECT vr.room_id, p.full_name AS patient_name
    FROM video_rooms vr
    JOIN patients p ON vr.patient_id = p.patient_id
    WHERE vr.doctor_id = ? AND vr.status = 'active'
");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$active_calls_result = $stmt->get_result();
while ($row = $active_calls_result->fetch_assoc()) {
    $data['active_calls'][] = $row;
}
$stmt->close();

echo json_encode($data);
?>

<?php
session_start();
include('../config.php');

// Ensure the session is active and the user is logged in as a doctor
if (!isset($_SESSION['doctor_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$last_fetched_time = isset($_GET['last_fetched_time']) ? $_GET['last_fetched_time'] : null;

try {
    $data = [
        'active_calls' => [],
        'pending_appointments' => []
    ];

    // Fetch active video calls
    $stmt = $conn->prepare("
        SELECT vr.room_id, p.full_name AS patient_name
        FROM video_rooms vr
        JOIN patients p ON vr.patient_id = p.patient_id
        WHERE vr.doctor_id = ? AND vr.status = 'active'
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $active_calls_result = $stmt->get_result();
    $data['active_calls'] = $active_calls_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Fetch pending appointments
    $stmt = $conn->prepare("
        SELECT a.appointment_id, a.appointment_date, a.appointment_time, p.full_name, p.mobile_number, p.email
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.doctor_id = ? AND a.status = 'Pending'
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $pending_appointments_result = $stmt->get_result();
    $data['pending_appointments'] = $pending_appointments_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500); // Internal server error
    echo json_encode(['error' => 'An unexpected error occurred', 'details' => $e->getMessage()]);
}
?>

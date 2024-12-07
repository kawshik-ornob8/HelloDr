<?php
session_start();
include('../config.php');

// Ensure the session is active and the user is logged in as a doctor
if (!isset($_SESSION['doctor_id'])) {
    http_response_code(401); // Set HTTP status code for unauthorized access
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$last_fetched_time = isset($_GET['last_fetched_time']) ? $_GET['last_fetched_time'] : null;

try {
    // Initialize the response data
    $data = [
        'appointments' => [],
        'active_calls' => [],
        'new_appointments' => []
    ];

    // Fetch upcoming appointments for the logged-in doctor
    $stmt = $conn->prepare("
        SELECT 
            a.appointment_id, 
            a.appointment_date, 
            a.appointment_time, 
            p.full_name, 
            p.mobile_number, 
            p.email, 
            a.status
        FROM appointments a
        JOIN patients p ON a.patient_id = p.patient_id
        WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $appointments_result = $stmt->get_result();
    $data['appointments'] = $appointments_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Check for new appointments if `last_fetched_time` is provided
    if ($last_fetched_time) {
        $stmt = $conn->prepare("
            SELECT 
                a.appointment_id, 
                a.appointment_date, 
                a.appointment_time, 
                p.full_name, 
                p.mobile_number, 
                p.email, 
                a.status
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            WHERE a.doctor_id = ? AND a.created_at > ?
        ");
        $stmt->bind_param("is", $doctor_id, $last_fetched_time);
        $stmt->execute();
        $new_appointments_result = $stmt->get_result();
        $data['new_appointments'] = $new_appointments_result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    // Fetch active video calls for the logged-in doctor
    $stmt = $conn->prepare("
        SELECT 
            vr.room_id, 
            p.full_name AS patient_name
        FROM video_rooms vr
        JOIN patients p ON vr.patient_id = p.patient_id
        WHERE vr.doctor_id = ? AND vr.status = 'active'
    ");
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $active_calls_result = $stmt->get_result();
    $data['active_calls'] = $active_calls_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Send the response data as JSON
    echo json_encode($data);

} catch (Exception $e) {
    // Handle unexpected errors gracefully
    http_response_code(500); // Set HTTP status code for internal server error
    echo json_encode(['error' => 'An unexpected error occurred', 'details' => $e->getMessage()]);
}
?>

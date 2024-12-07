<?php
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['appointment_time'], $_POST['doctor_id'], $_POST['date'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit;
    }

    $appointment_time = $_POST['appointment_time'];
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];

    $query = "UPDATE appointments SET status = 'Ended' WHERE doctor_id = ? AND appointment_time = ? AND appointment_date = ? AND status = 'Approved'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $doctor_id, $appointment_time, $date);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment marked as ended']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update appointment']);
    }

    $stmt->close();
    $conn->close();
}
?>

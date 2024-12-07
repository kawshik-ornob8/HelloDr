<?php
include_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = $data['appointment_id'];

    // Validate the appointment_id
    if (!$appointment_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid appointment ID.']);
        exit;
    }

    // Update the appointment status
    $update_query = "UPDATE appointments SET status = 'Ended' WHERE appointment_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $appointment_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the appointment.']);
    }

    $stmt->close();
    $conn->close();
}
?>

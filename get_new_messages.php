<?php
include('config.php');

// Get doctor_id from the query string
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$patient_id = isset($_SESSION['patient_id']) ? $_SESSION['patient_id'] : 0;

if ($doctor_id > 0 && $patient_id > 0) {
    // Fetch new messages
    $sql = "SELECT m.message, m.created_at, m.is_read, m.sender
            FROM messages m
            WHERE (m.patient_id = ? AND m.doctor_id = ?)
            OR (m.patient_id = ? AND m.doctor_id = ?)
            ORDER BY m.created_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $patient_id, $doctor_id, $patient_id, $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($message = $result->fetch_assoc()) {
        $messages[] = $message;
    }

    echo json_encode($messages);
} else {
    echo json_encode([]);
}

$stmt->close();
?>

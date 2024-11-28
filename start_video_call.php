<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

if (isset($_POST['doctor_id'], $_POST['room_id'])) {
    $doctor_id = intval($_POST['doctor_id']);
    $room_id = htmlspecialchars($_POST['room_id'], ENT_QUOTES, 'UTF-8');

    if (isset($_SESSION['patient_id'])) {
        $patient_id = intval($_SESSION['patient_id']);
    } else {
        header("Location: user%20info/user_login.php");
        exit();
    }

    // Clean up existing calls for the same doctor and patient
    $cleanup_sql = "DELETE FROM active_calls WHERE doctor_id = ? AND patient_id = ?";
    $cleanup_stmt = $conn->prepare($cleanup_sql);
    $cleanup_stmt->bind_param("ii", $doctor_id, $patient_id);
    $cleanup_stmt->execute();
    $cleanup_stmt->close();

    // Insert new call
    $sql = "INSERT INTO active_calls (room_id, doctor_id, patient_id, start_time) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sii", $room_id, $doctor_id, $patient_id);
        if ($stmt->execute()) {
            header("Location: video_call_room.php?room_id=" . urlencode($room_id));
            exit();
        } else {
            error_log("Database error: " . $stmt->error);
            header("Location: error_page.php?error=Database error");
            exit();
        }
        $stmt->close();
    } else {
        error_log("Database preparation error: " . $conn->error);
        header("Location: error_page.php?error=Query preparation failed");
        exit();
    }
} else {
    header("Location: error_page.php?error=Invalid request data");
    exit();
}
?>

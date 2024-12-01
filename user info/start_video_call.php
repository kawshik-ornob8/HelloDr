<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';

if (isset($_POST['doctor_id'])) {
    $doctor_id = intval($_POST['doctor_id']);
    
    // Generate a unique 12-character room_id
    $room_id = bin2hex(random_bytes(6)); // Generates a 12-character random ID

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

    // Insert new call into active_calls table
    $sql = "INSERT INTO active_calls (room_id, doctor_id, patient_id, start_time) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("sii", $room_id, $doctor_id, $patient_id);
        if ($stmt->execute()) {
            // Update video_rooms table
            $video_room_sql = "
                INSERT INTO video_rooms (room_id, doctor_id, patient_id, status, created_at) 
                VALUES (?, ?, ?, 'active', NOW())
                ON DUPLICATE KEY UPDATE 
                    status = 'active', 
                    created_at = NOW()
            ";
            $video_room_stmt = $conn->prepare($video_room_sql);
            if ($video_room_stmt) {
                $video_room_stmt->bind_param("sii", $room_id, $doctor_id, $patient_id);
                if ($video_room_stmt->execute()) {
                    // Send doctor_id along with room_id in the URL
                    header("Location: video_call_room.php?room_id=" . urlencode($room_id) . "&doctor_id=" . urlencode($doctor_id));
                    exit();
                } else {
                    error_log("Video rooms database error: " . $video_room_stmt->error);
                    header("Location: error_page.php?error=Database error in video_rooms");
                    exit();
                }
                $video_room_stmt->close();
            } else {
                error_log("Video rooms preparation error: " . $conn->error);
                header("Location: error_page.php?error=Query preparation failed for video_rooms");
                exit();
            }
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

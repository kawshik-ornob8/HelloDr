<?php
require '../config.php';

if (isset($_GET['room_id'])) {
    $room_id = htmlspecialchars($_GET['room_id'], ENT_QUOTES, 'UTF-8');

    $stmt = $conn->prepare("SELECT status FROM video_rooms WHERE room_id = ?");
    $stmt->bind_param("s", $room_id);
    $stmt->execute();
    $stmt->bind_result($status);
    $stmt->fetch();
    $stmt->close();

    if (!empty($status)) {
        echo json_encode(['status' => $status]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
} else {
    echo json_encode(['status' => 'error']);
}
?>

<?php
session_start();
require '../config.php';

// Log the request method for debugging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Handle POST or GET requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['room_id'])) {
        $roomID = $data['room_id'];
    } else {
        error_log("Room ID missing in POST request.");
        echo json_encode(['status' => 'error', 'message' => 'Room ID is required in POST request.']);
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['room_id'])) {
        $roomID = htmlspecialchars($_GET['room_id'], ENT_QUOTES, 'UTF-8');
    } else {
        error_log("Room ID missing in GET request.");
        echo json_encode(['status' => 'error', 'message' => 'Room ID is required in GET request.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Debugging: Log the received room ID
error_log("Received Room ID: " . $roomID);

// Check if the room exists before updating
$stmt = $conn->prepare("SELECT * FROM video_rooms WHERE room_id = ?");
$stmt->bind_param("s", $roomID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update the room status
    $updateStmt = $conn->prepare("UPDATE video_rooms SET status = 'ended' WHERE room_id = ?");
    $updateStmt->bind_param("s", $roomID);

    if ($updateStmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Call ended successfully.']);
    } else {
        error_log("Error updating room status: " . $updateStmt->error); // Log the error
        echo json_encode(['status' => 'error', 'message' => 'Error updating call status.']);
    }
    $updateStmt->close();
} else {
    error_log("Room ID not found: " . $roomID);
    echo json_encode(['status' => 'error', 'message' => 'Room ID not found.']);
}
$stmt->close();
?>

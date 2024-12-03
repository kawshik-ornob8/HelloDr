<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Doctor not logged in.']);
    exit;
}

// Get doctor ID
$doctor_id = intval($_SESSION['doctor_id']);

// Validate POST data
$patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$sender = 0; // Doctor is always the sender in this case

if ($patient_id <= 0 || ($message === '' && !isset($_FILES['image']))) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

// Handle image upload
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($_FILES['image']['tmp_name']);
    
    if (in_array($file_type, $allowed_types)) {
        $upload_dir = "../images/chat/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload image.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid image type.']);
        exit;
    }
}

// Insert message into database
$sql = "INSERT INTO messages (message, created_at, doctor_id, is_read, patient_id, sender, image_path)
        VALUES (?, NOW(), ?, 0, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siiis", $message, $doctor_id, $patient_id, $sender, $image_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}

$stmt->close();
$conn->close();
?>

<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Doctor not logged in.']);
    exit;
}

// Get doctor ID
$doctor_id = $_SESSION['doctor_id'];

// Get posted data
$patient_id = $_POST['patient_id'];
$message = $_POST['message'];
$sender = $_POST['sender'];  // Should be 0 for doctor, 1 for patient

// Handle image upload
$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image_name = basename($_FILES['image']['name']);
    $target_dir = "../images/chat/";
    $target_file = $target_dir . uniqid() . "_" . $image_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = $target_file;  // Save the path to the uploaded image
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

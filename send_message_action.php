<?php
session_start();
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['patient_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$patient_id = $_SESSION['patient_id'];
$doctor_id = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : 0;
$message = isset($_POST['message']) ? $_POST['message'] : '';
$sender = isset($_POST['sender']) ? $_POST['sender'] : 1; // Default sender is patient

// Validate inputs
if ($doctor_id <= 0 || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Handle image upload (if any)
$image_path = '';
if (isset($_FILES['message_image']) && $_FILES['message_image']['error'] == 0) {
    // Debugging: Check if the file is received
    error_log('File uploaded: ' . $_FILES['message_image']['name']);
    
    $image_name = time() . "_" . basename($_FILES['message_image']['name']);
    $target_dir = "doctor%20info/images/chat/";  // Ensure this directory exists and is writable
    $target_file = $target_dir . $image_name;

    // Check if the file is an image
    if (getimagesize($_FILES['message_image']['tmp_name']) !== false) {
        // Debugging: Log image type and size
        error_log('Image type: ' . $_FILES['message_image']['type']);
        error_log('Image size: ' . $_FILES['message_image']['size']);

        // Check for file size limit (optional)
        if ($_FILES['message_image']['size'] > 5000000) {  // Limit file size to 5MB
            echo json_encode(['success' => false, 'message' => 'File size exceeds the limit of 5MB']);
            exit();
        }

        // Try moving the uploaded file
        if (move_uploaded_file($_FILES['message_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        } else {
            error_log('Failed to move uploaded file.');
            echo json_encode(['success' => false, 'message' => 'Failed to upload image. Check directory permissions.']);
            exit();
        }
    } else {
        error_log('Uploaded file is not a valid image.');
        echo json_encode(['success' => false, 'message' => 'Uploaded file is not an image']);
        exit();
    }
} else {
    // Debugging: Check if no image is uploaded
    if (isset($_FILES['message_image'])) {
        error_log('No image uploaded, error code: ' . $_FILES['message_image']['error']);
    }
}

// Insert the message into the database
$sql = "INSERT INTO messages (message, doctor_id, patient_id, sender, image_path) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

// Check for errors in preparing the statement
if (!$stmt) {
    error_log('Database prepare error: ' . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param("siiss", $message, $doctor_id, $patient_id, $sender, $image_path);

// Execute the query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    // If execution fails, display error
    error_log('Database execution error: ' . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to send message. Error: ' . $stmt->error]);
}

$stmt->close();
?>

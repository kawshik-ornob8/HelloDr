<?php
session_start();
include('config.php');

$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session upon login
$doctor_id = $_POST['doctor_id'];
$message = $_POST['message'];

$query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $user_id, $doctor_id, $message);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Message sent successfully!";
} else {
    echo "Failed to send message.";
}
?>

<?php
// Include the PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include('config.php');

$doctor_id = $_POST['doctor_id'];
$appointment_date = $_POST['appointment_date'];
$appointment_time = $_POST['appointment_time'];

// Get doctor information
$query = "SELECT full_name, email FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    echo "Doctor not found.";
    exit();
}

// Email details
$doctor_email = $doctor['email'];
$doctor_name = $doctor['full_name'];

// Email message
$message = "Dear Dr. $doctor_name,

You have a new appointment request from Hello Dr.

Date: $appointment_date
Time: $appointment_time

Please log in to your system to review and confirm the appointment.

Thank you.";

// Email subject

$subject = "New Appointment Request from Hello Dr."; 

// Initialize PHPMailer
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'kawshik15-14750@diu.edu.bd'; // Your Gmail address
    $mail->Password = '212-15-14750-ornob'; // Your Gmail password (consider using an app-specific password if 2FA is enabled)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('kawshik15-14750@diu.edu.bd', 'Appointment System'); // Sender info
    $mail->addAddress($doctor_email, "Dr. $doctor_name"); // Doctor's email address

    // Content
    $mail->isHTML(false); // Set email format to plain text
    $mail->Subject = $subject;
    $mail->Body = $message;

    // Send the email
    if ($mail->send()) {
        echo "Appointment request sent and doctor notified.";
    } else {
        echo "Failed to send email.";
    }
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}

// Redirect to a confirmation page
header("Location: appointment_success.php");
exit();
?>

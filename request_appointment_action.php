<?php
// Include the PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include('config.php');

// Start the session
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Save date and time in cookies for 10 minutes
    setcookie('appointment_date', $appointment_date, time() + 600, "/");
    setcookie('appointment_time', $appointment_time, time() + 600, "/");

// Ensure that the patient is logged in
if (!isset($_SESSION['patient_id'])) {
    // If not logged in, redirect to login page
    header("Location: user info/user_login.php");
    exit();
}

// Ensure required POST data is available
$doctor_id = $_POST['doctor_id'] ?? null;
$appointment_date = $_POST['appointment_date'] ?? null;
$appointment_time = $_POST['appointment_time'] ?? null;

if (!$doctor_id || !$appointment_date || !$appointment_time) {
    die("Missing appointment details.");
}

// Get the patient ID from the session
$patient_id = $_SESSION['patient_id'];

// Prepare and execute the insert query to add the appointment to the database
$query = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing the query: " . $conn->error);
}

$stmt->bind_param("iiss", $patient_id, $doctor_id, $appointment_date, $appointment_time);

if ($stmt->execute()) {
    // Appointment inserted successfully, now send email to doctor
    // Retrieve doctor information from the database
    $query = "SELECT full_name, email FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $doctor = $stmt->get_result()->fetch_assoc();

    if (!$doctor) {
        die("Doctor not found.");
    }

    $doctor_email = $doctor['email'];
    $doctor_name = $doctor['full_name'];

    // Construct email message
    $subject = "New Appointment Request from Hello Dr.";
    $message = <<<EOD
Dear Dr. $doctor_name,

You have a new appointment request from Hello Dr.

Date: $appointment_date
Time: $appointment_time

Please log in to your system to review and confirm the appointment.

Thank you.
EOD;

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    try {
        // SMTP server configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'kawshik15-14750@diu.edu.bd'; // Replace with your Gmail
        $mail->Password = '212-15-14750-ornob';   // Replace with your app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email sender and recipient
        $mail->setFrom('kawshik15-14750@diu.edu.bd', 'Appointment System'); // Replace with your email
        $mail->addAddress($doctor_email, "Dr. $doctor_name");

        // Email content
        $mail->isHTML(false); // Plain text email
        $mail->Subject = $subject;
        $mail->Body = $message;

        // Send the email
        if ($mail->send()) {
            error_log("Appointment email successfully sent to Dr. $doctor_name.");
        } else {
            error_log("Failed to send email to Dr. $doctor_name.");
        }
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $mail->ErrorInfo);
        die("Failed to send email. Please try again later.");
    }

    // Redirect to a confirmation page
    header("Location: appointment_success.php");
    exit();
}
} else {
    // Error inserting appointment into database
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

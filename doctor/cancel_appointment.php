<?php
session_start();
include('../config.php');

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login");
    exit;
}

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Process the appointment cancellation
if (isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    // Fetch the appointment and patient details
    $stmt = $conn->prepare("SELECT p.email, p.full_name, a.appointment_date, a.appointment_time 
                            FROM appointments a
                            JOIN patients p ON a.patient_id = p.patient_id
                            WHERE a.appointment_id = ?");
    $stmt->bind_param("i", $appointment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();
        $to = $appointment['email'];
        $full_name = $appointment['full_name'];
        $appointment_date = $appointment['appointment_date'];
        $appointment_time = $appointment['appointment_time'];

        // Update the appointment status to "Cancelled"
        $update_stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
        $update_stmt->bind_param("i", $appointment_id);

        if ($update_stmt->execute()) {
            // Prepare email notification
            $subject = "Appointment Cancelled";
            $message = "Dear " . htmlspecialchars($full_name) . ",\n\n" .
                "We regret to inform you that your appointment has been cancelled.\n" .
                "Date: " . htmlspecialchars($appointment_date) . "\n" .
                "Time: " . htmlspecialchars($appointment_time) . "\n\n" .
                "If you have any questions or need further assistance, please contact us.\n\n" .
                "We apologize for any inconvenience caused.\n\nBest regards,\nHello Dr.";

            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom($config['smtp_username'], 'Hello Dr.');
                $mail->addAddress($to);

                $mail->Subject = $subject;
                $mail->Body = $message;

                $mail->send();
                $_SESSION['success_message'] = "Appointment cancelled, and email sent successfully.";
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Appointment cancelled, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION['error_message'] = "Failed to cancel the appointment.";
        }

        $update_stmt->close();
    } else {
        $_SESSION['error_message'] = "Appointment not found.";
    }

    $stmt->close();
    $conn->close();

    header("Location: view_appointments");
    exit;
} else {
    header("Location: view_appointments");
    exit;
}

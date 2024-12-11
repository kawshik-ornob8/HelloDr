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

// Process the appointment approval
if (isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];

    // Ensure payment_amount is passed or fetch it from the database
    if (isset($_POST['payment_amount']) && !empty($_POST['payment_amount'])) {
        $amount = $_POST['payment_amount'];
    } else {
        // Fetch the doctor's fee from the database
        $query = "SELECT d.doctor_fee 
                  FROM appointments a 
                  JOIN doctors d ON a.doctor_id = d.doctor_id 
                  WHERE a.appointment_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $amount = ($result->num_rows > 0) ? $result->fetch_assoc()['doctor_fee'] : 0;
        $stmt->close();
    }

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

        // Update the appointment status to "Approved"
        $update_stmt = $conn->prepare("UPDATE appointments SET status = 'Approved' WHERE appointment_id = ?");
        $update_stmt->bind_param("i", $appointment_id);

        if ($update_stmt->execute()) {
            // Prepare email notification
            // Prepare email notification
            $subject = "Appointment Approved";
            $message = "Dear " . htmlspecialchars($full_name) . ",\n\n" .
                "Your appointment has been approved.\n" .
                "Date: " . htmlspecialchars($appointment_date) . "\n" .
                "Time: " . htmlspecialchars($appointment_time) . "\n" .
                "Now you need to pay ৳" . htmlspecialchars($amount) . ".\n\n" .
                "To view or manage your appointment, please click the following link: " . $config['appointments_url'] . "\n\n" .
                "Thank you for choosing our service.\n\nBest regards,\nHello Dr.";

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
                $_SESSION['success_message'] = "Appointment approved, and email sent successfully.";
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Appointment approved, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $_SESSION['error_message'] = "Failed to approve the appointment.";
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

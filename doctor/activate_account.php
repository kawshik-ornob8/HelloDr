<?php
session_start();
include('../config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

if (isset($_GET['token'])) {
    $activation_token = $_GET['token'];

    // Check if the token exists in the database
    $stmt = $conn->prepare('SELECT doctor_id, full_name, email FROM doctors WHERE activation_token = ? AND is_active = 0');
    $stmt->bind_param('s', $activation_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
        $doctor_id = $doctor['doctor_id'];
        $doctor_name = $doctor['full_name'];
        $doctor_email = $doctor['email'];

        // Mark account as activated but pending admin approval
        $stmt = $conn->prepare('UPDATE doctors SET is_active = 2, activation_token = NULL WHERE activation_token = ?');
        $stmt->bind_param('s', $activation_token);

        if ($stmt->execute()) {
            // Send email to admin for approval
            $admin_email = 'contact.hellodr@gmail.com'; // Replace with the actual admin email
            $subject = "New Doctor Account Pending Approval";
            $message = <<<EOD
Dear Admin,

A new doctor account has been activated and is pending your approval.

Details:
- Name: $doctor_name
- Email: $doctor_email

Please log in to the admin panel to approve or reject the account.

Regards,
Hello Dr. Team
EOD;

            $mail = new PHPMailer(true);
            try {
                // SMTP server configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
    $mail->Password = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email sender and recipient
                $mail->setFrom($config['smtp_username'], 'Hello Dr.');
                $mail->addAddress($admin_email);

                // Email content
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body = $message;

                $mail->send();
            } catch (Exception $e) {
                $_SESSION['error'] = "Mailer Error: {$mail->ErrorInfo}";
            }

            $_SESSION['success'] = 'Your account has been activated successfully! Waiting for admin approval.';
            header('Location: ../login');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to activate your account. Please try again later.';
        }
    } else {
        $_SESSION['error'] = 'Invalid or expired activation token.';
    }
} else {
    $_SESSION['error'] = 'No activation token provided.';
}

// Redirect to the login page with an error message
header('Location: ../login');
exit();
?>

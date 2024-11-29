<?php
// Include the PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include('config.php');

// Set timezone to Ashulia (GMT+6)
date_default_timezone_set('Asia/Dhaka');

// Start the session
session_start();

$success_message = '';
$error_message = '';

// Handle password reset request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    // Determine the table based on user type
    $table = ($user_type === 'patient') ? 'patients' : 'doctors';

    // Check if email exists in the respective table
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $reset_token = bin2hex(random_bytes(16)); // Generate a secure token
        $reset_token_expiry = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

        // Save token to the database (reset any existing token)
        $stmt = $conn->prepare("UPDATE $table SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $reset_token, $reset_token_expiry, $email);
        if ($stmt->execute()) {
            // Send the reset email using PHPMailer
            $reset_link = "http://192.168.1.200/HelloDr/reset_password.php?token=$reset_token";
            $subject = "Password Reset Request";
            $message = <<<EOD
Dear user,

Click the link below to reset your password:
$reset_link

This link will expire in 1 hour and can only be used once.

If you did not request this, please ignore this email.

Regards,
Your Website Team
EOD;

            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            try {
                // SMTP server configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Gmail SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'kawshik15-14750@diu.edu.bd';  // Replace with your Gmail
                $mail->Password = '212-15-14750-ornob';   // Replace with your app-specific password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email sender and recipient
                $mail->setFrom('kawshik15-14750@diu.edu.bd', 'Hello Dr.'); // Replace with your email
                $mail->addAddress($email);  // Send the email to the user

                // Email content
                $mail->isHTML(false);  // Plain text email
                $mail->Subject = $subject;
                $mail->Body = $message;

                // Send the email
                if ($mail->send()) {
                    $success_message = "A password reset link has been sent to your email.";
                } else {
                    $error_message = "Failed to send reset email.";
                }
            } catch (Exception $e) {
                $error_message = "Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "Failed to generate a reset token. Please try again.";
        }
    } else {
        $error_message = "No account found with this email.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
        .navigation-buttons {
            margin-top: 20px;
        }
        .navigation-buttons a.back {
            background-color: red;
        }

        

        .navigation-buttons a {
            margin: 5px;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 50px;
            border-radius: 5px;
        }

        .navigation-buttons a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Forgot Password</h2>

        <?php 
        if (!empty($success_message)) {
            echo "<p style='color:green;'>$success_message</p>";
        } elseif (!empty($error_message)) {
            echo "<p style='color:red;'>$error_message</p>";
        }
        ?>

        <form action="forget.php" method="POST">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="user_type">I am a:</label>
            <select id="user_type" name="user_type" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
            </select>

            <button type="submit">Send Reset Link</button>
        </form>
        <!-- Navigation buttons -->
        <div class="navigation-buttons">
            <a href="index.php">Home</a>
            <a href="javascript:history.back()" class="back">Back</a>
        </div>
    </div>
</body>
</html>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
include('config.php');

date_default_timezone_set('Asia/Dhaka');
session_start();

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
        exit;
    }

    $allowed_tables = ['patients', 'doctors'];
    $table = ($user_type === 'patient') ? 'patients' : 'doctors';
    $id_field = ($user_type === 'patient') ? 'patient_id' : 'doctor_id';

    if (!in_array($table, $allowed_tables)) {
        die("Invalid user type.");
    }

    $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row[$id_field];
        $reset_token = bin2hex(random_bytes(16));
        $reset_token_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $stmt = $conn->prepare("UPDATE $table SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
        $stmt->bind_param("sss", $reset_token, $reset_token_expiry, $email);
        if ($stmt->execute()) {
            $reset_link = $config['base_url'] . "/HelloDr/newpassword.php?token=$reset_token&user_id=$user_id&user_type=$user_type";
            $subject = "Password Reset Request";
            $message = <<<EOD
Dear user,

Click the link below to reset your password:
$reset_link

This link will expire in 1 hour and can only be used once.

If you did not request this, please ignore this email.

Regards,
HelloDr Team
EOD;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom($config['smtp_username'], 'HelloDr Team');
                $mail->addAddress($email);
                $mail->isHTML(false);
                $mail->Subject = $subject;
                $mail->Body = $message;

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
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f8ff; /* Light blue-gray background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #ffffff;
            padding: 30px;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            font-size: 2em;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        p {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-size: 14px;
            color: #555;
        }

        input,
        select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            padding: 12px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #218838;
        }

        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #007bff;
            text-decoration: none;
        }

        .forgot-password:hover {
            color: #0056b3;
        }

        .alert {
            margin-top: 10px;
            padding: 10px;
            text-align: center;
        }

        .alert-success {
            color: green;
        }

        .alert-error {
            color: red;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Forgot Password</h2>
        <?php 
        if (!empty($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        } elseif (!empty($error_message)) {
            echo "<div class='alert alert-error'>$error_message</div>";
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

        <div class="mt-4 text-center">
            <a href="login.php" class="forgot-password">Back to Login</a>
        </div>
    </div>
</body>

</html>

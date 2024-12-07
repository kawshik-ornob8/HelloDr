<?php
session_start();
include('../config.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$errors = [];
$upload_dir = '../images/certificates/'; // Directory to store uploaded certificates

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST['full_name']);
    $dob = trim($_POST['dob']);
    $phone_number = trim($_POST['phone_number']);
    $email_address = trim($_POST['email_address']);
    $doctor_reg_id = trim($_POST['doctor_reg_id']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $certificate_tmp_name = '';

    // Validate inputs
    if (empty($full_name)) $errors[] = 'Full Name is required.';
    if (empty($dob)) $errors[] = 'Date of Birth is required.';
    if (empty($phone_number)) $errors[] = 'Phone Number is required.';
    if (empty($email_address) || !filter_var($email_address, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email Address is required.';
    if (empty($doctor_reg_id)) $errors[] = 'Doctor Registration ID is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    // Validate and temporarily upload the certificate
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['certificate']['tmp_name'];
        $file_name = basename($_FILES['certificate']['name']);
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array(strtolower($file_ext), $allowed_exts)) {
            $certificate_tmp_name = uniqid('cert_', true) . '.' . $file_ext;
            $destination = $upload_dir . $certificate_tmp_name;

            if (!move_uploaded_file($file_tmp, $destination)) {
                $errors[] = 'Failed to upload certificate.';
            }
        } else {
            $errors[] = 'Invalid certificate format. Only PDF, DOC, DOCX, JPG, JPEG, and PNG are allowed.';
        }
    } else {
        $errors[] = 'Certificate file is required.';
    }

    // Check for existing email or registration ID
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT * FROM doctors WHERE email = ? OR doctor_reg_id = ?');
        $stmt->bind_param('ss', $email_address, $doctor_reg_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'Email or Doctor Registration ID already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $activation_token = bin2hex(random_bytes(16)); // Generate a unique activation token

            // Insert into database
            $stmt = $conn->prepare('INSERT INTO doctors (full_name, date_of_birth, phone_number, email, doctor_reg_id, password, certificate, activation_token, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)');
            $stmt->bind_param('ssssssss', $full_name, $dob, $phone_number, $email_address, $doctor_reg_id, $hashed_password, $certificate_tmp_name, $activation_token);

            if ($stmt->execute()) {
                // Retrieve the inserted doctor's ID
                $doctor_id = $stmt->insert_id;

                // Rename the certificate file to match doctor_id
                $new_certificate_name = $doctor_id . '.' . $file_ext;
                $new_destination = $upload_dir . $new_certificate_name;

                if (rename($destination, $new_destination)) {
                    // Update the database with the new certificate name
                    $update_stmt = $conn->prepare('UPDATE doctors SET certificate = ? WHERE doctor_id = ?');
                    $update_stmt->bind_param('si', $new_certificate_name, $doctor_id);
                    $update_stmt->execute();
                } else {
                    $errors[] = 'Failed to rename the certificate.';
                }

                // Send activation email
                $activation_link = "http://192.168.1.200/HelloDr/doctor%20info/activate_account?token=$activation_token";
                $subject = "Account Activation Required";
                $message = <<<EOD
Dear $full_name,

Thank you for registering with us. Please activate your account by clicking the link below:
$activation_link

If you did not register for this account, please ignore this email.

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
                    $mail->addAddress($email_address);

                    // Email content
                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    if ($mail->send()) {
                        $_SESSION['success'] = 'Doctor registered successfully! Please check your email to activate your account.';
                        header('Location: ../login');
                        exit();
                    } else {
                        $errors[] = 'Failed to send activation email.';
                    }
                } catch (Exception $e) {
                    $errors[] = "Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $errors[] = 'Failed to register. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">s
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Signup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .signup-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="date"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        .errors p {
            color: red;
            font-size: 14px;
        }

        p {
            text-align: center;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create a Doctor Account</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="doctor_signup" method="POST" enctype="multipart/form-data">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required>

            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address" required>

            <label for="doctor_reg_id">Doctor Registration ID:</label>
            <input type="text" id="doctor_reg_id" name="doctor_reg_id" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <label for="certificate">Upload Certificate:</label>
            <input type="file" id="certificate" name="certificate" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>

            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="../login">Login here</a>.</p>
    </div>
</body>
</html>

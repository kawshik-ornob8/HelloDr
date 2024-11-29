<?php
session_start();
include '../config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $dob = trim($_POST['dob']);
    $sex = trim($_POST['sex']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate form inputs
    if (empty($full_name)) $errors[] = 'Full Name is required.';
    if (empty($dob)) $errors[] = 'Date of Birth is required.';
    if (empty($sex)) $errors[] = 'Sex is required.';
    if (empty($mobile)) $errors[] = 'Mobile Number is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    // Check for existing username or email
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT * FROM patients WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'Username or Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $activation_token = bin2hex(random_bytes(16)); // Generate a unique activation token

            $stmt = $conn->prepare('INSERT INTO patients (full_name, date_of_birth, sex, mobile_number, email, username, password, activation_token, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)');
            $stmt->bind_param('ssssssss', $full_name, $dob, $sex, $mobile, $email, $username, $hashed_password, $activation_token);

            if ($stmt->execute()) {
                // Send activation email
                $activation_link = "http://192.168.1.200/HelloDr/user%20info/activate_account.php?token=$activation_token";
                $subject = "Account Activation Required";
                $message = <<<EOD
Dear $full_name,

Thank you for registering with us. Please activate your account by clicking the link below:
$activation_link

If you did not register for this account, please ignore this email.

Regards,
Your Website Team
EOD;

                $mail = new PHPMailer(true);
                try {
                    // SMTP server configuration
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'kawshik15-14750@diu.edu.bd'; // Replace with your Gmail
                    $mail->Password = '212-15-14750-ornob'; // Replace with your app-specific password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Email sender and recipient
                    $mail->setFrom('kawshik15-14750@diu.edu.bd', 'Hello Dr.');
                    $mail->addAddress($email);

                    // Email content
                    $mail->isHTML(false);
                    $mail->Subject = $subject;
                    $mail->Body = $message;

                    if ($mail->send()) {
                        $_SESSION['success'] = 'Account created successfully! Please check your email to activate your account.';
                        header('Location: ../login.php');
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        /* CSS styling */
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

        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
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
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
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
        <h2>Create a User Account</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form action="user_signup.php" method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" name="dob" required>

            <label for="sex">Sex:</label>
            <select name="sex" required>
                <option value="">Select</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <label for="mobile">Mobile Number:</label>
            <input type="text" name="mobile" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="../login.php">Log in here</a></p>
    </div>
</body>
</html>

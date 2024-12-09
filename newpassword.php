<?php
include('config.php');
date_default_timezone_set('Asia/Dhaka');
session_start();

$success_message = '';
$error_message = '';

if (isset($_GET['token'], $_GET['user_id'], $_GET['user_type'])) {
    $reset_token = $_GET['token'];
    $user_id = $_GET['user_id'];
    $user_type = $_GET['user_type'];

    $allowed_tables = ['patients', 'doctors'];
    $table = ($user_type === 'patient') ? 'patients' : 'doctors';
    $id_field = ($user_type === 'patient') ? 'patient_id' : 'doctor_id';

    if (!in_array($table, $allowed_tables)) {
        die("Invalid user type.");
    }

    $stmt = $conn->prepare("SELECT * FROM $table WHERE reset_token = ? AND reset_token_expiry > NOW() AND $id_field = ?");
    $stmt->bind_param("si", $reset_token, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("UPDATE $table SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE $id_field = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    $success_message = "Your password has been reset successfully. You can now log in.";
                } else {
                    $error_message = "Failed to update password. Please try again.";
                }
            } else {
                $error_message = "Passwords do not match.";
            }
        }
    } else {
        $error_message = "Invalid or expired reset token.";
    }

    $stmt->close();
} else {
    $error_message = "Invalid request.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <h2>Reset Password</h2>
        <?php 
        if (!empty($success_message)) {
            echo "<p style='color:green;'>$success_message</p>";
        } elseif (!empty($error_message)) {
            echo "<p style='color:red;'>$error_message</p>";
        }
        ?>
        <form action="" method="POST">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>
</html>

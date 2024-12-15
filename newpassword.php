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
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="./images/favicon.png" type="image/png">
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
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

        input {
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
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Reset Password</h2>
        <?php
        if (!empty($success_message)) {
            echo "<div class='alert alert-success'>$success_message</div>";
        } elseif (!empty($error_message)) {
            echo "<div class='alert alert-error'>$error_message</div>";
        }
        ?>
        <form action="" method="POST">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            <button type="submit">Reset Password</button>
        </form>

        <div class="mt-4 text-center">
            <a href="login.php" class="forgot-password">Back to Login</a>
        </div>
    </div>
</body>

</html>

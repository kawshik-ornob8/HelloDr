<?php
session_start();
include('../config.php');

// Store the referring page for redirection after login
if (isset($_SERVER['HTTP_REFERER']) && !isset($_SESSION['redirect_to'])) {
    $_SESSION['redirect_to'] = $_SERVER['HTTP_REFERER'];
}

// Initialize error message
$error_message = "";

// Handle Patient Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if user exists in the patients table
    $stmt = $conn->prepare("SELECT * FROM patients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if account is active
        if ($row['is_active'] == 0) {
            $error_message = "Your account is not active. Please activate your account first.";
        } else {
            if (password_verify($password, $row['password'])) {
                $_SESSION['patient_id'] = $row['patient_id'];
                $_SESSION['username'] = $row['username'];

                // Redirect to the stored page or default profile page
                if (isset($_SESSION['redirect_to'])) {
                    $redirect_url = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']); // Clear after using
                    header("Location: $redirect_url");
                    exit;
                } else {
                    header("Location: user_profile"); // Default redirect
                    exit;
                }
            } else {
                $error_message = "Incorrect password.";
            }
        }
    } else {
        $error_message = "No user found with that username.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 5px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 20px;
        }

        .login-container input {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-container button {
            width: 88%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #0056b3;
        }

        .login-container a {
            display: inline-block;
            margin-top: 10px;
            color: #007BFF;
            text-decoration: none;
            margin-bottom: 10px;
            font-size: small;
            text-align: left;
        }

        .login-container a:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form action="user_login" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
                <a href="../forget">Forgot Password?</a>
                <a href="user_signup"> | Signup</a>

            <button type="submit" name="patient_login">Patient Login</button>
        </form>

        <a href="../index">Back to Home</a>

    </div>

</body>

</html>
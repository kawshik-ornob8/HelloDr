<?php
// Start the session
session_start();
include 'config.php';

// Initialize variables
$error_message = '';
$success_message = '';

// Function to reset the password
function resetPassword($conn, $table, $token, $hashed_password) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE $table SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);
        $stmt->execute();
        return true;
    }

    return false;
}

// Check if the token is present in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $error_message = "Invalid or missing token.";
} else {
    $token = htmlspecialchars($_GET['token']);

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate passwords
        if (strlen($new_password) < 8) {
            $error_message = "Password must be at least 8 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $found = false;

            // Check the token in both tables
            $tables = ['patients', 'doctors'];
            foreach ($tables as $table) {
                if (resetPassword($conn, $table, $token, $hashed_password)) {
                    $success_message = "Your password has been reset successfully.";
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $error_message = "Invalid or expired token.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Reset Password</h2>

        <?php 
        if (!empty($success_message)) {
            echo "<p style='color:green;'>$success_message</p>";
            echo "<p><a href='login.php'>Click here to login</a></p>";
        } elseif (!empty($error_message)) {
            echo "<p style='color:red;'>$error_message</p>";
        }
        ?>

        <?php if (empty($success_message)) { ?>
            <form action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">Reset Password</button>
            </form>
        <?php } ?>
    </div>
</body>
</html>

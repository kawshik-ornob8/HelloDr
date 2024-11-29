<?php
session_start();
include('../config.php');

if (isset($_GET['token'])) {
    $activation_token = $_GET['token'];

    // Check if the token exists in the database
    $stmt = $conn->prepare('SELECT doctor_id FROM doctors WHERE activation_token = ? AND is_active = 0');
    $stmt->bind_param('s', $activation_token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Activate the account
        $stmt = $conn->prepare('UPDATE doctors SET is_active = 1, activation_token = NULL WHERE activation_token = ?');
        $stmt->bind_param('s', $activation_token);

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Your account has been activated successfully! You can now log in.';
            header('Location: ../login.php');
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
header('Location: ../login.php');
exit();
?>

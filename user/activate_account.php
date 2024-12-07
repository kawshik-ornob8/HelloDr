<?php
include '../config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists and the account is inactive
    $stmt = $conn->prepare('SELECT * FROM patients WHERE activation_token = ? AND is_active = 0');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Activate the account and reset the activation token
        $stmt = $conn->prepare('UPDATE patients SET is_active = 1, activation_token = NULL WHERE activation_token = ?');
        $stmt->bind_param('s', $token);
        if ($stmt->execute()) {
            echo "Account activated successfully! You can now log in.";

            // Redirect to the login page after successful activation
            header("Location: ../login"); // Adjust the URL path to match your login page
            exit;
        } else {
            echo "Failed to activate account. Please try again.";
        }
    } else {
        echo "Invalid or expired token.";
    }

    $stmt->close();
}

$conn->close();
?>

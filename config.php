<?php
// Database Configuration
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password is an empty string
$dbname = "hello_dr";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// Check database connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Email Configuration
$config = [
    'smtp_username' => 'kawshik15-14750@diu.edu.bd',
    'smtp_password' => '212-15-14750-ornob',
    'smtp_host' => 'smtp.gmail.com', // Update based on your email provider
    'smtp_port' => 587, // Typical port for TLS
    'smtp_secure' => 'tls', // Use 'ssl' or 'tls' based on your provider
    'email_from' => 'kawshik15-14750@diu.edu.bd', // Replace with your preferred "from" email
    'email_from_name' => 'HelloDr Support',
];

// Error page handler
function handleError($error_message = null) {
    $_SESSION['error_message'] = $error_message; // Optionally pass a custom error message
    header('Location: error/error.php');
    exit;
}

// Close database connection
if (!function_exists('closeConnection')) {
    function closeConnection($connection) {
        if ($connection) {
            $connection->close();
        }
    }
}
?>

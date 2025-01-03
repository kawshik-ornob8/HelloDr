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
    'smtp_password' => 'e-mail pass',
    'smtp_host' => 'smtp.gmail.com', // Update based on your email provider
    'smtp_port' => 587, // Typical port for TLS
    'smtp_secure' => 'tls', // Use 'ssl' or 'tls' based on your provider
    'email_from' => 'kawshik15-14750@diu.edu.bd', // Replace with your preferred "from" email
    'email_from_name' => 'HelloDr Support',
];

// Define the base URL for your application
$config['base_url'] = 'http://172.20.10.2';
$config['payment_url'] = $config['base_url'] . '/HelloDr/user/patients_payment'; // Payment page URL
$config['appointments_url'] = $config['base_url'] . '/HelloDr/user/patients_view_appointments'; // View appointments URL



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

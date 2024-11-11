<?php
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password is an empty string
$dbname = "hello_dr";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define closeConnection only if it hasn't been declared
if (!function_exists('closeConnection')) {
    function closeConnection($connection) {
        if ($connection) {
            $connection->close();
        }
    }
}

// Use closeConnection($conn); at the end of your scripts to close the connection
?>

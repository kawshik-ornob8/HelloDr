<?php
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password (usually empty by default in XAMPP)
$dbname = "hello dr."; // The database name you created

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

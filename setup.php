<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password is an empty string
$dbname = "hello_dr";

// Create a connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database '$dbname' created or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Set character set to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// SQL queries to create tables only if they don't already exist
$queries = [
    "CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE
    )",
    "CREATE TABLE IF NOT EXISTS appointments (
        appointment_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('Approved', 'Pending', 'Cancelled') DEFAULT 'Pending',
        patient_id INT NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS doctors (
        doctor_id INT AUTO_INCREMENT PRIMARY KEY,
        phone_number VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        doctor_reg_id VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        specialty VARCHAR(100),
        degree VARCHAR(255),
        bio TEXT,
        profile_photo VARCHAR(255),
        full_name VARCHAR(100) NOT NULL,
        date_of_birth DATE NOT NULL,
        sex ENUM('Male', 'Female', 'Other') NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS messages (
        message_id INT AUTO_INCREMENT PRIMARY KEY,
        appointment_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        doctor_id INT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        patient_id INT NOT NULL,
        sender ENUM('Doctor', 'Patient') NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS patients (
        patient_id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        date_of_birth DATE NOT NULL,
        sex ENUM('Male', 'Female', 'Other') NOT NULL,
        mobile_number VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        profile_photo VARCHAR(255)
    )",
    "CREATE TABLE IF NOT EXISTS reviews (
        review_id INT AUTO_INCREMENT PRIMARY KEY,
        doctor_id INT NOT NULL,
        patient_id INT NOT NULL,
        rating INT CHECK(rating BETWEEN 1 AND 5),
        review_text TEXT,
        review_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS specialties (
        specialty_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS team (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        position VARCHAR(50) NOT NULL,
        image_url VARCHAR(255),
        instagram_url VARCHAR(255),
        facebook_url VARCHAR(255),
        twitter_url VARCHAR(255)
    )"
];

// Execute each query
foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table checked/created successfully: " . strtok($query, " ") . "<br>";
    } else {
        echo "Error creating/checking table: " . $conn->error . "<br>";
    }
}

// Insert default admin data if not already exists
$default_admin_sql = "
    INSERT INTO admins (full_name, username, password, email)
    SELECT 'Kawshik Ahmed Ornob', 'iam_kawshik', 'kawshik', 'kawshik.ornob8@gmail.com'
    WHERE NOT EXISTS (
        SELECT 1 FROM admins WHERE username = 'iam_kawshik'
    ) LIMIT 1;
";

// Execute the query to insert default admin
if ($conn->query($default_admin_sql) === TRUE) {
    echo "Default admin inserted successfully.<br>";
} else {
    echo "Error inserting default admin: " . $conn->error . "<br>";
}

// Close connection
$conn->close();

echo "Setup completed successfully!";
?>

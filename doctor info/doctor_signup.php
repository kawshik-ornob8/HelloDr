<?php
// Include the database connection file
include('../config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $phone_number = $_POST['phone_number'];
    $email_address = $_POST['email_address'];
    $doctor_reg_id = $_POST['doctor_reg_id'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

    // Check if email already exists
    $check_email = $conn->prepare("SELECT email FROM doctors WHERE email = ?");
    $check_email->bind_param("s", $email_address);
    $check_email->execute();
    $check_email->store_result();

    if ($check_email->num_rows > 0) {
        echo "Error: This email is already registered!";
    } else {
        // Prepare the SQL statement for insertion
        $stmt = $conn->prepare("INSERT INTO doctors (full_name, date_of_birth, phone_number, email, doctor_reg_id, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $full_name, $dob, $phone_number, $email_address, $doctor_reg_id, $password);

        // Execute the query
        if ($stmt->execute()) {
            echo "Doctor registered successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    $check_email->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="signup-container">
        <h2>Doctor Signup</h2>
        <form action="doctor_signup.php" method="POST">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="doctor_reg_id">Doctor Registration ID:</label>
            <input type="text" id="doctor_reg_id" name="doctor_reg_id" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="../login.php">Login here</a>.</p>
    </div>
</body>
</html>

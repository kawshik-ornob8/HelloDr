<?php
session_start();
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
        // Check if doctor_reg_id already exists
        $check_reg_id = $conn->prepare("SELECT doctor_reg_id FROM doctors WHERE doctor_reg_id = ?");
        $check_reg_id->bind_param("s", $doctor_reg_id);
        $check_reg_id->execute();
        $check_reg_id->store_result();

        if ($check_reg_id->num_rows > 0) {
            echo "Error: This Doctor Registration ID is already taken!";
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

        $check_reg_id->close();
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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }

        .signup-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="date"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }

        p {
            text-align: center;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Create a Doctor Account</h2>
        
        <form action="doctor_signup.php" method="POST">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="dob">Date of Birth:</label>
            <input type="date" id="dob" name="dob" required>

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required>

            <label for="email_address">Email Address:</label>
            <input type="email" id="email_address" name="email_address" required>

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

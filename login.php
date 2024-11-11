<?php
// Start the session
session_start();
include 'config.php';

// Handle Patient Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if user exists in the patients table
    $stmt = $conn->prepare("SELECT * FROM patients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            header("Location: patient_dashboard.php");
            exit;
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "No user found with that username.";
    }
    $stmt->close();
}

// Handle Doctor Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['doctor_login'])) {
    $doctor_id = $_POST['doctor_id'];
    $password = $_POST['password'];

    // Check if doctor exists in the doctors table
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_reg_id = ?");
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['doctor_id'] = $row['doctor_id'];
            $_SESSION['doctor_reg_id'] = $row['doctor_reg_id'];
            header("Location: doctor info/doctor_dashboard.php");
            exit;
        } else {
            $error_message = "Incorrect password.";
        }
    } else {
        $error_message = "No doctor found with that registration ID.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <link rel="stylesheet" href="css/login.css">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>

        <p>Please choose your login type:</p>
        <div class="login-options">
            <a href="#" id="patient-login-link" class="login-button">Patient Login</a>
            <a href="#" id="doctor-login-link" class="login-button">Doctor Login</a>
        </div>

        <!-- Patient Login Form -->
        <div id="patient-login-form" style="display:none;">
            <form action="login.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" name="patient_login">Login</button>
            </form>
        </div>

        <!-- Doctor Login Form -->
        <div id="doctor-login-form" style="display:none;">
            <form action="login.php" method="POST">
                <label for="doctor_id">Doctor Registration ID:</label>
                <input type="text" id="doctor_id" name="doctor_id" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" name="doctor_login">Login</button>
            </form>
        </div>
    </div>

    <script>
        // Show the respective login form based on the selection
        document.getElementById('patient-login-link').onclick = function() {
            document.getElementById('patient-login-form').style.display = 'block';
            document.getElementById('doctor-login-form').style.display = 'none';
        };

        document.getElementById('doctor-login-link').onclick = function() {
            document.getElementById('doctor-login-form').style.display = 'block';
            document.getElementById('patient-login-form').style.display = 'none';
        };
    </script>
</body>
</html>

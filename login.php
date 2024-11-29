<?php
// Start the session
session_start();
include 'config.php';

// Initialize the failed login attempt counter if not set
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}

// Check if the user visited forget.php and reset the counter
if (isset($_SESSION['visited_forget']) && $_SESSION['visited_forget']) {
    $_SESSION['failed_attempts'] = 0;
    $_SESSION['visited_forget'] = false; // Reset this flag after counter reset
}

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

        // Check if account is active
        if ($row['is_active'] == 0) {
            $error_message = "Your account is not active. Please contact support.";
        } else {
            if (password_verify($password, $row['password'])) {
                // Reset failed attempts on successful login
                $_SESSION['failed_attempts'] = 0;

                $_SESSION['patient_id'] = $row['patient_id'];
                $_SESSION['username'] = $row['username'];
                header("Location: index.php");
                exit;
            } else {
                $_SESSION['failed_attempts'] += 1;
                $error_message = "Incorrect password.";
            }
        }
    } else {
        $_SESSION['failed_attempts'] += 1;
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

        // Check if account is active
        if ($row['is_active'] == 0) {
            $error_message = "Your account is not active. Please contact support.";
        } else {
            if (password_verify($password, $row['password'])) {
                // Reset failed attempts on successful login
                $_SESSION['failed_attempts'] = 0;

                $_SESSION['doctor_id'] = $row['doctor_id'];
                $_SESSION['doctor_reg_id'] = $row['doctor_reg_id'];
                header("Location: doctor info/doctor_dashboard.php");
                exit;
            } else {
                $_SESSION['failed_attempts'] += 1;
                $error_message = "Incorrect password.";
            }
        }
    } else {
        $_SESSION['failed_attempts'] += 1;
        $error_message = "No doctor found with that registration ID.";
    }
    $stmt->close();
}

// Redirect to forget.php if 3 failed attempts
if ($_SESSION['failed_attempts'] >= 3) {
    $_SESSION['visited_forget'] = true; // Set flag to indicate forget.php visit
    header("Location: forget.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/login.css">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
            background-color: grey;
        }
        .navigation-buttons {
            margin-top: 3%;
        }
        .navigation-buttons a.back {
            background-color: red;
        }
        .navigation-buttons a.home {
            background-color: #28a745;
        }

        

        .navigation-buttons a {
            display: flex;
            margin-bottom: 3%;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 44.5%;
            border-radius: 5px;
        }

        .navigation-buttons a:hover {
            background-color: #0056b3;
        }
        .doctor-login-form {
            text-decoration: none;
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

            <a href="forget.php" class="forget" style="text-decoration: none; margin-top: 5px;">Forgotten password?</a>
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

            <a href="forget.php" class="forget" style="text-decoration: none; margin-top: 5px;">Forgotten password?</a>


        </div>
        <!-- Navigation buttons -->
        <div class="navigation-buttons">
        <a href="index.php" class="home">Home</a>
            <a href="javascript:history.back()" class="back">Back</a>
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

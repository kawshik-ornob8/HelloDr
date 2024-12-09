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
    $_SESSION['visited_forget'] = false;
}

// Handle Patient Login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['patient_login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM patients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if patient is active
        if ($row['is_active'] == 0) {
            $error_message = "Your account is not active. Please check your email.";
        } elseif ($row['is_active'] == 1) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['patient_id'] = $row['patient_id'];
                $_SESSION['username'] = $row['username'];
                header("Location: index");
                exit;
            } else {
                $_SESSION['failed_attempts'] += 1;
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "Unknown account status. Please contact support.";
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

    $stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_reg_id = ?");
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if ($row['is_active'] == 0) {
            $error_message = "Your account is not active. Please check your email.";
        } elseif ($row['is_active'] == 2) {
            $error_message = "Please wait for admin approval of your account.";
        } elseif ($row['is_active'] == 1) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['failed_attempts'] = 0;
                $_SESSION['doctor_id'] = $row['doctor_id'];
                $_SESSION['doctor_reg_id'] = $row['doctor_reg_id'];
                header("Location: doctor/doctor_dashboard");
                exit;
            } else {
                $_SESSION['failed_attempts'] += 1;
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "Unknown account status. Please contact support.";
        }
    } else {
        $_SESSION['failed_attempts'] += 1;
        $error_message = "No doctor found with that registration ID.";
    }
    $stmt->close();
}

// Redirect to forget.php if 3 failed attempts
if ($_SESSION['failed_attempts'] >= 3) {
    $_SESSION['visited_forget'] = true;
    header("Location: forget");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Sign In</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />


</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-center">
            <!-- Login Form -->
            <div class="w-full md:w-1/2 lg:w-1/3 bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Sign In</h2>
                <p class="text-gray-600 mb-6">Please choose your login type:</p>

                <?php if (isset($error_message)) {
                    echo "<p class='text-red-500 text-sm mb-4'>$error_message</p>";
                } ?>

                <!-- Role Selection -->
                <div class="mb-4">
                    <label class="block text-gray-700">Select Role</label>
                    <select id="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" onchange="toggleLoginForms()">
                        <option value="" disabled selected>Select Role</option>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                    </select>
                </div>

                <!-- Patient Login Form -->
                <form action="login" method="POST" id="patient-login-form" style="display:none;">
                    <div class="mb-4">
                        <label class="block text-gray-700">Username</label>
                        <input type="text" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700">Password</label>
                        <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" required />
                    </div>
                    <button type="submit" name="patient_login" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition duration-200">Patient Login</button>
                </form>

                <!-- Doctor Login Form -->
                <form action="login" method="POST" id="doctor-login-form" style="display:none;">
                    <div class="mb-4">
                        <label class="block text-gray-700">Doctor Registration ID</label>
                        <input type="text" name="doctor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" required />
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700">Password</label>
                        <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-green-500" required />
                    </div>
                    <button type="submit" name="doctor_login" class="w-full bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition duration-200">Doctor Login</button>
                </form>

                <!-- Forgot Password link -->
                <div class="mt-4">
                    <a href="forget" class="text-gray-600 hover:text-gray-900">Forgot Password?</a>
                </div>

                <div class="mt-6 text-center">
                    <p class="text-gray-600">or sign in with</p>
                    <div class="flex justify-center mt-4">
                        <a class="text-red-500 mx-6" href="#"><i class="fab fa-google"></i></a>
                    </div>
                </div>
                <div class="mb-4">
                    <button onclick="window.location.href='index';" class="bg-gray-300 text-black py-2 px-4 rounded-md shadow hover:bg-gray-400">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>

                </div>
            </div>



            <!-- Right Side Image -->
            <div class="hidden md:block md:w-1/2 lg:w-2/3">
                <img alt="Illustration of a person using a laptop and mobile devices" class="w-full h-auto" height="400" src="images/undraw.svg" width="400" />
            </div>
        </div>
    </div>
</body>

</html>

<script>
    function toggleLoginForms() {
        const role = document.getElementById('role').value;
        const patientForm = document.getElementById('patient-login-form');
        const doctorForm = document.getElementById('doctor-login-form');

        if (role === 'patient') {
            patientForm.style.display = 'block';
            doctorForm.style.display = 'none';
        } else if (role === 'doctor') {
            doctorForm.style.display = 'block';
            patientForm.style.display = 'none';
        } else {
            patientForm.style.display = 'none';
            doctorForm.style.display = 'none';
        }
    }
</script>
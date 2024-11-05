<?php
// Start session and include database connection
session_start();
include '../config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $dob = trim($_POST['dob']);
    $sex = trim($_POST['sex']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate form inputs
    if (empty($full_name)) $errors[] = 'Full Name is required.';
    if (empty($dob)) $errors[] = 'Date of Birth is required.';
    if (empty($sex)) $errors[] = 'Sex is required.';
    if (empty($mobile)) $errors[] = 'Mobile Number is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid Email is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($password)) $errors[] = 'Password is required.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    // Check for existing username or email
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = ? OR email = ?');
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'Username or Email already exists.';
        } else {
            // Insert new user data
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (full_name, dob, sex, mobile, email, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('sssssss', $full_name, $dob, $sex, $mobile, $email, $username, $hashed_password);

            if ($stmt->execute()) {
                header('Location: login.php');
                exit();
            } else {
                $errors[] = 'Failed to register. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Signup</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="signup-container">
    <h2>Create an Account</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form action="signup.php" method="POST">
        <label for="full_name">Full Name:</label>
        <input type="text" name="full_name" required>

        <label for="dob">Date of Birth:</label>
        <input type="date" name="dob" required>

        <label for="sex">Sex:</label>
        <select name="sex" required>
            <option value="">Select</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label for="mobile">Mobile Number:</label>
        <input type="text" name="mobile" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Sign Up</button>
    </form>
    <p>Already have an account? <a href="login.php">Log in here</a></p>
</div>
</body>
</html>

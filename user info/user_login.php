<?php
session_start();
include('../config.php');

$error_message = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "Username and password are required!";
    } else {
        // Check credentials in the patients table
        $query = "SELECT * FROM patients WHERE username = ?";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            die("Error preparing the query: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Debugging: Check if any rows are returned
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['patient_id'] = $user['patient_id'];

                if (isset($_SESSION['redirect_to'])) {
                    $redirect_url = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: $redirect_url");
                } else {
                    header("Location: home.php");
                }
                exit;
            } else {
                $error_message = "Invalid username or password!";
            }
        } else {
            $error_message = "User not found!"; // This means no user was found with the entered username
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
</head>
<body>

    <h2>Login</h2>
    
    <?php if (!empty($error_message)): ?>
        <div style="color: red;"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form action="" method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        
        <button type="submit">Login</button>
    </form>

</body>
</html>

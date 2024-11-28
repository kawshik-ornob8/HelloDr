<?php
// admin_signup.php

// Include the database connection
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert data into the admins table
    $sql = "INSERT INTO admins (full_name, username, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $full_name, $username, $email, $password);

    if ($stmt->execute()) {
        echo "<script>alert('Admin registration successful!'); window.location.href = 'admin_login.php';</script>";
    } else {
        echo "<script>alert('Error: Could not register admin.');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Signup</title>
    <link rel="stylesheet" href="css/admin_signup.css">
    <style>
        /* General reset for margins, padding, and box-sizing */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Container for the signup form */
        .signup-container {
            background-color: #fff;
            width: 100%;
            max-width: 500px;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: box-shadow 0.3s ease-in-out;
        }

        /* Hover effect for the container */
        .signup-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Heading styling */
        h2 {
            font-size: 1.8rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        /* Label styling */
        label {
            display: block;
            font-size: 1rem;
            color: #555;
            margin-bottom: 8px;
            text-align: left;
            font-weight: 500;
        }

        /* Input fields styling */
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f7f9fc;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        /* Focus effect for inputs */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
            box-shadow: inset 0 1px 5px rgba(0, 123, 255, 0.25);
        }

        /* Submit button styling */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        /* Hover effect for submit button */
        button[type="submit"]:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }

        /* Back to Dashboard button styling */
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 20px;
            background-color: #28a745;
            color: #fff;
            text-decoration: none;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        /* Hover effect for Back to Dashboard button */
        .button:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }

        /* Responsive styling for smaller screens */
        @media (max-width: 480px) {
            .signup-container {
                padding: 20px;
                width: 90%;
            }

            h2 {
                font-size: 1.5rem;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                padding: 10px;
            }

            button[type="submit"] {
                font-size: 1rem;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>Add New Admin</h2>
        <form action="admin_signup.php" method="POST">
            <label for="fullname">Full Name:</label>
            <input type="text" id="fullname" name="fullname" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Add Admin</button>
        </form>

        <!-- Back to Dashboard Button -->
        <a href="admin_dashboard.php" class="button">Back to Dashboard</a>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="icon" type="image/x-icon" href="./images/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f8ff; /* Light blue-gray background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            background-color: #ffffff;
            width: 90%;
            max-width: 500px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(50px);
            opacity: 0;
            animation: fadeIn 1s forwards;
        }

        h2 {
            text-align: center;
            font-size: 2em;
            color: #2c3e50; /* Darker text color */
            margin-bottom: 20px;
            animation: slideIn 1s ease-out forwards;
        }

        .option-buttons {
            display: flex;
            justify-content: space-around;
            flex-direction: column;
            gap: 15px;
        }

        .option-buttons a {
            display: block;
            text-align: center;
            background-color: #28a745; /* Green for button */
            padding: 15px;
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            transition: background-color 0.3s;
            animation: fadeIn 1.5s ease-out forwards;
        }

        .option-buttons a:hover {
            background-color: #218838; /* Darker green for hover */
        }

        .option-buttons a:active {
            transform: scale(0.98);
        }

        /* Animation for fading and sliding in */
        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            0% {
                transform: translateY(-50px);
            }
            100% {
                transform: translateY(0);
            }
        }

        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #007bff; /* Blue for the link */
            text-decoration: none;
            transition: color 0.3s;
        }

        .forgot-password:hover {
            color: #0056b3; /* Darker blue on hover */
        }

    </style>
</head>
<body>

    <div class="container">
        <h2>Welcome to Our Platform</h2>
        
        <div class="option-buttons">
            <a href="doctor info/doctor_signup.php">Doctor Signup</a>
            <a href="user info/user_signup.php">User Signup</a>
            <a href="login.php">Login</a>
            <a href="forgot_password.php" class="forgot-password">Forgot Password</a>
        </div>
    </div>

    <script>
        // Adding animation delay for the container to appear after page load
        window.addEventListener('load', () => {
            document.querySelector('.container').style.animation = 'fadeIn 1s forwards';
            document.querySelector('h2').style.animation = 'slideIn 1s ease-out forwards';
        });
    </script>

</body>
</html>

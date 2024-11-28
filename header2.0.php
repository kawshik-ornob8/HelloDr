<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Hello Dr.'; ?></title>
    <link rel="stylesheet" href="./css/send_message.css">
    <link rel="stylesheet" href="./css/contact.css">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
    </style>
</head>
<body>
    <!--=====NAVBAR======-->
    <nav>
        <div class="container nav__container">
            <a href="index.php"><h4>HELLO DR.</h4></a>
            <ul class="nav__menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="appointment.php">Appointment</a></li>
                <li><a href="contact.php">Contact</a></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li><a href="user info/user_profile.php">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
            <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
            <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
        </div>
    </nav>
    <!--=====END OF NAVBAR======-->
    <script src="./main.js"></script>
</body>
</html>
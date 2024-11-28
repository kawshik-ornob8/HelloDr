<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php'; // Ensure database connection logic is correct

// Fetch doctor_id from URL or session
if (isset($_GET['doctor_id'])) {
    $doctor_id = intval($_GET['doctor_id']);
} elseif (isset($_SESSION['doctor_id'])) {
    $doctor_id = intval($_SESSION['doctor_id']);
} else {
    header("Location: error_page.php?error=Doctor ID not specified"); // Redirect to error page
    exit();
}

// Redirect to login page if patient_id is not set in session
if (isset($_SESSION['patient_id'])) {
    $patient_id = intval($_SESSION['patient_id']);
} else {
    header("Location: user%20info/user_login.php");
    exit();
}

// Fetch the doctor's full name from the database
$doctor_name = "Unknown Doctor";
$sql = "SELECT full_name FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $stmt->bind_result($fetched_name);
    if ($stmt->fetch()) {
        $doctor_name = htmlspecialchars($fetched_name, ENT_QUOTES, 'UTF-8');
    }
    $stmt->close();
} else {
    error_log("Database error: " . $conn->error); // Log error for debugging
    header("Location: error_page.php?error=Database error");
    exit();
}

// Fetch the full name of the patient from the database
$patient_name = "Patient"; // Default fallback if the name is not found

$sql = "SELECT full_name FROM patients WHERE patient_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $stmt->bind_result($fetched_patient_name);
    if ($stmt->fetch()) {
        $patient_name = htmlspecialchars($fetched_patient_name, ENT_QUOTES, 'UTF-8');
    }
    $stmt->close();
} else {
    error_log("Database error: " . $conn->error); // Log error for debugging
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message to Dr. <?php echo $doctor_name; ?></title>
    <link rel="stylesheet" href="./css/send_message.css">
    <link rel="stylesheet" href="./css/contact.css">
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
    </style>
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
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

    <!--=====Send Message Section======-->
    <section class="contact">
        <div class="container contact__container">
            <aside class="contact__aside">
                <div class="aside__image">
                    <img src="./images/text.png" alt="Send Message">
                </div>
                <h4>Send a Message To:</h4>
                <h3>
                    Dr. <?php echo $doctor_name; ?>
                </h3>
                <p>Provide clear details to help your doctor assist you better.</p>
            </aside>
            <form id="messageForm" action="send_action.php" method="post" class="contact__form">
                <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="sender" value="1"> <!-- sender = 1 for patient -->

                <div class="form__name">
    <input type="text" name="Patient Name" value="<?php echo $patient_name; ?>" readonly>
</div>

                <textarea name="message" rows="7" placeholder="Type your message here" required></textarea>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </section>
    <!--=====End of Send Message Section======-->

    <!--=====Footer======-->
    <footer>
        <div class="container footer__container">
            <div class="footer__1">
                <a href="index.php" class="footer__logo"><h4>HELLO DR.</h4></a>
                <p>
                    At Your Doctors Online, we provide accessible virtual health care for your family around the clock with the help of our board-certified online doctors.
                </p>
            </div>
            <div class="footer__2">
                <h4>Permalink</h4>
                <ul class="permalink">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="appointment.php">Appointment</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer__3">
                <h4>Privacy</h4>
                <ul class="privacy">
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms and Conditions</a></li>
                    <li><a href="#">Refund Policy</a></li>
                </ul>
            </div>
            <div class="footer__4">
                <h4>Contact Us</h4>
                <p>+8801975288108</p>
                <p>kawshik.ornob8@gmail.com</p>
                <ul class="footer__socials">
                    <li><a href="https://www.facebook.com/kawshik.ornob.01"><i class="uil uil-facebook-f"></i></a></li>
                    <li><a href="https://www.instagram.com/iam_kawshik/"><i class="uil uil-instagram-alt"></i></a></li>
                    <li><a href="https://twitter.com/iam_kawshik/"><i class="uil uil-twitter"></i></a></li>
                </ul>
            </div>
        </div>
        <div class="footer__copyright">
            <small>Copyright &copy; Hello Dr. All Rights Reserved.</small>
        </div>
    </footer>
    <!--=====End Footer======-->
    <script src="./main.js"></script>
</body>
</html>

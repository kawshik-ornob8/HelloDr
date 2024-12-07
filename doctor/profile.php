
<?php
session_start();
include('../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS Links -->
    <link rel="stylesheet" href="./css/view_profile.css">
     <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;800;900&display=swap" rel="stylesheet">
    <title>Doctor Profile</title>
</head>
<body>
    <header>
    <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<nav>
    <div class="container nav__container">
        <a href="../index"><h4>HELLO DR.</h4></a>
        <ul class="nav__menu">
            <li><a href="doctor_dashboard">Dashboard</a></li>
            <li><a href="view_appointments">Appointments</a></li>
            
            <?php if (isset($_SESSION['doctor_reg_id'])): ?>
                <!-- Show username and logout option when logged in -->
                <li><a href="user_login">Your Login Reg ID: <?php echo htmlspecialchars($_SESSION['doctor_reg_id']); ?></a></li>
                <li><a href="../logout">Logout</a></li>
            <?php else: ?>
                <!-- Show login and signup options when logged out -->
                <li><a href="../login">Login</a></li>
                <li><a href="../signup">Signup</a></li>
            <?php endif; ?>
        </ul>
        
        <button id="open-menu-btn"><i class="uil uil-bars"></i></button>
        <button id="close-menu-btn"><i class="uil uil-multiply"></i></button>
    </div>
</nav>

    </header>
    <div class="containers">
        <?php
        $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
        $view_all = isset($_GET['view_all']) ? true : false;
        

        // Fetch doctor data
        $sql = "SELECT * FROM doctors WHERE doctor_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $doctor = $result->fetch_assoc();
            $full_name = htmlspecialchars($doctor['full_name']);
            $specialty = htmlspecialchars($doctor['specialty']);
            $bio = htmlspecialchars($doctor['bio'] ?? "Bio not available.");

            $email = htmlspecialchars($doctor['email']);
            $phone_number = htmlspecialchars($doctor['phone_number']);
            $doctor_reg_id = htmlspecialchars($doctor['doctor_reg_id']);
            $profile_photo_path = "images/default.jpg";

            // Check for profile photo
            $extensions = ['jpg', 'jpeg', 'png'];
            foreach ($extensions as $ext) {
                $possible_path = "images/{$doctor_id}.$ext";
                if (file_exists($possible_path)) {
                    $profile_photo_path = $possible_path;
                    break;
                }
            }
            ?>
            <div class="profile-card">
                <img src="<?php echo $profile_photo_path; ?>" alt="Profile Picture">
                <h2><?php echo $full_name; ?></h2>
                <p><?php echo $specialty; ?></p>
                <div class="contact-info">
                    <a class="mail" href="mailto:<?php echo $email; ?>">📧 <?php echo $email; ?></a>
                    <br>
                    <a class="phone" href="tel:<?php echo $phone_number; ?>">📲 <?php echo $phone_number; ?></a>
                    <p>Doctor Registration ID: <?php echo $doctor_reg_id; ?></p>
                </div>
                <a href="profile_edit?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="profile-btn">Edit Profile</a>
            </div>
            <div class="continer_reviews">
            <div class="review-section">
                <h3>About <?php echo $full_name; ?></h3>
                <div class="teacher-info">
                    <div>
                        <p><strong>Specialty:</strong> <?php echo $specialty; ?></p>
                        <p><strong>Doctor ID:</strong> <?php echo $doctor_reg_id; ?></p>
                        <p><strong>Bio:</strong> <?php echo $bio; ?></p>
                    </div>
                </div>
                <h3>Reviews</h3>
                <?php
                $limit = $view_all ? "" : "LIMIT 3";
                $review_sql = "SELECT r.rating, r.review_text, p.full_name FROM reviews r JOIN patients p ON r.patient_id = p.patient_id WHERE r.doctor_id = ? ORDER BY r.created_at DESC $limit";
                $review_stmt = $conn->prepare($review_sql);
                $review_stmt->bind_param("i", $doctor_id);
                $review_stmt->execute();
                $review_result = $review_stmt->get_result();

                if ($review_result->num_rows > 0) {
                    while ($review = $review_result->fetch_assoc()) {
                        $rating = htmlspecialchars($review['rating']);
                        $review_text = htmlspecialchars($review['review_text']);
                        $author = htmlspecialchars($review['full_name']);
                        ?>
                        <div class="review-item">
                            <div class="rating"><?php echo str_repeat("★", $rating) . str_repeat("☆", 5 - $rating); ?></div>
                            <p class="review-text"><?php echo $review_text; ?></p>
                            <p class="review-author">- <?php echo $author; ?></p>
                        </div>
                        <?php
                    }
                    if (!$view_all) {
                        echo '<a href="?doctor_id=' . $doctor_id . '&view_all=true" class="view-all-btn">View All Reviews</a>';
                    }
                } else {
                    echo "<p>No reviews found for this doctor.</p>";
                }

                $review_stmt->close();
                ?>
            </div>
            </div>
            <?php
        } else {
            echo "<p>Doctor not found.</p>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
    
    <!-- Footer -->
<!--=====Footer======-->
<footer>
    <div class="container footer__container">
        <div class="footer__1">
            <a href="index" class="footer__logo"><h4>HELLO DR.</h4></a>
            <p>
                At Your Doctors Online, we provide easily accessible virtual health care for your families around the clock with the help of our board-certified online doctors. 
                We believe that everyone in the world should have the ability to connect with an experienced doctor online.
            </p>
        </div>
        <div class="footer__2">
            <h4>Permalink</h4>
            <ul class="permalink">
                <li><a href="../index">Home</a></li>
                <li><a href="../about">About</a></li>
                <li><a href="../appointment">Appointment</a></li>
                <li><a href="../admin/admin_dashboard">Admin Potal</a></li>
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
            <h4>Contact us</h4>
            <div>
                <p>+8801975288108</p>
                <p><a href="mailto:kawshik.ornob8@gmail.com">kawshik.ornob8@gmail.com</a></p>
            </div>
            <ul class="footer__socials">
                <li><a href="https://www.facebook.com/kawshik.ornob.01" target="_blank"><i class="uil uil-facebook-f"></i></a></li>
                <li><a href="https://www.instagram.com/iam_kawshik/" target="_blank"><i class="uil uil-instagram-alt"></i></a></li>
                <li><a href="https://www.twitter.com/iam_kawshik/" target="_blank"><i class="uil uil-twitter"></i></a></li>
            </ul>
        </div>
        
    </div>
    <div class="footer__copyright">
        <small>&copy; <?php echo date("Y"); ?> Hello Dr. All Rights Reserved.</small>
    </div>
</footer>
<!--=====End Footer======-->
<!-- JavaScript Files -->
<script>

    //change navbar styles on scroll

window.addEventListener('scroll', () => {
    document.querySelector('nav').classList.toggle('window-scroll', window.scrollY > 0)
})




//show/hide nav menu
const menu = document.querySelector(".nav__menu");
const menuBtn = document.querySelector("#open-menu-btn");
const closeBtn = document.querySelector("#close-menu-btn");
//There is a problem i can,t find why the button is not working
menuBtn.addEventListener('click', () => {
    menu.style.display = "flex";
    closeBtn.style.display = "inline-block";
    menuBtn.style.display = "none";
})

//close nav menu
const closeNav = () => {
    menu.style.display = "none";
    closeBtn.style.display = "none";
    menuBtn.style.display = "inline-block";
}

closeBtn.addEventListener('click', closeNav)
</script>

</body>
</html>

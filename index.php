<?php
// Include the database connection
include 'config.php';

// Query to fetch doctors from the database
$query = "SELECT doctor_id, full_name, specialty, degree, bio, profile_photo FROM doctors";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello Dr.</title>

    <!-- CSS Links -->
    <link rel="stylesheet" href="./css/style.css">


    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;800;900&display=swap" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="./images/favicon.png">

    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
    </style>
</head>

<body>

    <!-- Header / Navbar -->
    <?php include 'header.php'; ?>


    <!-- Hero Section -->
    <section class="hero">
        <div class="container hero__container">
            <h1>Welcome to Hello Dr.</h1>
            <p>Your trusted partner for virtual healthcare services, connecting you with certified doctors anytime, anywhere.</p>
            <a href="doctor_lists" class="btn btn-primary">Book an Appointment</a>
        </div>
        <div class="header__right">
            <div class="header__right-imge">
                <img src="https://raw.githubusercontent.com/kawshik-ornob8/Hello-Dr/e972851db1151844e0a38b7d7065872edb4a2f55/images/header.svg">
            </div>
    </section>


    <!--=====CATEGORIES======-->
    <section class="categories">
        <div class="container categories__container">
            <div class="categories__left">
                <h1>Categories</h1>
                <p>
                    Talk to a doctor within minutes. Our qualified network of doctors is available 24/7. You can get a consultation and prescription whenever you need.
                </p>
                <a href="doctor/profile-card" class="btn">Find Doctor</a>
            </div>
            <div class="categories__right">
                <article class="category">
                    <span class="category__icon"><i class="uil uil-user-md"></i></span>
                    <h5>Live Video Consultation</h5>
                    <p>
                        Instant video consultation now or schedule a future appointment
                    </p>
                </article>

                <article class="category">
                    <span class="category__icon"><i class="uil uil-heart-medical"></i></span>
                    <h5>Health Tipes</h5>
                    <p>Be Happy, Be Healthy</p>
                </article>

                <article class="category">
                    <span class="category__icon"><i class="uil uil-ambulance"></i></span>
                    <h5>24/7 Ambulance</h5>
                    <p>Work or school? Are you just after a quick consultation</p>
                </article>

                <article class="category">
                    <span class="category__icon"><i class="uil uil-medkit"></i></span>
                    <h5>Diagnostic at your doorstep</h5>
                    <p>
                        Get tested in few hours at home & get report in the app within 24 hours.
                    </p>
                </article>

                <article class="category">
                    <span class="category__icon"><i class="uil uil-capsule"></i></span>
                    <h5>Order Medicine Online</h5>
                    <p>
                        Order easily and get the medicine in 1 hour
                    </p>
                </article>

                <article class="category">
                    <span class="category__icon"><i class="uil uil-package"></i></span>
                    <h5>Healthcare Packages</h5>
                    <p>
                        Consultations, hospital care, insurance & more
                    </p>
                </article>
            </div>
        </div>
    </section>
    <!--=====End OF CATEGORIES======-->
    <!-- Doctors Section -->
    <section class="doctors">
        <div class="container doctors__container">
            <h2>Top Specialist Doctor</h2>
            <div class="doctor__list">
                <?php
                // Query to fetch top 6 doctors sorted by average rating
                $query = "
                SELECT doctors.doctor_id, doctors.full_name, doctors.degree, doctors.specialty, doctors.bio, doctors.profile_photo, 
                       IFNULL(AVG(reviews.rating), 0) AS average_rating
                FROM doctors
                LEFT JOIN reviews ON doctors.doctor_id = reviews.doctor_id
                GROUP BY doctors.doctor_id
                ORDER BY average_rating DESC
                LIMIT 6
            ";
                $result = $conn->query($query);

                // Loop through the results and display each doctor
                while ($doctor = $result->fetch_assoc()):
                ?>
                    <article class="doctor">
                        <!-- Display profile photo -->
                        <img src="doctor/images/<?php echo $doctor['doctor_id']; ?>.<?php echo htmlspecialchars(pathinfo($doctor['profile_photo'], PATHINFO_EXTENSION)); ?>"
                            alt="Profile photo of Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>"
                            loading="lazy">

                        <!-- Display full name -->
                        <h4> <?php echo htmlspecialchars($doctor['full_name']); ?></h4>

                        <!-- Display specialty and degree -->
                        <p class="doctor__degree"><?php echo htmlspecialchars($doctor['degree']); ?>, Specialist in <?php echo htmlspecialchars($doctor['specialty']); ?></p>

                        <!-- Display average rating -->
                        <div class="doctor__rating">
                        <p style="color: black; font-weight: bold;">Average Rating</p>
                            <?php
                            $average_rating = round($doctor['average_rating'], 1); // Round to 1 decimal
                            $full_stars = floor($average_rating); // Number of full stars
                            $half_star = ($average_rating - $full_stars) >= 0.5; // Check for a half star
                            $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0); // Remaining empty stars

                            // Display full stars
                            for ($i = 0; $i < $full_stars; $i++) {
                                echo '<span style="color: gold; font-size: 2rem;">&#9733;</span>';
                            }

                            // Display half star
                            if ($half_star) {
                                echo '<span style="color: gold; font-size: 2rem;">&#9734;</span>';
                            }

                            // Display empty stars
                            for ($i = 0; $i < $empty_stars; $i++) {
                                echo '<span style="color: #ccc; font-size: 2rem;">&#9733;</span>';
                            }
                            ?>
                        </div>


                        <!-- Link to consultation page -->
                        <a href="appointment?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-primary">Consult Now</a>

                        <!-- Link to send message page -->
                        <a href="send_message?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-primary">Send Message</a>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <!-- End Doctors Section -->


    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container testimonials__container">
            <h2>What Our Patients Say</h2>
            <article class="testimonial">
                <p>"Hello Dr. made healthcare easy and accessible. The doctors are knowledgeable and caring."</p>
                <h4>- Washik Wail</h4>
            </article>
            <article class="testimonial">
                <p>"I received excellent advice for my condition. I highly recommend their services!"</p>
                <h4>- Md Masud Rana</h4>
            </article>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- JavaScript Files -->
    <script src="main.js"></script>

</body>

</html>
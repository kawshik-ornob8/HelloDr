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
    <title>Hello Dr. - Home</title>

    <!-- CSS Links -->
    <link rel="stylesheet" href="./css/style.css">
    
    
    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;800;900&display=swap" rel="stylesheet">
    
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
        <a href="doctor lists.php" class="btn btn-primary">Book an Appointment</a>
    </div>
    <div class="header__right">
            <div class="header__right-imge">
                <img src="https://raw.githubusercontent.com/kawshik-ornob8/Hello-Dr/e972851db1151844e0a38b7d7065872edb4a2f55/images/header.svg">
            </div>
</section>

<!-- Services Section -->
<section class="services">
    <div class="container services__container">
        <h2>Our Services</h2>
        <p>We offer a range of healthcare services to support your wellbeing.</p>
        <div class="service__list">
            <article class="service">
                <h3>Online Consultation</h3>
                <p>Get advice from experienced doctors through video consultations.</p>
            </article>
            <article class="service">
                <h3>Health Check-ups</h3>
                <p>Schedule virtual check-ups to stay on top of your health.</p>
            </article>
            <article class="service">
                <h3>Specialized Care</h3>
                <p>Access specialists for conditions like dermatology, adolescent medicine, and more.</p>
            </article>
        </div>
    </div>
</section>

<!-- Doctors Section -->
<section class="doctors">
    <div class="container doctors__container">
        <h2>Meet Our Top Doctors</h2>
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
                    <img src="doctor info/images/<?php echo $doctor['doctor_id']; ?>.<?php echo htmlspecialchars(pathinfo($doctor['profile_photo'], PATHINFO_EXTENSION)); ?>" 
                         alt="Profile photo of Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>" 
                         loading="lazy" >

                    <!-- Display full name -->
                    <h4>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h4>
                    
                    <!-- Display specialty and degree -->
                    <p class="doctor__degree"><?php echo htmlspecialchars($doctor['degree']); ?>, Specialist in <?php echo htmlspecialchars($doctor['specialty']); ?></p>
                    
                    <!-- Display bio -->
                    <p class="doctor__bio"><?php echo htmlspecialchars($doctor['bio']); ?></p>

                    <!-- Display average rating -->
                    <p class="doctor__rating">Average Rating: <?php echo number_format($doctor['average_rating'], 1); ?> / 5</p>
                    
                    <!-- Link to consultation page -->
                    <a href="appointment.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-primary">Consult Now</a>
                    
                    <!-- Link to send message page -->
                    <a href="send_message.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="btn btn-secondary">Send Message</a>
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
            <h4>- Patient A</h4>
        </article>
        <article class="testimonial">
            <p>"I received excellent advice for my condition. I highly recommend their services!"</p>
            <h4>- Patient B</h4>
        </article>
    </div>
</section>

<!-- Footer -->
<?php include 'footer.php'; ?>

<!-- JavaScript Files -->
<script src="main.js"></script>

</body>
</html>

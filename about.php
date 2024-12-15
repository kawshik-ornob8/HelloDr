<?php
include('config.php'); // Include the database configuration file

// Fetch team members from the database
$team_result = $conn->query("SELECT * FROM team"); // Modify table name if needed
if (!$team_result) {
    die("Error fetching team members: " . $conn->error);
}

// Use the correct column name `appointment_date` in the query
$sql = "SELECT COUNT(*) as appointment_count 
        FROM appointments 
        WHERE appointment_date = CURDATE()"; // CURDATE() for today’s date

$result = $conn->query($sql);

$appointmentCount = 0; // Default to 0 in case no results are found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $appointmentCount = $row['appointment_count'];
}


// Query to count the total number of patients
$sql = "SELECT COUNT(*) as patient_count FROM patients";

$result = $conn->query($sql);

$patientCount = 0; // Default to 0 in case no results are found
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $patientCount = $row['patient_count'];
}

$conn->close();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Hello Dr.</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/about.css">

    <!-- ICONSCOUT CDN -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <!-- Google Fonts (Montserrat) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;0,800;0,900;1,600&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url("./images/bg-texture.png");
        }
    </style>
    <link rel="icon" href="./images/favicon.png" type="image/png">
    <link rel="shortcut icon" href="./images/favicon.png" type="image/x-icon">
</head>
<body>
    <!-- Header / Navbar -->
    <?php include 'header.php'; ?>

    <!-- =====Achievements====== -->
    <section class="about__achievements">
        <div class="container about__achievements-container">
            <div class="about__achievements-left">
                <img src="./images/about achievements.svg" alt="Achievements Image">
            </div>
            <div class="about__achievements-right">
                <h1>Achievements</h1>
                <ul>
                    <li>Hello Dr. is an online medical service targeting emerging markets that are rapidly digitising. Our mission is to improve the health and wellbeing of the populations we serve. We have developed accessible and affordable telehealth services that aim to support the public health system, research, and clinical trials.</li>
                </ul>
                <div class="achievements__cards">
                    <article class="achievement__card">
                        <span class="achievement__icon">
                            <i class="uil uil-video"></i>
                        </span>
                        <h3><?php echo $appointmentCount; ?>+</h3>
                        <p>Appointment Per Day</p>
                    </article>
                    <article class="achievement__card">
                        <span class="achievement__icon">
                            <i class="uil uil-users-alt"></i>
                        </span>
                        <h3><?php echo $patientCount; ?>+</h3>
                        <p>Patient</p>
                    </article>
                    <article class="achievement__card">
                        <span class="achievement__icon">
                            <i class="uil uil-trophy"></i>
                        </span>
                        <h3>20+</h3>
                        <p>Awards</p>
                    </article>
                </div>
            </div>
        </div>
    </section>
    <!-- =====End of Achievements====== -->

<!-- =====Team====== -->
<section class="team">
    <h2>Meet Our Team</h2>
    <div class="container team__container">
        <?php if ($team_result->num_rows > 0): ?>
            <?php while ($team_member = $team_result->fetch_assoc()) { ?>
                <article class="team__member">
                    <div class="team__member-image">
                        <img src="admin/<?php echo htmlspecialchars($team_member['image_url']); ?>" alt="<?php echo htmlspecialchars($team_member['id']); ?>">
                    </div>
                    <div class="team__member-info">
                        <h4><?php echo htmlspecialchars($team_member['name']); ?></h4>
                        <p><?php echo htmlspecialchars($team_member['position']); ?> of Hello Dr.</p>
                    </div>
                    <div class="team__member-socials">
                        <?php if (!empty($team_member['instagram_url'])) { ?>
                            <a href="<?php echo htmlspecialchars($team_member['instagram_url']); ?>" target="_blank"><i class="uil uil-instagram"></i></a>
                        <?php } ?>
                        <?php if (!empty($team_member['facebook_url'])) { ?>
                            <a href="<?php echo htmlspecialchars($team_member['facebook_url']); ?>" target="_blank"><i class="uil uil-facebook-f"></i></a>
                        <?php } ?>
                        <?php if (!empty($team_member['twitter_url'])) { ?>
                            <a href="<?php echo htmlspecialchars($team_member['twitter_url']); ?>" target="_blank"><i class="uil uil-twitter-alt"></i></a>
                        <?php } ?>
                    </div>
                </article>
            <?php } ?>
        <?php else: ?>
            <p>No team members available at the moment.</p>
        <?php endif; ?>
    </div>
</section>
<!-- =====End of Team====== -->


    <!-- Footer -->
    <?php include 'footer.php'; ?>
    <script src="./main.js"></script>
</body>
</html>

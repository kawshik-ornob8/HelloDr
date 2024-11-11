<?php
// Include the database connection
require_once('../config.php');

// Check if doctor ID is set in the session
if (!isset($_SESSION['doctor_id'])) {
    die('Doctor ID not found.');
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch the doctor's details from the database
$query = "SELECT * FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Set default values for counts if they are not set
$doctor['patients_count'] = $doctor['patients_count'] ?? 0;
$doctor['appointments_count'] = $doctor['appointments_count'] ?? 0;
$doctor['followers_count'] = $doctor['followers_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Doctor Profile Card</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/profile-card.css">
</head>
<body>
    <section class="profile_bg">
        <div class="card_container">
            <div class="card">
                <div class="tag">Doctor</div>
                <div class="profile_image">
                    <img src="images/<?php echo htmlspecialchars($doctor['profile_photo']); ?>" alt="Profile Image" />
                </div>
                <div class="profile_name">
                    <h1>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h1>
                </div>
                <div class="profile_position">
                    <h3><?php echo htmlspecialchars($doctor['specialty']); ?></h3>
                </div>
                <div class="profile_description">
                    <h6>
                        <?php echo nl2br(htmlspecialchars($doctor['bio'])); ?>
                    </h6>
                </div>
                <div class="profile_projects_following_followers_container">
                    <div class="profile_projects_count">
                        <div class="profile_projects_count_inner">
                            <p class="project_title">Patients</p>
                            <p class="project_count"><?php echo htmlspecialchars($doctor['patients_count']); ?></p>
                        </div>
                    </div>
                    <div class="profile_following">
                        <div class="profile_following_inner">
                            <p class="project_title">Appointments</p>
                            <p class="project_count"><?php echo htmlspecialchars($doctor['appointments_count']); ?></p>
                        </div>
                    </div>
                    <div class="profile_followers">
                        <div class="profile_followers_inner">
                            <p class="project_title">Followers</p>
                            <p class="project_count"><?php echo htmlspecialchars($doctor['followers_count']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="profile_btn_group">
                    <h4 class="follow_btn">
                        <a href="#">Follow</a>
                    </h4>
                    <h4 class="hire_btn">
                        <a href="#">Book Appointment</a>
                    </h4>
                </div>
            </div>
        </div>
    </section>
</body>
</html>

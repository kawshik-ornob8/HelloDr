<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profiles</title>
    <link rel="stylesheet" href="css/profile-card.css">
    
</head>
<body>

<?php
session_start();
include('../config.php');

// Query to fetch all doctor data
$sql = "SELECT * FROM doctors";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($doctor = $result->fetch_assoc()) {
        $doctor_id = $doctor['doctor_id'];
        $full_name = htmlspecialchars($doctor['full_name']);
        $specialty = "Specialty: " .htmlspecialchars($doctor['specialty']);
        $faculty = "Degree: " . htmlspecialchars($doctor['degree']); // Example static data
        $department = "Bio: " . htmlspecialchars($doctor['bio']); // Example static data
        $email = htmlspecialchars($doctor['email']);
        $phone_number = htmlspecialchars($doctor['phone_number']);
        
        // Check for profile photo in various formats
        $extensions = ['jpg', 'jpeg', 'png'];
        $profile_photo_path = "images/default.jpg"; // Default image if none found

        foreach ($extensions as $ext) {
            $possible_path = "images/{$doctor_id}.$ext";
            if (file_exists($possible_path)) {
                $profile_photo_path = $possible_path;
                break;
            }
        }

        // Fetch average rating and total reviews for the doctor
        $rating_query = "SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM reviews WHERE doctor_id = ?";
        $stmt = $conn->prepare($rating_query);
        $stmt->bind_param("i", $doctor_id);
        $stmt->execute();
        $rating_result = $stmt->get_result();
        $rating_data = $rating_result->fetch_assoc();

        // Set the rating and review count
        $rating = !empty($rating_data['avg_rating']) ? round($rating_data['avg_rating'], 1) : 0;
        $reviews_count = $rating_data['review_count'];
        $stmt->close();
?>

<div class="profile-card">
    <div class="profile-header">
        <img src="<?php echo $profile_photo_path; ?>" alt="Profile Picture" class="profile-pic">
        <div class="profile-info">
            <h2><?php echo $full_name; ?></h2>
            <p class="role"><?php echo $specialty; ?></p>
            <p class="faculty"><?php echo $faculty; ?></p>
            <p class="department"><?php echo $department; ?></p>
        </div>
    </div>
    
    <div class="rating-section">
        <span class="rating"><?php echo $rating; ?></span>
        <div class="rating-bar">
            <div class="rating-fill" style="width: <?php echo $rating * 20; ?>%;"></div>
        </div>
        <p class="reviews"><?php echo $reviews_count; ?> reviews</p>
    </div>

    <div class="contact-info">
        <p><span class="icon">✉️</span> <?php echo $email; ?></p>
        <p><span class="icon">📞</span> <?php echo $phone_number; ?></p>
    </div>

    <div class="action-buttons">
        <button class="review-btn" onclick="window.location.href='../add_review.php?doctor_id=<?php echo $doctor_id; ?>'">Add Your Review</button>
        <button class="view-profile-btn" onclick="window.location.href='view_profile.php?doctor_id=<?php echo $doctor_id; ?>'">View Profile</button>
        <button class="review-btn" onclick="window.location.href='../appointment.php?doctor_id=<?php echo $doctor_id; ?>'">Appointment</button>
    </div>
</div>

<?php
    }
} else {
    echo "<p>No doctors found.</p>";
}

// Close the connection at the end
$conn->close();
?>

</body>
</html>

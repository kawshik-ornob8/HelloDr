<?php
session_start();
// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: ../login');
    exit();
}

// Include database connection
require_once '../config.php';

// Get doctor information from the database
$doctor_id = $_SESSION['doctor_id'];
$sql = "SELECT fullname, dob, phone, email, reg_id, specialty, degree, bio, profile_photo FROM doctors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="profile-container">
        <h2>Doctor Profile</h2>

        <!-- Display profile photo if available -->
        <?php if (!empty($doctor['profile_photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($doctor['profile_photo']); ?>" alt="Profile Photo" class="profile-photo">
        <?php else: ?>
            <p>No profile photo available.</p>
        <?php endif; ?>

        <!-- Profile Information -->
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($doctor['fullname']); ?></p>
        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($doctor['dob']); ?></p>
        <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($doctor['phone']); ?></p>
        <p><strong>Email Address:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
        <p><strong>Doctor Registration ID:</strong> <?php echo htmlspecialchars($doctor['reg_id']); ?></p>
        <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialty']); ?></p>
        <p><strong>Degree:</strong> <?php echo htmlspecialchars($doctor['degree']); ?></p>
        <p><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($doctor['bio'])); ?></p>

        <!-- Edit Profile Form -->
        <h3>Edit Profile</h3>
        <form action="update_profile" method="POST" enctype="multipart/form-data">
            <label for="specialty">Specialty:</label>
            <input type="text" name="specialty" id="specialty" value="<?php echo htmlspecialchars($doctor['specialty']); ?>" required>

            <label for="degree">Degree:</label>
            <input type="text" name="degree" id="degree" value="<?php echo htmlspecialchars($doctor['degree']); ?>" required>

            <label for="bio">Bio:</label>
            <textarea name="bio" id="bio" rows="5" required><?php echo htmlspecialchars($doctor['bio']); ?></textarea>

            <label for="profile_photo">Profile Photo:</label>
            <input type="file" name="profile_photo" id="profile_photo">

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <!-- Get Appointment Option -->
        <h3>Appointment</h3>
        <a href="book_appointment?doctor_id=<?php echo $doctor_id; ?>" class="btn btn-primary">Get Appointment</a>

        <a href="logout">Logout</a>
    </div>
</body>
</html>

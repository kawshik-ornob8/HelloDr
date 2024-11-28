<?php
session_start();
include('../config.php');

// Redirect to login if not logged in as a doctor
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit;
}

// Fetch doctor information
$doctor_id = $_SESSION['doctor_id'];
$stmt = $conn->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Count today's total appointments
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) AS total_appointments FROM appointments WHERE doctor_id = ? AND appointment_date = ?");
$stmt->bind_param("is", $doctor_id, $today);
$stmt->execute();
$total_appointments = $stmt->get_result()->fetch_assoc()['total_appointments'] ?? 0;
$stmt->close();

// Count unread messages
$stmt = $conn->prepare("SELECT COUNT(*) AS unread_messages FROM messages WHERE doctor_id = ? AND is_read = 0");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$unread_messages = $stmt->get_result()->fetch_assoc()['unread_messages'] ?? 0;
$stmt->close();

// Fetch active calls for the doctor
$stmt = $conn->prepare("SELECT ac.*, p.full_name AS patient_name 
                        FROM active_calls ac 
                        JOIN patients p ON ac.patient_id = p.patient_id 
                        WHERE ac.doctor_id = ?");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$active_calls = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="css/doctor_dashboard.css">
</head>
<body>

<div class="dashboard-container">
    <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h2>

    <!-- Logout Button -->
    <a href="doctor_logout.php" class="button logout-button">Logout</a>

    <!-- Profile Information -->
    <div class="section profile-info">
        <h3>Profile Information</h3>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($doctor['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($doctor['phone_number']); ?></p>
        <p><strong>Specialty:</strong> <?php echo htmlspecialchars($doctor['specialty']); ?></p>
        <p><strong>Degree:</strong> <?php echo htmlspecialchars($doctor['degree']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($doctor['bio']); ?></p>
    </div>

    <!-- Today's Appointments Section -->
    <div class="section appointments">
        <h3>Today's Total Appointments</h3>
        <?php if ($total_appointments > 0): ?>
            <p><?php echo $total_appointments; ?> appointment(s) scheduled today.</p>
        <?php else: ?>
            <p>No appointments scheduled for today.</p>
        <?php endif; ?>
        <a href="view_appointments.php" class="button">View Appointment Scheduled</a>
    </div>

    <!-- Messages Section -->
    <div class="section messages">
        <h3>Messages</h3>
        <?php if ($unread_messages > 0): ?>
            <p>You have <?php echo $unread_messages; ?> unread message(s).</p>
            <a href="doctor_conversation.php" class="button">View Messages</a>
        <?php else: ?>
            <p>No unread messages.</p>
        <?php endif; ?>
    </div>

    <!-- Edit Profile Section -->
    <div class="section edit-profile">
        <h3>Edit Profile</h3>
        <a href="doctor_profile_edit.php" class="button">Edit Profile</a>
    </div>
<!-- Video Call Section -->
<div class="section video-call">
        <h3>Video Calls</h3>
        <?php if ($active_calls->num_rows > 0): ?>
            <ul>
                <?php while ($call = $active_calls->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($call['patient_name']); ?>: 
                        <a href="../video_call_room.php?room_id=<?php echo urlencode($call['room_id']); ?>">Join</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No active calls.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>

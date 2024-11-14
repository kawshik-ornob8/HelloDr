<?php
include('config.php');

$doctor_id = $_GET['doctor_id'];
$query = "SELECT * FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    echo "Doctor not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Consult Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></title>
    <link rel="stylesheet" href="css/appointment.css">
    <style>
        /* Base Button Style */
        .button {
            background-color: #8094ff;
            color: #fff;
            padding: 0.8rem 1.6rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 1rem;
            display: inline-block;
            text-decoration: none;
            width: 100%; /* Ensures both buttons are the same width */
            text-align: center;
        }

        .button:hover {
            background-color: #4e73e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Doctor's Profile Information -->
        <img src="doctor info/images/<?php echo $doctor['doctor_id']; ?>.<?php echo htmlspecialchars(pathinfo($doctor['profile_photo'], PATHINFO_EXTENSION)); ?>" 
             alt="Profile photo of Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>" 
             loading="lazy">
        <h2>Consult Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h2>
        <p>Specialty: <?php echo htmlspecialchars($doctor['specialty']); ?></p>
        <p>Bio: <?php echo htmlspecialchars($doctor['bio']); ?></p>

        <!-- Form for requesting appointment or consultation -->
        <form action="request_appointment_action.php" method="post">
            <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
            <label for="appointment_date">Preferred Date:</label>
            <input type="date" name="appointment_date" required>
            <label for="appointment_time">Preferred Time:</label>
            <input type="time" name="appointment_time" required>
            <button type="submit" class="button">Request Appointment</button>
        </form>

        <!-- Back Button -->
        <button onclick="history.back()" class="button" style="margin-top: 10px;">Back</button>
    </div>
</body>
</html>

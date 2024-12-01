<?php
session_start();
include('../config.php');

// Redirect to login if not logged in as a patient
if (!isset($_SESSION['patient_id'])) {
    header("Location: user_login.php");
    exit;
}

$patient_id = $_SESSION['patient_id'];

// Fetch appointments for the logged-in patient where date is not in the past
$stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, d.doctor_id, d.full_name AS doctor_name, a.status
                        FROM appointments a
                        JOIN doctors d ON a.doctor_id = d.doctor_id
                        WHERE a.patient_id = ? AND a.appointment_date >= CURDATE()
                        ORDER BY a.appointment_date, a.appointment_time");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link rel="stylesheet" href="css/view_appointments.css">
</head>
<body>

<h2>My Upcoming Appointments</h2>

<div class="container">
<?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Doctor Name</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <?php if ($row['status'] == 'Approved'): ?>
                        <!-- Check for a 'Paid' flag stored in the session (demo) -->
                        <?php if (!isset($_SESSION['paid'][$row['appointment_id']])): ?>
                            <form method="post" action="">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                <button type="submit" name="pay" class="pay-btn">Pay</button>
                            </form>
                        <?php else: ?>
                            <!-- Video Call Form -->
                            <form action="start_video_call.php" method="POST">
                                <input type="hidden" name="doctor_id" value="<?php echo $row['doctor_id']; ?>">
                                <input type="hidden" name="room_id" value="unique-room-id-<?php echo $row['doctor_id']; ?>">
                                <button type="submit" class="btn btn-secondary">Start Video Call</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>You have no upcoming appointments.</p>
<?php endif; ?>

<a href="user_profile.php" class="button">Back to Dashboard</a>
</div>

</body>
</html>

<?php
// Simulate a payment process
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay'])) {
    $appointment_id = $_POST['appointment_id'];

    // Mark as paid (for demo purposes, we're using session storage)
    $_SESSION['paid'][$appointment_id] = true;

    echo "<script>alert('Payment successful for appointment ID: $appointment_id'); window.location.href = 'patients_view_appointments.php';</script>";
}

$stmt->close();
$conn->close();
?>

<?php
session_start();
include('../config.php');

// Redirect to login if not logged in as a doctor
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login.php");
    exit;
}

$doctor_id = $_SESSION['doctor_id'];

// Fetch appointments for the logged-in doctor where date is not in the past
$stmt = $conn->prepare("SELECT a.appointment_id, a.appointment_date, a.appointment_time, p.full_name, p.mobile_number, p.email, a.status
                        FROM appointments a
                        JOIN patients p ON a.patient_id = p.patient_id
                        WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
                        ORDER BY a.appointment_date, a.appointment_time");
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="css/view_appointments.css">
</head>
<body>

<h2>Scheduled Appointments</h2>

<div class="container">
<?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Patient Name</th>
            <th>Mobile Number</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['mobile_number']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <!-- Approve button only if status is 'Pending' -->
                    <?php if ($row['status'] == 'Pending'): ?>
                        <form action="approve_appointment.php" method="post" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <button type="submit" class="approve-btn">Approve</button>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Delete button only if status is 'Pending' -->
                    <?php if ($row['status'] == 'Pending'): ?>
                        <form action="cancel_appointment.php" method="post" style="display:inline;">
                            <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>No appointments scheduled.</p>
<?php endif; ?>

<a href="doctor_dashboard.php" class="button">Back to Dashboard</a>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

<?php
session_start();
include('../config.php');

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: doctor_login");
    exit;
}

// Fetch appointments and active calls for the logged-in doctor
$doctor_id = $_SESSION['doctor_id'];

$query = "SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, 
                 p.full_name, p.mobile_number, p.email 
          FROM appointments a 
          JOIN patients p ON a.patient_id = p.patient_id 
          WHERE a.doctor_id = ? 
          ORDER BY a.appointment_date, a.appointment_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="css/view_appointments.css">
    <style>
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            z-index: 9999;
            cursor: pointer;
            margin-top: 10px;
        }

        .notification a {
            color: white;
            text-decoration: underline;
        }

        #notification-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column-reverse;
        }

        tr[style="background-color: red;"] {
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <h2>Scheduled Appointments</h2>

    <?php if (isset($_SESSION['success_message'])) : ?>
        <p style="color: green;"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])) : ?>
        <p style="color: red;"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
    <?php endif; ?>

    <div class="container">
        <?php
        $current_date = null;
        foreach ($appointments as $appointment) :
            // If the date changes, close the previous table and start a new one
            if ($current_date !== $appointment['appointment_date']) {
                if ($current_date !== null) {
                    echo '</tbody></table>';
                }
                $current_date = $appointment['appointment_date'];
        ?>
                <h3>Date: <?php echo htmlspecialchars($current_date); ?></h3>
                <table id="appointments-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Patient Name</th>
                            <th>Mobile Number</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php
            }
            ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['mobile_number']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                        <td>
                            <?php if ($appointment['status'] === 'Pending') : ?>
                                <form action="approve_appointment.php" method="post" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <button type="submit" class="approve-btn">Approve</button>
                                </form>
                                <form action="cancel_appointment.php" method="post" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            <?php elseif ($appointment['status'] === 'Approved') : ?>
                                <form action="cancel_appointment.php" method="post" style="display:inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <button type="submit" class="delete-btn">Cancel</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
        <?php endforeach; ?>
        </tbody>
        </table>

        <a href="doctor_dashboard.php" class="button">Back to Dashboard</a>
    </div>

    <div id="notification-container"></div>
</body>

</html>

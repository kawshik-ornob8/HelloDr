<?php
session_start();
include '../config.php'; // Database configuration and connection

// Check if the patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header('Location: ../login'); // Redirect to login page if not logged in
    exit();
}

$patient_id = $_SESSION['patient_id']; // Logged-in patient's ID

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Fetch patient's email from the database
$patient_email_stmt = $conn->prepare("SELECT email FROM patients WHERE patient_id = ?");
$patient_email_stmt->bind_param("i", $patient_id);
$patient_email_stmt->execute();
$patient_email_result = $patient_email_stmt->get_result();
$patient_email = ($patient_email_result->num_rows > 0) ? $patient_email_result->fetch_assoc()['email'] : null;

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $appointment_id = $_POST['appointment_id'];
    $amount = $_POST['payment_amount']; // Payment amount from the form
    $payment_method = "Credit Card"; // Example payment method

    // Fetch the doctor ID for this appointment
    $stmt = $conn->prepare("SELECT doctor_id FROM appointments WHERE appointment_id = ? AND patient_id = ?");
    $stmt->bind_param("ii", $appointment_id, $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor_id = ($result->num_rows > 0) ? $result->fetch_assoc()['doctor_id'] : null;

    if ($doctor_id) {
        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO payments (patient_id, doctor_id, appointment_id, amount, payment_method) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiids", $patient_id, $doctor_id, $appointment_id, $amount, $payment_method);

        if ($stmt->execute()) {
            // Send payment confirmation email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $config['smtp_username'];
                $mail->Password = $config['smtp_password'];
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom($config['smtp_username'], 'Hello Dr.');
                $mail->addAddress($patient_email); // Patient's email from the database

                $mail->Subject = "Payment Confirmation";
                $mail->Body = "Your payment of $amount for appointment ID $appointment_id has been successfully received.\n\nThank you for using our service.";

                $mail->send();
            } catch (Exception $e) {
                echo "<script>alert('Payment successful, but email confirmation failed.');</script>";
            }

            echo "<script>alert('Payment successful! A confirmation email has been sent.'); window.location.href = 'patients_view_appointments.php';</script>";
            exit();
        } else {
            echo "<script>alert('Payment failed. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid appointment ID or access denied.');</script>";
    }
}

// Fetch upcoming appointments for the logged-in patient
$stmt = $conn->prepare("
    SELECT a.appointment_id, a.appointment_date, a.appointment_time, a.status, 
           d.full_name AS doctor_name, d.doctor_fee, a.doctor_id 
    FROM appointments AS a 
    INNER JOIN doctors AS d ON a.doctor_id = d.doctor_id 
    WHERE a.patient_id = ? AND a.appointment_date >= CURDATE()
    ORDER BY a.appointment_date, a.appointment_time
");

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
    <style>
        /* Add some basic styling for better visibility */
        .container {
            width: 90%;
            margin: auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #dddddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        .pay-btn, .btn-secondary {
            padding: 8px 12px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn-secondary {
            background-color: #008CBA;
        }

        .button {
            padding: 10px 15px;
            background-color: #555555;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .button:hover, .pay-btn:hover, .btn-secondary:hover {
            opacity: 0.8;
        }
    </style>
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
                    <?php
                    // Define appointment start and end times
                    $appointment_datetime_str = $row['appointment_date'] . ' ' . $row['appointment_time'];
                    $appointment_start = new DateTime($appointment_datetime_str);
                    // Define duration (e.g., 30 minutes)
                    $appointment_duration = new DateInterval('PT30M'); // 30 minutes
                    $appointment_end = clone $appointment_start;
                    $appointment_end->add($appointment_duration);

                    // Get current datetime
                    $current_datetime = new DateTime();

                    // Determine the action based on current time
                    $action = '';
                    if ($row['status'] === 'Approved') {
                        // Check if a payment exists for this appointment
                        $payment_stmt = $conn->prepare("SELECT * FROM payments WHERE appointment_id = ?");
                        $payment_stmt->bind_param("i", $row['appointment_id']);
                        $payment_stmt->execute();
                        $payment_result = $payment_stmt->get_result();

                        if ($payment_result->num_rows == 0) {
                            // Show the Pay button if no payment is found
                            $action = '
                                <form method="post" action="">
                                    <input type="hidden" name="appointment_id" value="' . htmlspecialchars($row['appointment_id']) . '">
                                    <input type="hidden" name="payment_amount" value="' . htmlspecialchars($row['doctor_fee']) . '">
                                    <button type="submit" name="confirm_payment" class="pay-btn">
                                        Pay ৳' . htmlspecialchars($row['doctor_fee']) . '
                                    </button>
                                </form>
                            ';
                        } else {
                            // Payment exists, determine what to show based on current time
                            if ($current_datetime < $appointment_start) {
                                // Before appointment start time
                                $action = '<span>Wait for your appointment time.</span>';
                            } elseif ($current_datetime >= $appointment_start && $current_datetime <= $appointment_end) {
                                // During appointment time window
                                $action = '
                                    <form action="start_video_call.php" method="POST">
                                        <input type="hidden" name="doctor_id" value="' . htmlspecialchars($row['doctor_id']) . '">
                                        <input type="hidden" name="room_id" value="room-' . htmlspecialchars($row['doctor_id']) . '-' . htmlspecialchars($row['appointment_id']) . '">
                                        <button type="submit" class="btn-secondary">Start Video Call</button>
                                    </form>
                                ';
                            } else {
                                // After appointment end time
                                $action = '<span>Your appointment time has finished.</span>';
                            }
                        }
                    } else {
                        // Status is not 'Approved'
                        $action = '<span>Wait for Approval</span>';
                    }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo $action; ?></td>
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
$stmt->close();
$conn->close();
?>

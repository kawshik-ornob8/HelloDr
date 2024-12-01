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

// Fetch active video calls for the doctor where status is 'active'
$stmt = $conn->prepare("
    SELECT vr.room_id, vr.patient_id, vr.doctor_id, vr.status, p.full_name AS patient_name
    FROM video_rooms vr
    JOIN patients p ON vr.patient_id = p.patient_id
    WHERE vr.doctor_id = ? AND vr.status = 'active'
");
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
    <style>
        /* Notification styles */
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
    </style>
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
    <p>Active video calls:</p>
    <ul>
        <?php if ($active_calls->num_rows > 0): ?>
            <?php while ($call = $active_calls->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($call['patient_name']); ?>: 
                    <a href="dr_video_call_room.php?room_id=<?php echo urlencode($call['room_id']); ?>">Join</a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>No active video calls at the moment.</li>
        <?php endif; ?>
    </ul>
</div>

</div>

<script>
    const notificationContainer = document.createElement('div');
    notificationContainer.id = 'notification-container';
    document.body.appendChild(notificationContainer);

    // Fetch active calls from the server
    async function fetchActiveCalls() {
        try {
            const response = await fetch('fetch_active_calls.php');
            if (!response.ok) throw new Error("Failed to fetch active calls");
            
            const calls = await response.json();
            displayNotifications(calls);
        } catch (error) {
            console.error("Error fetching active calls:", error);
        }
    }

    // Display notifications dynamically
    function displayNotifications(calls) {
        const existingRooms = new Set(
            [...document.querySelectorAll('.notification')].map(notification => notification.dataset.roomId)
        );

        calls.forEach(call => {
            if (!existingRooms.has(call.room_id)) {
                const notification = document.createElement('div');
                notification.className = 'notification';
                notification.dataset.roomId = call.room_id;
                notification.innerHTML = `
                    <p><strong>Call with ${call.patient_name}</strong></p>
                    <a href="dr_video_call_room.php?room_id=${encodeURIComponent(call.room_id)}">Join</a>
                `;
                notificationContainer.appendChild(notification);

                setTimeout(() => {
                    notification.remove();
                }, 10000); // Auto-remove after 10 seconds
            }
        });
    }

    // Fetch calls on page load and set up periodic updates
    fetchActiveCalls();
    setInterval(fetchActiveCalls, 10000);
</script>

<script>
async function fetchDashboardData() {
    try {
        const response = await fetch('fetch_dashboard_data.php');
        if (!response.ok) throw new Error('Failed to fetch dashboard data');
        
        const data = await response.json();
        if (data.error) {
            console.error(data.error);
            return;
        }

        // Update total appointments
        const appointmentsSection = document.querySelector('.section.appointments p');
        appointmentsSection.textContent = data.total_appointments > 0
            ? `${data.total_appointments} appointment(s) scheduled today.`
            : 'No appointments scheduled for today.';

        // Update unread messages
        const messagesSection = document.querySelector('.section.messages p');
        if (data.unread_messages > 0) {
            messagesSection.innerHTML = `You have ${data.unread_messages} unread message(s).`;
        } else {
            messagesSection.textContent = 'No unread messages.';
        }

        // Update active calls
        const videoCallSection = document.querySelector('.section.video-call ul');
        videoCallSection.innerHTML = '';
        if (data.active_calls.length > 0) {
            data.active_calls.forEach(call => {
                const listItem = document.createElement('li');
                listItem.innerHTML = `
                    ${call.patient_name}: 
                    <a href="dr_video_call_room.php?room_id=${encodeURIComponent(call.room_id)}">Join</a>
                `;
                videoCallSection.appendChild(listItem);
            });
        } else {
            videoCallSection.innerHTML = '<li>No active video calls at the moment.</li>';
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
    }
}

// Fetch dashboard data periodically
fetchDashboardData();
setInterval(fetchDashboardData, 1000); // Update every 10 seconds
</script>


</body>
</html>

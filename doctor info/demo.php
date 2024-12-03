<?php
// Include configuration
include_once '../config.php';

// Start a session (if not already started)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch logged-in doctor details (example: replace with session-based login)
if (!isset($_SESSION['doctor_id'])) {
    die("Unauthorized access. Please log in.");
}
$doctor_id = $_SESSION['doctor_id'];

// Fetch doctor details
$doctor_query = "SELECT * FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($doctor_query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor_result = $stmt->get_result()->fetch_assoc();

// Fetch dashboard stats
$today_date = date('Y-m-d');

// Total Patients
$total_patients_query = "SELECT COUNT(DISTINCT patient_id) AS total_patients FROM appointments WHERE doctor_id = ?";
$total_patients_stmt = $conn->prepare($total_patients_query);
$total_patients_stmt->bind_param("i", $doctor_id);
$total_patients_stmt->execute();
$total_patients_count = $total_patients_stmt->get_result()->fetch_assoc()['total_patients'];

// Today's Patients
$today_patients_query = "SELECT COUNT(DISTINCT patient_id) AS today_patients FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status = 'Approved'";
$today_patients_stmt = $conn->prepare($today_patients_query);
$today_patients_stmt->bind_param("is", $doctor_id, $today_date);
$today_patients_stmt->execute();
$today_patients_count = $today_patients_stmt->get_result()->fetch_assoc()['today_patients'];

// Today's Appointments
$today_appointments_query = "SELECT COUNT(*) AS today_appointments FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status = 'Approved'";
$today_appointments_stmt = $conn->prepare($today_appointments_query);
$today_appointments_stmt->bind_param("is", $doctor_id, $today_date);
$today_appointments_stmt->execute();
$today_appointments_count = $today_appointments_stmt->get_result()->fetch_assoc()['today_appointments'];

// Today's Appointment List
$appointment_list_query = "SELECT a.appointment_time, p.full_name, p.profile_photo 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.patient_id 
    WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.status = 'Approved'
    ORDER BY a.appointment_time ASC";
$appointment_list_stmt = $conn->prepare($appointment_list_query);
$appointment_list_stmt->bind_param("is", $doctor_id, $today_date);
$appointment_list_stmt->execute();
$appointment_list_result = $appointment_list_stmt->get_result();

// Fetch current time
$current_time = date('H:i:s');

// Fetch next patient details
$next_patient_query = "SELECT a.appointment_time, p.patient_id, p.full_name, p.date_of_birth, p.sex, 
    p.profile_photo, p.reg_date, 
    (SELECT MAX(appointment_date) FROM appointments WHERE patient_id = p.patient_id AND doctor_id = ?) AS last_appointment 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.patient_id 
    WHERE a.doctor_id = ? AND a.appointment_date = ? AND a.appointment_time > ? AND a.status = 'Approved'
    ORDER BY a.appointment_time ASC LIMIT 1";
$next_patient_stmt = $conn->prepare($next_patient_query);
$next_patient_stmt->bind_param("iiss", $doctor_id, $doctor_id, $today_date, $current_time);
$next_patient_stmt->execute();
$next_patient = $next_patient_stmt->get_result()->fetch_assoc();

// Count unread messages
$unread_messages_query = "SELECT COUNT(*) AS unread_count 
    FROM messages 
    WHERE doctor_id = ? AND is_read = 0";
$unread_messages_stmt = $conn->prepare($unread_messages_query);
$unread_messages_stmt->bind_param("i", $doctor_id);
$unread_messages_stmt->execute();
$unread_messages_count = $unread_messages_stmt->get_result()->fetch_assoc()['unread_count'];
$unread_messages_stmt->close();


// Close prepared statements
$stmt->close();
$total_patients_stmt->close();
$today_patients_stmt->close();
$today_appointments_stmt->close();
$appointment_list_stmt->close();
$next_patient_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">
<div class="flex">
    <!-- Sidebar -->
    <div class="w-1/5 bg-white h-screen p-5">
        <div class="text-center mb-5">
            <img src="<?= htmlspecialchars($doctor_result['profile_photo']); ?>" alt="Doctor's profile picture" 
                class="rounded-full mx-auto mb-3" width="100" height="100">
            <h2 class="text-blue-600 font-bold"><?= htmlspecialchars($doctor_result['full_name']); ?></h2>
            <p class="text-gray-500"><?= htmlspecialchars($doctor_result['specialty']); ?></p>
        </div>
        <nav>
            <ul>
                <li class="mb-4"><a class="flex items-center text-blue-600" href="#"><i class="fas fa-th-large mr-3"></i>Dashboard</a></li>
                <li class="mb-4"><a class="flex items-center text-gray-600" href="view_appointments.php"><i class="fas fa-calendar-alt mr-3"></i>Appointments</a></li>
                <li class="mb-4"><a class="flex items-center text-gray-600" href="view_profile.php?doctor_id=<?php echo $doctor_id; ?>"><i class="fas fa-user mr-3"></i>Profile</a></li>
                <li class="mb-4"><a class="flex items-center text-gray-600" href="#"><i class="fas fa-sign-out-alt mr-3"></i>Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="w-4/5 p-5">
        <!-- Header -->
        <div class="flex justify-between items-center mb-5">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <div class="flex items-center">
            <a href="doctor_conversation.php" class="relative inline-block">
                <i class="fas fa-envelope text-gray-600 mr-5 text-xl"></i>
                <?php if ($unread_messages_count > 0): ?>
                    <span class="absolute top-0 left-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">
                        <?= htmlspecialchars($unread_messages_count); ?>
                    </span>
                    <?php endif; ?>
                </a>
                <i class="fas fa-bell text-gray-600 mr-5"></i>
                <input type="text" placeholder="Search" class="border rounded-lg p-2">
                <i class="fas fa-bars text-gray-600 ml-5"></i>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-3 gap-5 mb-5">
            <div class="bg-white p-5 rounded-lg shadow">
                <h2 class="text-xl font-bold">Total Patients</h2>
                <p class="text-2xl font-bold text-blue-600"><?= htmlspecialchars($total_patients_count); ?>+</p>
                <p class="text-gray-500">Till Today</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow">
                <h2 class="text-xl font-bold">Today Patients</h2>
                <p class="text-2xl font-bold text-blue-600"><?= htmlspecialchars($today_patients_count); ?></p>
                <p class="text-gray-500"><?= date("d M Y"); ?></p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow">
                <h2 class="text-xl font-bold">Today Appointments</h2>
                <p class="text-2xl font-bold text-blue-600"><?= htmlspecialchars($today_appointments_count); ?></p>
                <p class="text-gray-500"><?= date("d M Y"); ?></p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-5">
            <!-- Today's Appointments -->
            <div class="bg-white p-5 rounded-lg shadow mb-5">
                <h2 class="text-xl font-bold mb-3">Today's Appointments</h2>
                <?php while ($row = $appointment_list_result->fetch_assoc()): ?>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="../user%20info/<?= !empty($row['profile_photo']) ? htmlspecialchars($row['profile_photo']) : 'images/default_patient.jpg'; ?>" 
                                alt="Patient photo" class="rounded-full mr-3" width="50" height="40">
                            <div>
                                <h3 class="text-blue-600 font-bold"><?= htmlspecialchars($row['full_name']); ?></h3>
                                <p class="text-gray-500"><?= htmlspecialchars($row['appointment_time']); ?></p>
                            </div>
                        </div>
                        <?php if ($row['appointment_time'] === $current_time): ?>
                            <span class="text-green-500 font-bold">SMS Ongoing</span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Next Patient Details -->
            <div class="bg-white p-5 rounded-lg shadow mb-5">
                <h2 class="text-xl font-bold mb-3">Next Patient Details</h2>
                <?php if ($next_patient): ?>
                    <div class="flex items-center mb-3">
                        <img src="../user%20info/<?= !empty($next_patient['profile_photo']) ? htmlspecialchars($next_patient['profile_photo']) : 'images/default_patient.jpg'; ?>" 
                            alt="Next patient photo" class="rounded-full mr-3" width="50" height="40">
                        <div>
                            <h3 class="text-blue-600 font-bold"><?= htmlspecialchars($next_patient['full_name']); ?></h3>
                            <p class="text-gray-500">Appointment Time: <?= htmlspecialchars($next_patient['appointment_time']); ?></p>
                            <p class="text-gray-500">Patient ID: <?= htmlspecialchars($next_patient['patient_id']); ?></p>
                            <p class="text-gray-500">Date of Birth: <?= htmlspecialchars($next_patient['date_of_birth']); ?></p>
                            <p class="text-gray-500">Sex: <?= htmlspecialchars($next_patient['sex']); ?></p>
                            <p class="text-gray-500">Last Appointment: <?= htmlspecialchars($next_patient['last_appointment'] ?: 'N/A'); ?></p>
                            <p class="text-gray-500">Registered: <?= htmlspecialchars($next_patient['reg_date']); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No more appointments for today.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>

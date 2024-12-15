<?php
session_start();
include('../config.php');

// Check if the patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: login");
    exit;
}

// Get patient ID from session
$patient_id = intval($_SESSION['patient_id']);

// Fetch patient data
$sql = "SELECT * FROM patients WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f9;
            margin: 0;
            padding: 10px;
        }
        .container {
            max-width: 800px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Profile Card */
        .profile-card {
            background: linear-gradient(to bottom, #0066cc, #3399ff);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 3px solid white;
        }
        .profile-card h2 {
            margin: 0;
            font-size: 18px;
        }
        .profile-card p {
            margin: 5px 0;
        }
        .profile-card .contact-info a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 5px 0;
        }
        .profile-card .profile-btn {
            background: #e0f0ff;
            color: #0066cc;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            width: 200px;
        }
        

        /* Message Section */
        .message-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
        }
        .message-section h3 {
            font-size: 22px;
            color: #0066cc;
            margin-bottom: 10px;
        }
        .message-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            display: none; /* Hide initially */
        }
        .message-item .message-text {
            color: #333;
            font-size: 14px;
        }
        .message-item .message-status {
            color: #666;
            font-size: 12px;
            text-align: right;
        }
        .view-conversation-btn {
            cursor: pointer;
            color: #0066cc;
            text-decoration: none;
        }
        .view-all-btn {
            background: #0066cc;
            color: white;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        

        /* Responsive styles */
        @media (min-width: 600px) {
            .container {
                flex-direction: row;
                gap: 20px;
            }
            .profile-card {
                width: 250px;
            }
            .message-section {
                flex-grow: 1;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        $full_name = htmlspecialchars($patient['full_name']);
        $date_of_birth = htmlspecialchars($patient['date_of_birth']);
        $sex = htmlspecialchars($patient['sex']);
        $email = htmlspecialchars($patient['email']);
        $mobile_number = htmlspecialchars($patient['mobile_number']);
        $profile_photo_path = "images/default_patient.jpg";

        // Check for profile photo
        $extensions = ['jpg', 'jpeg', 'png'];
        foreach ($extensions as $ext) {
            $possible_path = "images/{$patient_id}.$ext";
            if (file_exists($possible_path)) {
                $profile_photo_path = $possible_path;
                break;
            }
        }
        ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <img src="<?php echo $profile_photo_path; ?>" alt="Profile Picture">
            <h2><?php echo $full_name; ?></h2>
            <p>Date of Birth: <?php echo $date_of_birth; ?></p>
            <p>Sex: <?php echo $sex; ?></p>
            <div class="contact-info">
                <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
                <a href="tel:<?php echo $mobile_number; ?>"><?php echo $mobile_number; ?></a>
            </div>
            <a href="patients_view_appointments" class="profile-btn">View Appointments</a>
            <a href="edit_user_profile" class="profile-btn">Edit Profile</a>
            <a href="../index" class="profile-btn">Back to Dashboard</a>
        </div>

        <!-- Message Section -->
        <div class="message-section">
            <h3>Messages</h3>

            <?php
            // Fetch all messages with unread messages first
            $message_sql = "SELECT m.message, m.is_read, m.doctor_id, m.created_at, d.full_name AS doctor_name 
                            FROM messages m 
                            JOIN doctors d ON m.doctor_id = d.doctor_id 
                            WHERE m.patient_id = ? 
                            GROUP BY m.doctor_id 
                            ORDER BY m.is_read ASC, m.created_at DESC"; // Prioritize unread messages
            $message_stmt = $conn->prepare($message_sql);
            $message_stmt->bind_param("i", $patient_id);
            $message_stmt->execute();
            $message_result = $message_stmt->get_result();

            $message_count = 0;
            if ($message_result->num_rows > 0) {
                while ($message = $message_result->fetch_assoc()) {
                    $message_text = htmlspecialchars($message['message']);
                    $is_read = $message['is_read'] ? 'Read' : 'Unread';
                    $doctor_name = htmlspecialchars($message['doctor_name']);
                    $doctor_id = $message['doctor_id'];
                    $message_count++;
                    ?>
                    <div class="message-item <?php echo $message_count <= 3 ? 'visible' : ''; ?>">
                        <p><strong><span class="view-conversation-btn" data-doctor-id="<?php echo $doctor_id; ?>"><?php echo $doctor_name; ?></span></strong></p>
                        <p class="message-text"><?php echo $message_text; ?></p>
                        <p class="message-status">Status: <?php echo $is_read; ?></p>
                    </div>
                    <?php
                }
                echo '<div class="view-all-btn" id="viewAllMessages">View All Messages</div>';
            } else {
                echo "<p>No messages sent.</p>";
            }

            $message_stmt->close();
            ?>
        </div>

        <?php
    } else {
        echo "<p>Patient not found.</p>";
    }

    // Close connection
    $stmt->close();
    $conn->close();
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Open conversation when doctor name is clicked
        document.querySelectorAll('.view-conversation-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var doctorId = this.getAttribute('data-doctor-id');
                window.location.href = '../conversation?doctor_id=' + doctorId;
            });
        });

        // View all messages functionality
        const viewAllBtn = document.getElementById('viewAllMessages');
        const messageItems = document.querySelectorAll('.message-item');

        // Initially show only the first 3 messages
        messageItems.forEach((item, index) => {
            item.style.display = index < 3 ? 'block' : 'none';
        });

        let showAll = false;

        viewAllBtn.addEventListener('click', function () {
            showAll = !showAll;
            messageItems.forEach((item, index) => {
                item.style.display = showAll || index < 3 ? 'block' : 'none';
            });
            viewAllBtn.textContent = showAll ? 'Show Less' : 'View All Messages';
        });
    });
</script>

</body>
</html>

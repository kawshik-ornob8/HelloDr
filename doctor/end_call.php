<?php
session_start();
require '../config.php';

// Handle room_id retrieval
$roomID = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $roomID = $data['room_id'] ?? null;
} elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $roomID = $_GET['room_id'] ?? null;
}

if (!$roomID) {
    die("<h3>Room ID is required.</h3>");
}

// Verify and update room status
$stmt = $conn->prepare("SELECT * FROM video_rooms WHERE room_id = ?");
$stmt->bind_param("s", $roomID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $updateStmt = $conn->prepare("UPDATE video_rooms SET status = 'ended' WHERE room_id = ?");
    $updateStmt->bind_param("s", $roomID);

    if ($updateStmt->execute()) {
        // Call ended successfully
    } else {
        die("<h3>Error updating call status. Please try again later.</h3>");
    }
    $updateStmt->close();
} else {
    die("<h3>Room ID not found.</h3>");
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Ended</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            text-align: center;
            background: white;
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
        }

        button {
            font-size: 16px;
            padding: 12px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-message {
            background-color: #007bff;
            color: white;
        }

        .btn-message:hover {
            background-color: #0056b3;
        }

        .btn-dashboard {
            background-color: #28a745;
            color: white;
        }

        .btn-dashboard:hover {
            background-color: #218838;
        }

        .timer {
            font-size: 14px;
            color: #888;
            margin-top: 20px;
        }
    </style>
    <script>
        let countdown = 10; // Timer countdown in seconds

        function updateTimer() {
            const timerElement = document.getElementById('timer');
            if (countdown > 0) {
                timerElement.innerText = `Redirecting to dashboard in ${countdown--} seconds...`;
            } else {
                window.location.href = 'doctor_dashboard.php';
            }
        }

        setInterval(updateTimer, 1000); // Update timer every second
    </script>
</head>
<body>

<div class="container">
    <h1>Call Ended Successfully</h1>
    <p>Your video consultation has ended. What would you like to do next?</p>
    <div>
        <button class="btn-message" onclick="window.location.href='send_message?patient_id=<?php echo htmlspecialchars($patient_id); ?>'">Send Message</button>

        <button class="btn-dashboard" onclick="window.location.href='doctor_dashboard'">Go to Dashboard</button>
    </div>
    <p class="timer" id="timer">Redirecting to dashboard in 10 seconds...</p>
</div>

</body>
</html>

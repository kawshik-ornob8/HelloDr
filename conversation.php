<?php
session_start();
include('config.php');

// Check if the user is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: user%20info/user_login.php"); // Redirect to login page if not logged in
    exit();
}

// Get patient_id from session and doctor_id from URL parameter
$patient_id = $_SESSION['patient_id'];
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id <= 0) {
    echo "Invalid doctor ID.";
    exit();
}

// Fetch the doctor's name for the title
$doctor_sql = "SELECT full_name FROM doctors WHERE doctor_id = ?";
$doctor_stmt = $conn->prepare($doctor_sql);
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();
$doctor_name = $doctor_result->fetch_assoc()['full_name'] ?? 'Unknown Doctor';
$doctor_stmt->close();

// Fetch the conversation (all messages between the patient and the doctor)
$sql = "SELECT m.message, m.created_at, m.is_read, m.sender
        FROM messages m
        WHERE (m.patient_id = ? AND m.doctor_id = ?)
        OR (m.patient_id = ? AND m.doctor_id = ?)
        ORDER BY m.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $patient_id, $doctor_id, $patient_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark messages as read for the patient (if messages are from doctor)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $update_sql = "UPDATE messages SET is_read = 1 WHERE doctor_id = ? AND patient_id = ? AND is_read = 0";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $doctor_id, $patient_id);
    $update_stmt->execute();
    $update_stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation with Dr. <?php echo htmlspecialchars($doctor_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .message-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        /* Style for messages sent by the doctor */
        .doctor-message {
            background-color: #d4edda; /* Light green */
        }

        /* Style for messages sent by the patient */
        .patient-message {
            background-color: #f9f9f9; /* Default light grey */
        }

        .message-text {
            font-size: 16px;
            color: #333;
        }

        .message-status {
            font-size: 12px;
            color: #666;
            text-align: right;
        }

        form {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
        }

        textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }

        button {
    padding: 10px;
    background-color: #0066cc;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%; /* Makes the button take the full width */
    margin-bottom: 10px;
}

button:hover {
    background-color: #005bb5;
}

.back-button {
    padding: 10px;
    background-color: #888;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    width: 100%; /* Makes the back button take the full width */
    margin-top: 10px; /* Adds some space between the Send Message button and the Back button */
}

.back-button:hover {
    background-color: #555;
}

    </style>
</head>
<body>

<div class="container">
    <h2>Conversation with Dr. <?php echo htmlspecialchars($doctor_name); ?></h2>

    <div id="messagesContainer">
        <!-- Conversation messages will be inserted here -->
        <?php
        if ($result->num_rows > 0) {
            while ($message = $result->fetch_assoc()) {
                $message_text = htmlspecialchars($message['message']);
                $created_at = date("F j, Y, g:i a", strtotime($message['created_at']));
                $is_read = $message['is_read'] ? 'Read' : 'Unread';
                $sender = $message['sender']; // 0 for doctor, 1 for patient

                // Determine message class based on sender
                $message_class = ($sender == 1) ? 'patient-message' : 'doctor-message';
                ?>
                <div class="message-item <?php echo $message_class; ?>">
                    <p class="message-text"><?php echo $message_text; ?></p>
                    <p class="message-status"><?php echo $is_read; ?> | Sent on: <?php echo $created_at; ?></p>
                </div>
                <?php
            }
        } else {
            echo "<p>No messages found in this conversation.</p>";
        }

        $stmt->close();
        ?>
    </div>

    <!-- Form to send a new message to the doctor -->
    <form action="send_message_action.php" method="post" id="messageForm">
        <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id); ?>">
        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">
        <input type="hidden" name="sender" value="1"> <!-- sender = 1 for patient -->
        <textarea name="message" placeholder="Type your message here" required></textarea>
        <button type="submit">Send Message</button>
    </form>

    <!-- Back button -->
    <button class="back-button" onclick="window.history.back();">Back</button>
</div>

<script>
// Function to fetch new messages from the server
function fetchMessages() {
    const doctorId = <?php echo $doctor_id; ?>; // Get doctor_id dynamically from PHP
    fetch(`get_new_messages.php?doctor_id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data) && data.length > 0) {
                // Clear the conversation container to replace with updated messages
                const messagesContainer = document.getElementById('messagesContainer');
                messagesContainer.innerHTML = ''; // Clear previous messages

                // Append new messages to the conversation container
                data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add(message.sender === 1 ? 'patient-message' : 'doctor-message');

                    const messageText = document.createElement('p');
                    messageText.classList.add('message-text');
                    messageText.textContent = message.message;

                    const messageStatus = document.createElement('p');
                    messageStatus.classList.add('message-status');
                    messageStatus.textContent = `${message.is_read} | Sent on: ${message.created_at}`;

                    messageDiv.appendChild(messageText);
                    messageDiv.appendChild(messageStatus);
                    messagesContainer.appendChild(messageDiv);
                });
            }
        })
        .catch(error => console.error('Error fetching new messages:', error));
}

// Call fetchMessages every 5 seconds to check for new messages
setInterval(fetchMessages, 5000);

// Call fetchMessages once when the page loads to load the initial conversation
fetchMessages();

// Event listener for the form submission using fetch API
document.getElementById('messageForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting the usual way

    const formData = new FormData(this);

    fetch('send_message_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // After successful message send, reload the page to show the latest messages
            location.reload(); // This reloads the page and fetches the latest messages
        } else {
            alert('Failed to send message. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});
</script>

</body>
</html>

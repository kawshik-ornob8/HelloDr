<?php
session_start();
include('config.php');

if (!isset($_SESSION['patient_id'])) {
    header("Location: user%20info/user_login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;

if ($doctor_id <= 0) {
    echo "Invalid doctor ID.";
    exit();
}

$doctor_sql = "SELECT full_name FROM doctors WHERE doctor_id = ?";
$doctor_stmt = $conn->prepare($doctor_sql);
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor_result = $doctor_stmt->get_result();
$doctor_name = $doctor_result->fetch_assoc()['full_name'] ?? 'Unknown Doctor';
$doctor_stmt->close();

// Initial fetch of messages
$sql = "SELECT m.message, m.created_at, m.sender
        FROM messages m
        WHERE (m.patient_id = ? AND m.doctor_id = ?)
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $patient_id, $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Dr. <?php echo htmlspecialchars($doctor_name); ?></title>
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
        .message-item {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .doctor-message {
            background-color: #d4edda;
            text-align: left;
        }
        .patient-message {
            background-color: #f9f9f9;
            text-align: right;
        }
        textarea {
            width: 100%;
            height: 50px;
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            background-color: #0066cc;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Chat with Dr. <?php echo htmlspecialchars($doctor_name); ?></h2>
    <div id="messagesContainer" style="height: 400px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
        <?php foreach ($messages as $message): ?>
            <div class="message-item <?php echo $message['sender'] == 1 ? 'patient-message' : 'doctor-message'; ?>">
                <p><?php echo htmlspecialchars($message['message']); ?></p>
                <small><?php echo date("F j, Y, g:i a", strtotime($message['created_at'])); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <form id="messageForm">
        <textarea id="messageInput" name="message" placeholder="Type your message here"></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<script>
    const messagesContainer = document.getElementById('messagesContainer');
    const messageForm = document.getElementById('messageForm');
    const messageInput = document.getElementById('messageInput');
    let lastMessageTimestamp = '<?php echo end($messages)['created_at'] ?? "1970-01-01 00:00:00"; ?>';

    // Fetch new messages and append them
    function fetchMessages() {
        fetch(`get_new_messages.php?doctor_id=<?php echo $doctor_id; ?>&patient_id=<?php echo $patient_id; ?>&last_timestamp=${lastMessageTimestamp}`)
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(message => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = message.sender === 1 ? 'patient-message' : 'doctor-message';

                        const messageText = document.createElement('p');
                        messageText.textContent = message.message;

                        const messageMeta = document.createElement('small');
                        messageMeta.textContent = new Date(message.created_at).toLocaleString();

                        messageDiv.appendChild(messageText);
                        messageDiv.appendChild(messageMeta);
                        messagesContainer.appendChild(messageDiv);
                    });

                    // Update the last timestamp
                    lastMessageTimestamp = data[data.length - 1].created_at;
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            })
            .catch(error => console.error('Error fetching messages:', error));
    }

    // Send a new message
    messageForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const formData = new FormData();
        formData.append('message', messageInput.value);
        formData.append('doctor_id', <?php echo $doctor_id; ?>);
        formData.append('patient_id', <?php echo $patient_id; ?>);
        formData.append('sender', 1); // Sender is the patient

        fetch('send_message_action.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fetchMessages();
                    messageInput.value = '';
                } else {
                    alert('Failed to send message.');
                }
            })
            .catch(error => console.error('Error sending message:', error));
    });

    // Fetch messages every 1 seconds
    setInterval(fetchMessages, 1000);
</script>
</body>
</html>

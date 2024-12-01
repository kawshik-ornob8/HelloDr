<?php
session_start();
include('../config.php');

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit;
}

// Get logged-in doctor ID
$doctor_id = intval($_SESSION['doctor_id']);

// Fetch unique patient list for the logged-in doctor
$patient_sql = "
    SELECT DISTINCT p.patient_id, p.full_name 
    FROM messages m 
    JOIN patients p ON m.patient_id = p.patient_id 
    WHERE m.doctor_id = ?
";
$patient_stmt = $conn->prepare($patient_sql);
$patient_stmt->bind_param("i", $doctor_id);
$patient_stmt->execute();
$patient_result = $patient_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Conversations</title>
    <link rel="stylesheet" href="css/view_appointments.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background-color: #f9fafb;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            gap: 20px;
        }
        .patient-list, .message-section {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .patient-list h3, .message-section h3 {
            margin-bottom: 15px;
            font-size: 1.2em;
            color: #0073e6;
        }
        .patient-list ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .patient-list li {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #ddd;
            color: #333;
            transition: background 0.2s;
        }
        .patient-list li:hover {
            background-color: #f0f8ff;
        }
        .message-section {
            display: flex;
            flex-direction: column;
        }
        .message-item {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #f7f7f7;
            transition: background 0.2s;
        }
        .message-item.unread {
            background-color: #ffe6e6;
            font-weight: bold;
        }
        .message-item:hover {
            background-color: #e6f7ff;
        }
        .message-text {
            margin: 5px 0;
            color: #333;
        }
        .message-meta {
            font-size: 0.9em;
            color: #888;
        }
        .reply-form {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .reply-form textarea {
            flex: 1;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
            resize: vertical;
        }
        .reply-form button {
            padding: 10px 20px;
            background-color: #0073e6;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .reply-form button:hover {
            background-color: #005bb5;
        }
        .image-preview {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Patient List Section -->
    <div class="patient-list">
        <h3>Patients</h3>
        <ul id="patientList">
            <?php
            if ($patient_result->num_rows > 0) {
                while ($patient = $patient_result->fetch_assoc()) {
                    $patient_id = $patient['patient_id'];
                    $patient_name = htmlspecialchars($patient['full_name']);
                    echo "<li data-patient-id='$patient_id'>$patient_name</li>";
                }
            } else {
                echo "<p>No patients found.</p>";
            }
            ?>
        </ul>
    </div>

    <!-- Message Section -->
    <div class="message-section" id="messageSection">
        <h3>Messages</h3>
        <div id="messageList">
            <p>Select a patient to view messages.</p>
        </div>

        <!-- Reply Form -->
        <form class="reply-form" id="replyForm" style="display: none;" enctype="multipart/form-data">
            <textarea id="replyMessage" rows="3" placeholder="Type your reply..."></textarea>
            <input type="file" id="messageImage" accept="image/*">
            <button type="button" onclick="sendMessage()">Send</button>
        </form>
    </div>
</div>

<script>
    let currentPatientId = null;

    document.addEventListener('DOMContentLoaded', function () {
    const patientList = document.getElementById('patientList');
    const messageList = document.getElementById('messageList');
    const replyForm = document.getElementById('replyForm');
    const messageImage = document.getElementById('messageImage');

    // Add click event listener for each patient
    patientList.addEventListener('click', function(event) {
        if (event.target.tagName === 'LI') {
            currentPatientId = event.target.getAttribute('data-patient-id');
            loadMessages(currentPatientId);  // Load messages for the selected patient
            replyForm.style.display = 'flex';
        }
    });

    // Auto-fetch messages for the selected patient every 5 seconds
    setInterval(autoFetchMessages, 0.5); // Fetch every 5 seconds
});

// Function to load messages for a patient
function loadMessages(patientId) {
    fetch(`fetch_messages.php?patient_id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            const messageList = document.getElementById('messageList');
            messageList.innerHTML = '';
            if (data.length === 0) {
                messageList.innerHTML = '<p>No messages found.</p>';
            } else {
                data.forEach(message => {
                    const messageItem = document.createElement('div');
                    messageItem.classList.add('message-item');
                    if (message.is_read === 0) {
                        messageItem.classList.add('unread');
                    }

                    // Check the sender and apply the correct styling
                    if (message.sender === 1) {
                        // Patient message
                        messageItem.style.backgroundColor = '#d4edda'; // Light green for patient
                    } else {
                        // Doctor message (sender = 0)
                        messageItem.style.backgroundColor = '#f7f7f7'; // Light grey for doctor
                    }

                    let messageContent = `<p class="message-text">${message.message}</p>`;
                    if (message.image_path) {
                        messageContent += `<img src="${message.image_path}" class="image-preview" />`;
                    }
                    messageContent += `<p class="message-meta">Sent on: ${message.created_at}</p>`;

                    messageItem.innerHTML = messageContent;
                    messageList.appendChild(messageItem);
                });
            }
        });
}

// Function to auto-fetch messages periodically
function autoFetchMessages() {
    if (currentPatientId) {
        loadMessages(currentPatientId);
    }
}


    function sendMessage() {
        const replyMessage = document.getElementById('replyMessage').value.trim();
        const fileInput = document.getElementById('messageImage');
        const image = fileInput.files[0];

        if (replyMessage === '' && !image) return;

        const formData = new FormData();
        formData.append('patient_id', currentPatientId);
        formData.append('message', replyMessage);
        formData.append('sender', 0);  // sender = 0 for doctor
        if (image) {
            formData.append('image', image);
        }

        fetch('send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages(currentPatientId);
                document.getElementById('replyMessage').value = '';
                fileInput.value = '';  // Clear file input
            } else {
                alert('Error sending message.');
            }
        });
    }
</script>

</body>
</html>

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
    <style>
        /* Updated CSS for a modern look and new form styling */
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
        <form class="reply-form" id="replyForm" style="display: none;">
            <textarea id="replyMessage" rows="3" placeholder="Type your reply..."></textarea>
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

        // Add click event listener for each patient
        patientList.addEventListener('click', function(event) {
            if (event.target.tagName === 'LI') {
                currentPatientId = event.target.getAttribute('data-patient-id');
                loadMessages(currentPatientId);
                replyForm.style.display = 'flex';
            }
        });
    });

    function loadMessages(patientId) {
        fetch(`fetch_messages.php?patient_id=${patientId}`)
            .then(response => response.json())
            .then(data => {
                messageList.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(message => {
                        const messageItem = document.createElement('div');
                        messageItem.classList.add('message-item');

                        // Add unread class if message is not read
                        if (!message.is_read) {
                            messageItem.classList.add('unread');
                        }

                        // Check the sender and apply the correct styling
                        if (message.sender === 1) {
                            messageItem.style.backgroundColor = '#d4edda'; // Light green for patient
                        } else {
                            messageItem.style.backgroundColor = '#f7f7f7'; // Light grey for doctor
                        }

                        messageItem.innerHTML = `
                            <p class="message-text">${message.message}</p>
                            <p class="message-meta">Sent on: ${message.created_at} | Status: ${message.is_read ? 'Read' : 'Unread'}</p>
                        `;
                        messageList.appendChild(messageItem);
                    });
                } else {
                    messageList.innerHTML = '<p>No messages found for this patient.</p>';
                }
            });
    }

    function sendMessage() {
        const replyMessage = document.getElementById('replyMessage').value.trim();

        if (replyMessage === '' || !currentPatientId) return;

        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ patient_id: currentPatientId, message: replyMessage, sender: 0 }) // sender is 0 for doctor
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages(currentPatientId);
                document.getElementById('replyMessage').value = '';
            } else {
                alert('Error sending message.');
            }
        });
    }
</script>

</body>
</html>

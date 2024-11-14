<?php
session_start();

// Assuming doctor_id is passed via session or URL; adapt as necessary
if (isset($_GET['doctor_id'])) {
    $doctor_id = intval($_GET['doctor_id']); // Ensure it's an integer
} elseif (isset($_SESSION['doctor_id'])) {
    $doctor_id = intval($_SESSION['doctor_id']); // Ensure it's an integer
} else {
    die("Doctor ID not specified.");
}

// Assuming patient_id is stored in the session when the patient logs in
if (isset($_SESSION['patient_id'])) {
    $patient_id = intval($_SESSION['patient_id']); // Ensure it's an integer
} else {
    die("Patient ID not found. Please log in.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message</title>
    <link rel="stylesheet" href="css/send_message.css">
</head>
<body>

<div class="container">
    <h2>Send a Message to the Doctor</h2>
    
    <!-- Form for sending a message -->
    <form id="messageForm" action="send_message_action.php" method="post">
        <!-- Hidden inputs to pass doctor_id, patient_id, and sender (patient) to the action file -->
        <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor_id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="sender" value="1"> <!-- sender = 1 for patient -->

        <!-- Message input area -->
        <textarea name="message" placeholder="Type your message here" required></textarea>
        
        <!-- Submit button -->
        <button type="submit">Send Message</button>
    </form>

    <!-- Success message and navigation buttons -->
    <div id="responseMessage" style="display: none;">
        <p>Message successfully sent!</p>
        <button onclick="window.location.href='user info/user_profile.php';">Profile</button>
        <button onclick="window.location.href='index.php';">Home</button>
    </div>
</div>

<script>
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
                // Hide the form and show the success message
                document.getElementById('messageForm').style.display = 'none';
                document.getElementById('responseMessage').style.display = 'block';
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

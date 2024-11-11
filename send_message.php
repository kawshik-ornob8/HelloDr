<?php
include('config.php');

$doctor_id = $_GET['doctor_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Send Message</title>
</head>
<body>
    <h1>Send a Message to the Doctor</h1>
    <form action="send_message_action.php" method="post">
        <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
        <textarea name="message" placeholder="Type your message here" required></textarea>
        <button type="submit">Send Message</button>
    </form>
</body>
</html>

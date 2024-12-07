<?php
session_start();
require '../config.php';

// Check if the user is logged in as a doctor
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch doctor information
$doctorID = $_SESSION['doctor_id'];
$doctorName = '';

$stmt = $conn->prepare("SELECT full_name FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctorID);
$stmt->execute();
$stmt->bind_result($doctorName);
$stmt->fetch();
$stmt->close();

$userID = "doc_" . $doctorID;

// Ensure the room_id is set in the URL
if (!isset($_GET['room_id'])) {
    die("Invalid Room ID.");
}

$roomID = htmlspecialchars($_GET['room_id'], ENT_QUOTES, 'UTF-8');

// Zego App ID and Server Secret
$appID = 513229008;
$serverSecret = "3e58cb91b87348860a11099bfaa481e1";
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        #root { width: 100vw; height: 100vh; }
    </style>
</head>
<body>
    <div id="root"></div>
</body>
<script src="https://unpkg.com/@zegocloud/zego-uikit-prebuilt/zego-uikit-prebuilt.js"></script>

<script>
    window.onload = function () {
        const appID = <?php echo $appID; ?>;
        const serverSecret = "<?php echo $serverSecret; ?>";
        const roomID = "<?php echo $roomID; ?>";
        const userID = "<?php echo $userID; ?>";
        const userName = "<?php echo $doctorName; ?>";

        // Generate Zego token
        const kitToken = ZegoUIKitPrebuilt.generateKitTokenForTest(appID, serverSecret, roomID, userID, userName);

        // Initialize Zego UI Kit
        const zp = ZegoUIKitPrebuilt.create(kitToken);

        // Configure and join room
        zp.joinRoom({
            container: document.querySelector("#root"),
            sharedLinks: [{
                name: 'Doctor Link',
                url: window.location.protocol + '//' + window.location.host + window.location.pathname + '?room_id=' + roomID,
            }],
            scenario: {
                mode: ZegoUIKitPrebuilt.VideoConference,
            },
            turnOnMicrophoneWhenJoining: true,
            turnOnCameraWhenJoining: true,
            showRoomLeaveButton: true, // Display the leave button
            onLeaveRoom: () => {
                // Redirect to end_call.php with the room ID
                window.location.href = `end_call.php?room_id=${roomID}`;
            }
        });
    }
</script>
</html>
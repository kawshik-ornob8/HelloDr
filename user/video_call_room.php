<?php
session_start();
require '../config.php';

// Check if user is logged in as doctor or patient
if (!isset($_SESSION['doctor_id']) && !isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

// Determine user type (doctor or patient)
$userType = isset($_SESSION['doctor_id']) ? 'doctor' : 'patient';
$userID = '';
$userName = '';

// Fetch user name from database based on user type
if ($userType === 'doctor') {
    $stmt = $conn->prepare("SELECT full_name FROM doctors WHERE doctor_id = ?");
    $stmt->bind_param("i", $_SESSION['doctor_id']);
} else {
    $stmt = $conn->prepare("SELECT full_name FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $_SESSION['patient_id']);
}

$stmt->execute();
$stmt->bind_result($userName);
$stmt->fetch();
$userID = ($userType === 'doctor' ? "doc_" : "pat_") . $_SESSION[$userType . '_id'];
$stmt->close();

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
        // Zego video call parameters
        const appID = <?php echo $appID; ?>;
        const serverSecret = "<?php echo $serverSecret; ?>";
        const roomID = "<?php echo $roomID; ?>";
        const userID = "<?php echo $userID; ?>";
        const userName = "<?php echo $userName; ?>";
        const doctorID = <?php echo isset($_GET['doctor_id']) ? $_GET['doctor_id'] : 'null'; ?>;

        // Generate the token for joining the room
        const kitToken = ZegoUIKitPrebuilt.generateKitTokenForTest(appID, serverSecret, roomID, userID, userName);

        // Initialize the ZegoUIKitPrebuilt instance
        const zp = ZegoUIKitPrebuilt.create(kitToken);

        // Join the room with video conference settings
        zp.joinRoom({
            container: document.querySelector("#root"),
            scenario: {
                mode: ZegoUIKitPrebuilt.VideoConference,  // Enable Video Conference mode
            },
            turnOnMicrophoneWhenJoining: true,  // Automatically turn on the microphone
            turnOnCameraWhenJoining: true,      // Automatically turn on the camera
            showMyCameraToggleButton: true,     // Allow toggling camera
            showMyMicrophoneToggleButton: true, // Allow toggling microphone
            showAudioVideoSettingsButton: true, // Show audio/video settings
            showScreenSharingButton: true,      // Allow screen sharing
            showTextChat: true,                 // Enable text chat
            showUserList: true,                 // Show user list
            maxUsers: 2,                        // Maximum number of users
            layout: "Auto",                     // Auto-layout for the video conference
            showLayoutButton: false,            // Do not show layout button
        });

        // Remove "End Call" button for patients
        const userType = "<?php echo $userType; ?>";
        if (userType === "patient") {
            const observer = new MutationObserver(() => {
                const endCallButton = document.querySelector("#ZegoRoomLeaveButton");
                if (endCallButton) {
                    endCallButton.parentElement.removeChild(endCallButton); // Remove the button
                    observer.disconnect(); // Stop observing after the button is removed
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }

        setInterval(() => {
    fetch('check_room_status.php?room_id=' + roomID)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'ended') {
                alert("The doctor has ended the call.");
                window.location.href = `add_review.php?doctor_id=${doctorID}`;

 
            }
        })
        .catch(err => console.error("Error checking room status:", err));
}, 0.5); // Poll every 5 seconds

    }
</script>
</html>

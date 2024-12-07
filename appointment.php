<?php
include('config.php');

// Check if 'doctor_id' exists in the URL
if (!isset($_GET['doctor_id']) || empty($_GET['doctor_id'])) {
    echo "Doctor ID is missing.";
    exit();
}

$doctor_id = $_GET['doctor_id'];

// Fetch doctor details
$query = "SELECT * FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    echo "Doctor not found.";
    exit();
}

// Retrieve values from cookies (if they exist)
$prev_date = isset($_COOKIE['appointment_date']) ? $_COOKIE['appointment_date'] : '';
$prev_time = isset($_COOKIE['appointment_time']) ? $_COOKIE['appointment_time'] : '';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consult Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></title>
    <link rel="stylesheet" href="css/appointment.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1f2641;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        body {
            background-image: url("./images/bg-texture.png");
        }

        .container {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 1.5rem;
            color: #555;
            margin-bottom: 10px;
        }

        p {
            font-size: 1rem;
            color: #666;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            text-align: left;
            margin: 10px 0 5px;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        button {
            background-color: #8094ff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #4e73e6;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Doctor's Profile Information -->
        <img src="doctor/images/<?php echo $doctor['doctor_id']; ?>.<?php echo htmlspecialchars(pathinfo($doctor['profile_photo'], PATHINFO_EXTENSION)); ?>"
            alt="Profile photo of Dr. <?php echo htmlspecialchars($doctor['full_name']); ?>"
            loading="lazy">
        <h2>Consult With</h2>
        <h2>Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h2>
        <p>Specialty: <?php echo htmlspecialchars($doctor['specialty']); ?></p>
        <p>Bio: <?php echo htmlspecialchars($doctor['bio']); ?></p>

        <!-- Form for requesting appointment -->
        <form action="request_appointment_action" method="post">
            <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">

            <label for="appointment_date">Preferred Date:</label>
            <input type="text" id="appointment_date" name="appointment_date" value="<?php echo htmlspecialchars($prev_date); ?>" min="<?php echo date('Y-m-d'); ?>" required>


            <label for="appointment_time">Preferred Time:</label>
            <select name="appointment_time" id="appointment_time" required>
                <option value="">Select a time</option>
                <?php
                $start_time = strtotime("10:00");
                $end_time = strtotime("21:30");
                $interval = 30 * 60; // 30 minutes

                for ($time = $start_time; $time <= $end_time; $time += $interval) {
                    $formatted_time = date("h:i A", $time); // Format in 12-hour AM/PM
                    $value_time = date("H:i", $time); // Hidden value in 24-hour format
                    $selected = ($value_time == $prev_time) ? "selected" : "";
                    echo "<option value=\"$value_time\" $selected>$formatted_time</option>";
                }
                ?>

            </select>

            <button type="submit">Request Appointment</button>
        </form>

        <!-- Back Button -->
        <button onclick="history.back()">Back</button>
    </div>

    <script>
        flatpickr("#appointment_date", {
            dateFormat: "Y-m-d", // You can adjust the format here
            minDate: "<?php echo date('Y-m-d'); ?>", // Set the minimum date as today (PHP)
            defaultDate: "<?php echo htmlspecialchars($prev_date); ?>", // Set the default date (PHP)
        });
    </script>

</body>

</html>
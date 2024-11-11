<?php
session_start();
include('config.php'); // Database configuration file

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: user%20info/user_login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch all unique specialties from the doctors table
$specialties = [];
$query = "SELECT DISTINCT specialty FROM doctors";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $specialties[] = $row['specialty'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment</title>
    <link rel="stylesheet" href="css/doctor lists.css">
</head>
<body>

<div class="container">
    <h2>Select a Specialist</h2>
    
    <form action="" method="post">
        <label for="specialty">Specialist Name:</label>
        <select id="specialty" name="specialty" onchange="this.form.submit()" required>
            <option value="">Select a Specialty</option>
            <?php foreach ($specialties as $specialty): ?>
                <option value="<?php echo htmlspecialchars($specialty); ?>" <?php if(isset($_POST['specialty']) && $_POST['specialty'] == $specialty) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($specialty); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (isset($_POST['specialty'])): ?>
        <?php
        // Fetch doctors based on the selected specialty
        $selected_specialty = $_POST['specialty'];
        $stmt = $conn->prepare("SELECT doctor_id, full_name, degree FROM doctors WHERE specialty = ?");
        $stmt->bind_param("s", $selected_specialty);
        $stmt->execute();
        $doctors = $stmt->get_result();
        ?>

        <h3>Available Doctors for <?php echo htmlspecialchars($selected_specialty); ?></h3>
        
        <table>
            <tr>
                <th>Doctor Name</th>
                <th>Degree</th>
                <th>Action</th>
            </tr>
            <?php while ($doctor = $doctors->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['degree']); ?></td>
                    <td>
                    <form action="appointment.php" method="get" style="display: inline;">
    <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
    <button type="submit">Book Appointment</button>
</form>

                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        
        <?php
        $stmt->close();
        $conn->close();
        ?>
    <?php endif; ?>
</div>

</body>
</html>

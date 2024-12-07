<?php
session_start();
include('config.php'); // Database configuration file

// Check if user is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: user/user_login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch all unique specialties from the doctors table
$specialties = [];
$query = "SELECT DISTINCT specialty FROM doctors";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Split specialties if they contain a comma
        $split_specialties = explode(',', $row['specialty']);
        foreach ($split_specialties as $specialty) {
            $specialty = trim($specialty); // Remove any leading/trailing whitespace
            if (!in_array($specialty, $specialties)) { // Avoid duplicate specialties
                $specialties[] = $specialty;
            }
        }
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
    <!-- Back Button -->
    <button onclick="history.back()" style="margin-bottom: 20px;">Back</button>

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
        // Fetch doctors based on the selected specialty using LIKE for partial matches
        $selected_specialty = $_POST['specialty'];
        $stmt = $conn->prepare("SELECT doctor_id, full_name, degree FROM doctors WHERE specialty LIKE CONCAT('%', ?, '%')");
        $stmt->bind_param("s", $selected_specialty);
        $stmt->execute();
        $doctors = $stmt->get_result();
        ?>

        <h3>Available Doctors for <?php echo htmlspecialchars($selected_specialty); ?></h3>
        
        <table>
            <tr>
                <th>Doctor Name</th>
                <th>Degree</th>
                <th>Review</th>
                <th>Action</th>
            </tr>
            <?php while ($doctor = $doctors->fetch_assoc()): ?>
                <?php
                // Fetch the average rating for the doctor
                $review_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE doctor_id = ?");
                $review_stmt->bind_param("i", $doctor['doctor_id']);
                $review_stmt->execute();
                $review_result = $review_stmt->get_result();
                $review_data = $review_result->fetch_assoc();
                $average_rating = round($review_data['avg_rating'], 1); // Round to 1 decimal place
                ?>
                <tr>
                    <td>
                        <a href="doctor%20info/view_profile.php?doctor_id=<?php echo $doctor['doctor_id']; ?>">
                            <?php echo htmlspecialchars($doctor['full_name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($doctor['degree']); ?></td>
                    <td><?php echo $average_rating ? $average_rating . " / 5" : "No reviews"; ?></td>
                    <td>
                        <form action="appointment.php" method="get" style="display: inline;">
                            <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
                            <button type="submit">Book Appointment</button>
                        </form>
                    </td>
                </tr>
                <?php $review_stmt->close(); ?>
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

<?php
session_start();  // Start the session to access session variables

// Include the database configuration
include('../config.php');

// Check if the doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    // Redirect to login page if not logged in
    header("Location: doctor_login");
    exit();
}

// Retrieve the doctor_id from the session
$doctor_id = $_SESSION['doctor_id'];

// Fetch the doctor's existing data
$query = "SELECT * FROM doctors WHERE doctor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    echo "Doctor not found.";
    exit();
}

// Handle form submission to update doctor details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $specialty = $_POST['specialty'];
    $degree = $_POST['degree'];
    $bio = $_POST['bio'];
    $date_of_birth = $_POST['date_of_birth'];
    $sex = $_POST['sex'];
    $doctor_fee = $_POST['doctor_fee'];  // New doctor fee field

    // Handle profile photo upload
    $profile_photo = $doctor['profile_photo']; // Keep the current photo if no new upload
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "images/";  // Directory for storing the uploaded file
        $imageFileType = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));

        // Rename the file using doctor_id and extension
        $target_file = $target_dir . $doctor_id . '.' . $imageFileType;

        // Validate that the uploaded file is an image
        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check === false) {
            echo "File is not an image.";
            exit();
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
            $profile_photo = $target_file; // Update profile photo path
        } else {
            echo "Error uploading file.";
            exit();
        }
    }

    // Update the doctor's information in the database
    $update_query = "
        UPDATE doctors 
        SET full_name = ?, phone_number = ?, email = ?, specialty = ?, degree = ?, bio = ?, profile_photo = ?, date_of_birth = ?, sex = ?, doctor_fee = ?
        WHERE doctor_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param(
        "ssssssssssi",
        $full_name,
        $phone_number,
        $email,
        $specialty,
        $degree,
        $bio,
        $profile_photo,
        $date_of_birth,
        $sex,
        $doctor_fee,
        $doctor_id
    );

    if ($update_stmt->execute()) {
        echo "<script>
            alert('Profile updated successfully.');
            window.location.href = 'doctor_dashboard.php';
        </script>";
    } else {
        echo "<script>
            alert('Error updating profile.');
            window.location.href = 'doctor_dashboard.php';
        </script>";
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor Profile</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4a90e2;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 24px;
            margin: 0;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 22px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            font-size: 15px;
            padding: 10px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            transition: border 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0px 0px 4px rgba(74, 144, 226, 0.5);
        }

        textarea {
            height: 120px;
        }

        button {
            background-color: #4a90e2;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #357abd;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
        }

        .profile-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            header h1 {
                font-size: 20px;
            }

            button {
                font-size: 14px;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Edit Profile for Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h1>
</header>

<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($doctor['phone_number'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label for="specialty">Specialty:</label>
            <input type="text" name="specialty" value="<?php echo htmlspecialchars($doctor['specialty'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label for="degree">Degree:</label>
            <input type="text" name="degree" value="<?php echo htmlspecialchars($doctor['degree'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label for="bio">Bio:</label>
            <textarea name="bio" required><?php echo htmlspecialchars($doctor['bio'], ENT_QUOTES); ?></textarea>
        </div>

        <div class="form-group">
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($doctor['date_of_birth'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group">
            <label for="sex">Sex:</label>
            <select name="sex" required>
                <option value="Male" <?php echo $doctor['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $doctor['sex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <div class="form-group">
            <label for="doctor_fee">Doctor Fee (৳):</label>
            <input type="number" name="doctor_fee" value="<?php echo htmlspecialchars($doctor['doctor_fee'], ENT_QUOTES); ?>" required>
        </div>

        <div class="form-group profile-photo">
            <label for="profile_photo">Profile Photo:</label>
            <?php if ($doctor['profile_photo']): ?>
                <img src="<?php echo htmlspecialchars($doctor['profile_photo'], ENT_QUOTES); ?>" alt="Profile Photo">
            <?php endif; ?>
            <input type="file" name="profile_photo">
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center;">
    <button type="button" onclick="history.back()" style="background-color: #ccc; color: #333; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease;">Back</button>
    <button type="submit">Update Profile</button>
</div>

    </form>
</div>

</body>
</html>

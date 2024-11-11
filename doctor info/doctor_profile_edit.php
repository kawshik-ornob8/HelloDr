<?php
session_start();  // Start the session to access session variables

// Include the database configuration
include('../config.php');

// Check if doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    // If doctor is not logged in, redirect to login page
    header("Location: doctor_login.php");
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

    // Handle profile photo upload if a new photo is provided
    $profile_photo = $doctor['profile_photo'];  // Keep the current photo if no new upload
    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "images/";  // Directory where the image will be saved
        $imageFileType = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));

        // Rename the file to doctor_id with extension
        $target_file = $target_dir . $doctor_id . '.' . $imageFileType;

        // Check if the uploaded file is an image
        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check === false) {
            echo "File is not an image.";
            exit();
        }

        // Move the uploaded file to the desired directory with the new name
        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
            $profile_photo = $target_file;  // Update the profile photo path
        } else {
            echo "Error uploading file.";
            exit();
        }
    }

    // Update the doctor's information in the database
    $update_query = "UPDATE doctors SET full_name = ?, phone_number = ?, email = ?, specialty = ?, degree = ?, bio = ?, profile_photo = ?, date_of_birth = ?, sex = ? WHERE doctor_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("sssssssssi", $full_name, $phone_number, $email, $specialty, $degree, $bio, $profile_photo, $date_of_birth, $sex, $doctor_id);

    if ($update_stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile.";
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
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #2d3e50;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 80%;
            max-width: 900px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 28px;
            color: #333;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            font-size: 16px;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        input[type="file"] {
            margin-bottom: 20px;
        }

        textarea {
            height: 150px;
        }

        button {
            background-color: #2d3e50;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #1f2d3d;
        }

        .profile-photo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
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
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name']); ?>" required><br>
        </div>

        <div class="form-group">
            <label for="phone_number">Phone Number:</label>
            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($doctor['phone_number']); ?>" required><br>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required><br>
        </div>

        <div class="form-group">
            <label for="specialty">Specialty:</label>
            <input type="text" name="specialty" value="<?php echo htmlspecialchars($doctor['specialty']); ?>" required><br>
        </div>

        <div class="form-group">
            <label for="degree">Degree:</label>
            <input type="text" name="degree" value="<?php echo htmlspecialchars($doctor['degree']); ?>" required><br>
        </div>

        <div class="form-group">
            <label for="bio">Bio:</label>
            <textarea name="bio" required><?php echo htmlspecialchars($doctor['bio']); ?></textarea><br>
        </div>

        <div class="form-group">
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($doctor['date_of_birth']); ?>" required><br>
        </div>

        <div class="form-group">
            <label for="sex">Sex:</label>
            <select name="sex" required>
                <option value="Male" <?php if ($doctor['sex'] == 'Male') echo 'selected'; ?>>Male</option>
                <option value="Female" <?php if ($doctor['sex'] == 'Female') echo 'selected'; ?>>Female</option>
            </select><br>
        </div>

        <div class="form-group profile-photo">
            <label for="profile_photo">Profile Photo:</label>
            <?php if ($doctor['profile_photo']): ?>
                <img src="<?php echo htmlspecialchars($doctor['profile_photo']); ?>" alt="Profile Photo"><br>
            <?php endif; ?>
            <input type="file" name="profile_photo"><br>
        </div>

        <button type="submit">Update Profile</button>
    </form>
</div>

</body>
</html>

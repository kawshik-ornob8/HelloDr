<?php
session_start();
include('../config.php');

// Check if the patient is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: login");
    exit;
}

// Get patient ID from session
$patient_id = intval($_SESSION['patient_id']);

// Fetch patient data
$sql = "SELECT * FROM patients WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Patient not found.";
    exit;
}
$patient = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = htmlspecialchars($_POST['full_name']);
    $date_of_birth = htmlspecialchars($_POST['date_of_birth']);
    $sex = htmlspecialchars($_POST['sex']);
    $mobile_number = htmlspecialchars($_POST['mobile_number']);
    $email = htmlspecialchars($_POST['email']);

    // Handle profile photo upload
    $upload_dir = 'images/';
    $profile_photo = $_FILES['profile_photo'];
    $profile_photo_path = $patient['profile_photo'];

    if ($profile_photo['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $profile_photo['tmp_name'];
        $extension = strtolower(pathinfo($profile_photo['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        if (in_array($extension, $allowed_extensions)) {
            $new_photo_path = $upload_dir . $patient_id . '.' . $extension;

            // Remove old profile photo if exists
            foreach ($allowed_extensions as $ext) {
                $old_photo_path = $upload_dir . $patient_id . '.' . $ext;
                if (file_exists($old_photo_path)) {
                    unlink($old_photo_path);
                }
            }

            // Move the uploaded file
            if (move_uploaded_file($tmp_name, $new_photo_path)) {
                $profile_photo_path = $new_photo_path;
            } else {
                echo "Failed to upload profile photo.";
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
        }
    }

    // Update the database
    $update_sql = "UPDATE patients SET full_name = ?, date_of_birth = ?, sex = ?, mobile_number = ?, email = ?, profile_photo = ? WHERE patient_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssi", $full_name, $date_of_birth, $sex, $mobile_number, $email, $profile_photo_path, $patient_id);

    if ($update_stmt->execute()) {
        header("Location: user_profile");
        exit;
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
    <title>Edit User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f4f9;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #0066cc;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-container button:hover {
            background-color: #0052a3;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Profile</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>

            <label for="date_of_birth">Date of Birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo htmlspecialchars($patient['date_of_birth']); ?>" required>

            <label for="sex">Sex</label>
            <select name="sex" id="sex" required>
                <option value="Male" <?php echo $patient['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $patient['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo $patient['sex'] === 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>

            <label for="mobile_number">Mobile Number</label>
            <input type="text" name="mobile_number" id="mobile_number" value="<?php echo htmlspecialchars($patient['mobile_number']); ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>

            <label for="profile_photo">Profile Photo</label>
            <input type="file" name="profile_photo" id="profile_photo" accept=".jpg,.jpeg,.png">

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>

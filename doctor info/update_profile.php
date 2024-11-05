<?php
// update_profile.php

session_start();
require_once '../config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['doctor_id'])) {
    $doctor_id = $_SESSION['doctor_id'];

    // Get the form data
    $specialty = $_POST['specialty'];
    $degree = $_POST['degree'];
    $bio = $_POST['bio'];
    $profile_photo = $_FILES['profile_photo'];

    // Update profile photo if a file was uploaded
    $photo_name = null;
    if (!empty($profile_photo['name'])) {
        $target_dir = "uploads/";
        $photo_name = basename($profile_photo['name']);
        $target_file = $target_dir . $photo_name;

        // Check if the file is an image
        $check = getimagesize($profile_photo['tmp_name']);
        if ($check !== false) {
            // Move file to the upload directory
            if (move_uploaded_file($profile_photo['tmp_name'], $target_file)) {
                // Update the database with the new profile photo name
            } else {
                echo "Sorry, there was an error uploading your file.";
                exit();
            }
        } else {
            echo "File is not an image.";
            exit();
        }
    }

    // Update the doctor's profile information in the database
    $sql = "UPDATE doctors SET specialty = ?, degree = ?, bio = ?, profile_photo = IFNULL(?, profile_photo) WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $specialty, $degree, $bio, $photo_name, $doctor_id);
    
    if ($stmt->execute()) {
        header('Location: doctor_profile.php');
    } else {
        echo "Error updating profile.";
    }

    $stmt->close();
    $conn->close();
}
?>

<?php
session_start();
include('../config.php'); // Include the database configuration file

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission to update team member information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $team_member_id = $_POST['team_member_id'];
    $name = $_POST['name'];
    $position = $_POST['position'];
    $instagram = $_POST['instagram'];
    $facebook = $_POST['facebook'];
    $twitter = $_POST['twitter'];

    // Handle image upload if a new file is provided
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "images/";
        $image_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_url = $target_dir . $team_member_id . "." . $image_extension;
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_url);
    } else {
        // Use existing image if no new image is uploaded
        $image_url = $_POST['existing_image'];
    }

    // Update team member information in the database
    $stmt = $conn->prepare("UPDATE team SET name = ?, position = ?, image_url = ?, instagram_url = ?, facebook_url = ?, twitter_url = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $position, $image_url, $instagram, $facebook, $twitter, $team_member_id);

    if ($stmt->execute()) {
        $success_message = "Team member information updated successfully!";
    } else {
        $error_message = "Error updating information: " . $stmt->error;
    }
    $stmt->close();
}

// Process form submission to delete a team member
if (isset($_POST['delete'])) {
    $team_member_id = $_POST['team_member_id'];

    $stmt = $conn->prepare("DELETE FROM team WHERE id = ?");
    $stmt->bind_param("i", $team_member_id);

    if ($stmt->execute()) {
        $success_message = "Team member removed successfully!";
    } else {
        $error_message = "Error removing team member: " . $stmt->error;
    }
    $stmt->close();
}

// Process form submission to add a new team member
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $instagram = $_POST['instagram'];
    $facebook = $_POST['facebook'];
    $twitter = $_POST['twitter'];

    // Handle image upload for new member
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_url = $target_dir . uniqid() . "." . $image_extension; // Use a unique name
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_url);
    } else {
        $image_url = ''; // Set to empty string if no image is uploaded
    }

    // Insert new team member information into the database
    $stmt = $conn->prepare("INSERT INTO team (name, position, image_url, instagram_url, facebook_url, twitter_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $position, $image_url, $instagram, $facebook, $twitter);

    if ($stmt->execute()) {
        $success_message = "New team member added successfully!";
    } else {
        $error_message = "Error adding new team member: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch team members from the database
$team_result = $conn->query("SELECT * FROM team");

if (!$team_result) {
    die("Error retrieving team data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Update Team</title>
    <link rel="stylesheet" href="css/team.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Admin Dashboard - Meet Our Team</h2>
        <a href="../logout.php" class="button">Logout</a>

        <?php if (isset($success_message)) echo "<p class='success'>$success_message</p>"; ?>
        <?php if (isset($error_message)) echo "<p class='error'>$error_message</p>"; ?>

        <h3>Update Team Members</h3>

        <?php if ($team_result->num_rows == 0) {
            echo "<p>No team members found in the database.</p>";
        } ?>

        <!-- Display each team member with Edit/Delete options -->
        <?php while ($team_member = $team_result->fetch_assoc()) { ?>
            <div class="team-member">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($team_member['name']); ?></p>
                <p><strong>Position:</strong> <?php echo htmlspecialchars($team_member['position']); ?></p>
                <img src="<?php echo htmlspecialchars($team_member['image_url']); ?>" alt="Image" style="width:50px;height:50px;">
                
                <form action="team.php" method="POST" style="display:inline;">
                    <input type="hidden" name="team_member_id" value="<?php echo $team_member['id']; ?>">
                    <button type="submit" name="edit" value="1">Edit</button>
                </form>
                <form action="team.php" method="POST" style="display:inline;">
                    <input type="hidden" name="team_member_id" value="<?php echo $team_member['id']; ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>

                <?php if (isset($_POST['edit']) && $_POST['team_member_id'] == $team_member['id']) { ?>
                    <form action="team.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="team_member_id" value="<?php echo $team_member['id']; ?>">
                        <input type="hidden" name="existing_image" value="<?php echo $team_member['image_url']; ?>">
                        <input type="hidden" name="update" value="1">

                        <label for="name">Full Name:</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($team_member['name']); ?>" required>

                        <label for="position">Position:</label>
                        <input type="text" name="position" value="<?php echo htmlspecialchars($team_member['position']); ?>" required>

                        <label for="image">Image:</label>
                        <input type="file" name="image">
                        <p>Current Image: <img src="<?php echo $team_member['image_url']; ?>" alt="Image" style="width:50px;height:50px;"></p>

                        <label for="instagram">Instagram URL:</label>
                        <input type="url" name="instagram" value="<?php echo htmlspecialchars($team_member['instagram_url']); ?>">

                        <label for="facebook">Facebook URL:</label>
                        <input type="url" name="facebook" value="<?php echo htmlspecialchars($team_member['facebook_url']); ?>">

                        <label for="twitter">Twitter URL:</label>
                        <input type="url" name="twitter" value="<?php echo htmlspecialchars($team_member['twitter_url']); ?>">

                        <button type="submit">Update</button>
                    </form>
                    <hr>
                <?php } ?>
            </div>
        <?php } ?>

        <!-- Form to Add a New Team Member -->
        <h3>Add New Team Member</h3>
        <form action="team.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add" value="1">

            <label for="name">Full Name:</label>
            <input type="text" name="name" required>

            <label for="position">Position:</label>
            <input type="text" name="position" required>

            <label for="image">Image:</label>
            <input type="file" name="image">

            <label for="instagram">Instagram URL:</label>
            <input type="url" name="instagram">

            <label for="facebook">Facebook URL:</label>
            <input type="url" name="facebook">

            <label for="twitter">Twitter URL:</label>
            <input type="url" name="twitter">

            <button type="submit">Add Team Member</button>
        </form>
    </div>
</body>
</html>

<?php
include('../config.php');

// Add Plan Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plan_name = $_POST['plan_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $features = $_POST['features'];

    $stmt = $conn->prepare("INSERT INTO health_plans (plan_name, description, price, duration, features) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $plan_name, $description, $price, $duration, $features);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Plan added successfully!'); window.location.href = 'admin_add_plan.php';</script>";
    } else {
        echo "<script>alert('Failed to add plan. Try again!');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Health Plan</title>
</head>
<body>
    <h2>Add New Health Plan</h2>
    <form method="POST" action="">
        <label for="plan_name">Plan Name:</label>
        <input type="text" name="plan_name" required>

        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <label for="price">Price (USD):</label>
        <input type="number" name="price" step="0.01" required>

        <label for="duration">Duration (days):</label>
        <input type="number" name="duration" required>

        <label for="features">Features (comma-separated):</label>
        <textarea name="features" required></textarea>

        <button type="submit">Add Plan</button>
    </form>
</body>
</html>

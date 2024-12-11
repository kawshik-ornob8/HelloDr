<?php
session_start();
include('../config.php'); // Adjust path if necessary

// Check if the user is logged in and is a patient
if (!isset($_SESSION['patient_id']) || !isset($_SESSION['username'])) {
    $_SESSION['redirect_to'] = 'add_review';
    header("Location: user/user_login");
    exit;
}

// Ensure doctor_id is set in the URL
if (!isset($_GET['doctor_id'])) {
    echo "<p class='error-message'>Doctor ID is not specified.</p>";
    exit;
}

// Get doctor_id from the URL
$doctor_id = htmlspecialchars($_GET['doctor_id']);

// Fetch doctor details (name)
$doctor_sql = "SELECT full_name FROM doctors WHERE doctor_id = ?";
$doctor_stmt = $conn->prepare($doctor_sql);
$doctor_stmt->bind_param("i", $doctor_id);
$doctor_stmt->execute();
$doctor_stmt->bind_result($doctor_name);
$doctor_stmt->fetch();
$doctor_stmt->close();

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['patient_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        echo "<p class='error-message'>Invalid rating. Rating should be between 1 and 5.</p>";
        exit;
    }

    // Insert review into the database
    $stmt = $conn->prepare("INSERT INTO reviews (doctor_id, patient_id, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $doctor_id, $patient_id, $rating, $review_text);

    if ($stmt->execute()) {
        // Show success message and redirect
        echo "<script>
                alert('Review added successfully!');
                window.location.href = '../doctor/view_profile?doctor_id=" . $doctor_id . "'; 
              </script>";
    } else {
        echo "<p class='error-message'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        .form-container {
            width: 400px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #6a0dad;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-size: 14px;
            color: #555;
            text-align: left;
        }

        input[type="number"],
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            margin-top: 5px;
        }

        textarea {
            resize: none;
        }

        button[type="submit"] {
            background-color: #6a0dad;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #5a0ca5;
        }

        .rating-stars {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .star {
            font-size: 40px; /* Increased size of the stars */
            color: #ccc;
            cursor: pointer;
            transition: color 0.3s;
        }

        .star:hover,
        .star.selected {
            color: #f0c419;
        }

        .success-message {
            color: #28a745;
            margin-top: 15px;
        }

        .error-message {
            color: #dc3545;
            margin-top: 15px;
        }

        .skip-button {
            background-color: #d9534f;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
        }

        .skip-button:hover {
            background-color: #c9302c;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Call with Dr. <?php echo $doctor_name; ?> has ended.</h2>
    <p>The video call with Dr. <?php echo $doctor_name; ?> has ended. Thank you for using our service.</p>


    <h2>Add Review for Dr. <?php echo $doctor_name; ?></h2>

    <form method="POST" action="add_review?doctor_id=<?php echo $doctor_id; ?>">
        <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">

        <label for="rating">Rating:</label>
        <div class="rating-stars">
            <span class="star" data-value="1">&#9733;</span>
            <span class="star" data-value="2">&#9733;</span>
            <span class="star" data-value="3">&#9733;</span>
            <span class="star" data-value="4">&#9733;</span>
            <span class="star" data-value="5">&#9733;</span>
        </div>
        <input type="hidden" name="rating" id="rating-input" required>

        <label for="review_text">Review:</label>
        <textarea id="review_text" name="review_text" rows="4" required></textarea>

        <button type="submit">Submit Review</button>
    </form>
    <!-- Skip button to visit index page -->
    <form action="../index" method="get">
        <button type="submit" class="skip-button">Skip and Go to Dashboard</button>
    </form>
</div>

<script>
    // JavaScript for rating stars functionality
    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('rating-input');
    let selectedRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', function () {
            selectedRating = this.getAttribute('data-value');
            ratingInput.value = selectedRating;
            updateStarColors(selectedRating); // Update star colors based on the selected rating
        });

        star.addEventListener('mouseover', function () {
            const rating = this.getAttribute('data-value');
            updateStarColors(rating);
        });

        star.addEventListener('mouseout', function () {
            if (selectedRating === 0) {
                // Reset stars to the original state if no rating is selected
                updateStarColors(0);
            } else {
                // Keep selected stars highlighted
                updateStarColors(selectedRating);
            }
        });
    });

    // Update the star colors based on the rating
    function updateStarColors(rating) {
        stars.forEach(star => {
            if (star.getAttribute('data-value') <= rating) {
                star.classList.add('selected');
            } else {
                star.classList.remove('selected');
            }
        });
    }
</script>

</body>
</html>

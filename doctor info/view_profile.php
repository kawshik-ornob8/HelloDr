<?php
session_start();
include('../config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Base styles */
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f9;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 10px;
        }

        /* Profile Card */
        .profile-card {
            background: linear-gradient(to bottom, #6a0dad, #a34fe0);
            color: white;
            width: 100%;
            max-width: 250px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .profile-card img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 3px solid white;
        }
        .profile-card h2 {
            margin: 0;
            font-size: 18px;
        }
        .profile-card p {
            margin: 5px 0;
        }
        .profile-card .rating {
            font-size: 24px;
            margin: 20px 0;
        }
        .profile-card .rating-stars {
            color: #ffd700;
        }
        .profile-card .contact-info a {
            color: white;
            text-decoration: none;
            display: block;
            margin: 5px 0;
        }
        .profile-card .profile-btn {
            background: #e0d4ff;
            color: #6a0dad;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        /* Review Section */
        .review-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
        }
        .review-section h3 {
            font-size: 22px;
            color: #6a0dad;
            margin-bottom: 10px;
        }
        .review-section .teacher-info {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            background-color: #f0f0f5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .review-section .teacher-info div {
            flex: 1 1 100%;
            margin-bottom: 10px;
        }
        .review-section .add-review, .review-section .send-message {
            display: inline-block;
            background: #a34fe0;
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .review-section .send-message {
            background: #6a0dad;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .review-section .send-message img {
            width: 16px;
        }
        .review-section .review-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .review-section .review-item .rating {
            color: #ffd700;
            font-size: 18px;
        }
        .review-section .review-item .review-text {
            color: #333;
            font-size: 14px;
            margin-top: 10px;
        }
        .review-section .review-item .review-author {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
            text-align: right;
        }
        .view-all-btn {
            background: #6a0dad;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        /* Responsive adjustments */
        @media (min-width: 768px) {
            .container {
                flex-direction: row;
            }
            .profile-card {
                max-width: 250px;
                margin: 0;
            }
            .review-section {
                flex-grow: 1;
            }
            .review-section .teacher-info div {
                flex: 1 1 45%;
            }
        }

        @media (max-width: 768px) {
            .review-section h3 {
                font-size: 20px;
            }
            .review-section .teacher-info div {
                flex: 1 1 100%;
            }
            .profile-card .rating {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php
    // Get doctor ID from URL
    $doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
    $view_all = isset($_GET['view_all']) ? true : false;

    // Fetch doctor data
    $sql = "SELECT * FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
        $full_name = htmlspecialchars($doctor['full_name']);
        $specialty = htmlspecialchars($doctor['specialty']);
        $faculty = "Faculty of Science and Information Technology";
        $department = "Software Engineering (SWE)";
        $email = htmlspecialchars($doctor['email']);
        $phone_number = htmlspecialchars($doctor['phone_number']);
        $doctor_reg_id = htmlspecialchars($doctor['doctor_reg_id']);
        $profile_photo_path = "images/default.jpg";

        // Check for profile photo
        $extensions = ['jpg', 'jpeg', 'png'];
        foreach ($extensions as $ext) {
            $possible_path = "images/{$doctor_id}.$ext";
            if (file_exists($possible_path)) {
                $profile_photo_path = $possible_path;
                break;
            }
        }
        ?>

        <!-- Profile Card -->
        <div class="profile-card">
            <img src="<?php echo $profile_photo_path; ?>" alt="Profile Picture">
            <h2><?php echo $full_name; ?></h2>
            <p><?php echo $specialty; ?></p>
            <p><?php echo $department; ?></p>
            <p><?php echo $faculty; ?></p>
            <div class="rating">
                <span class="rating-stars">☆☆☆☆☆</span>
            </div>
            <div class="contact-info">
                <a href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
                <a href="tel:<?php echo $phone_number; ?>"><?php echo $phone_number; ?></a>
                <p>Doctor Registration ID: <?php echo $doctor_reg_id; ?></p>
            </div>
            <a href="#" class="profile-btn">View Official Profile</a>
        </div>

        <!-- Review Section -->
        <div class="review-section">
            <h3>About <?php echo $full_name; ?></h3>
            <div class="teacher-info">
                <div>
                    <p><strong>Designation:</strong> <?php echo $specialty; ?></p>
                    <p><strong>Faculty:</strong> <?php echo $faculty; ?></p>
                </div>
                <div>
                    <p><strong>Department:</strong> <?php echo $department; ?></p>
                    <p><strong>Doctor Registration ID:</strong> <?php echo $doctor_reg_id; ?></p>
                </div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="../add_review.php?doctor_id=<?php echo $doctor_id; ?>" class="add-review">Add Your Review</a>
                <a href="../send_message.php?doctor_id=<?php echo $doctor_id; ?>" class="send-message">
                    <img src="images/chat.png" alt="Message Icon"> Send Message
                </a>
            </div>

            <h3>Reviews</h3>

            <?php
            // Fetch reviews for this doctor (limit to 3 or show all if view_all is set)
            $limit = $view_all ? "" : "LIMIT 3";
            $review_sql = "SELECT r.rating, r.review_text, p.full_name FROM reviews r JOIN patients p ON r.patient_id = p.patient_id WHERE r.doctor_id = ? ORDER BY r.created_at DESC $limit";
            $review_stmt = $conn->prepare($review_sql);
            $review_stmt->bind_param("i", $doctor_id);
            $review_stmt->execute();
            $review_result = $review_stmt->get_result();

            if ($review_result->num_rows > 0) {
                while ($review = $review_result->fetch_assoc()) {
                    $rating = htmlspecialchars($review['rating']);
                    $review_text = htmlspecialchars($review['review_text']);
                    $author = htmlspecialchars($review['full_name']);
                    ?>
                    <div class="review-item">
                        <div class="rating">
                            <?php echo str_repeat("★", $rating) . str_repeat("☆", 5 - $rating); ?>
                        </div>
                        <p class="review-text"><?php echo $review_text; ?></p>
                        <p class="review-author">- <?php echo $author; ?></p>
                    </div>
                    <?php
                }
                if (!$view_all) {
                    echo '<a href="?doctor_id=' . $doctor_id . '&view_all=true" class="view-all-btn">View All Reviews</a>';
                }
            } else {
                echo "<p>No reviews found for this doctor.</p>";
            }

            $review_stmt->close();
            ?>

        </div>

        <?php
    } else {
        echo "<p>Doctor not found.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>
</div>

</body>
</html>

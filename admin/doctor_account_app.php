<?php
session_start();
include "../config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login');
    exit;
}

// Pagination variables
$entries_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $entries_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';
$params = [];
$types = '';

if (!empty($search)) {
    if (is_numeric($search)) {
        $search_query = " AND doctor_id = ?";
        $params[] = (int)$search;
        $types .= "i";
    } else {
        $search_query = " AND (full_name LIKE ? OR email LIKE ? OR phone_number LIKE ? OR doctor_reg_id LIKE ?)";
        $like_search = "%" . $search . "%";
        $params = array_fill(0, 4, $like_search);
        $types .= str_repeat("s", 4);
    }
}

// Fetch pending doctors with pagination
$query = "SELECT * FROM doctors WHERE is_active = 2" . $search_query . " LIMIT ?, ?";
$params[] = $start;
$params[] = $entries_per_page;
$types .= "ii";

$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Error preparing statement: " . $conn->error);
}

// Count total pending doctors
$count_query = "SELECT COUNT(*) AS total_count FROM doctors WHERE is_active = 2" . $search_query;
$count_stmt = $conn->prepare($count_query);
if ($count_stmt) {
    if (!empty($params) && count($params) > 2) {
        $count_stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2));
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_doctors = $count_row['total_count'];
} else {
    die("Error preparing count statement: " . $conn->error);
}

// Approve doctor account
if (isset($_GET['approve_id']) && is_numeric($_GET['approve_id'])) {
    $approve_id = (int)$_GET['approve_id'];

    $approve_query = "UPDATE doctors SET is_active = 1 WHERE doctor_id = ?";
    $approve_stmt = $conn->prepare($approve_query);
    if ($approve_stmt) {
        $approve_stmt->bind_param("i", $approve_id);
        $approve_stmt->execute();
        header("Location: doctor_account_app?page=$page&search=" . urlencode($search));
        exit;
    } else {
        die("Error preparing approval statement: " . $conn->error);
    }
}

// Reject doctor account
if (isset($_GET['reject_id']) && is_numeric($_GET['reject_id'])) {
    $reject_id = (int)$_GET['reject_id'];

    $reject_query = "DELETE FROM doctors WHERE doctor_id = ?";
    $reject_stmt = $conn->prepare($reject_query);
    if ($reject_stmt) {
        $reject_stmt->bind_param("i", $reject_id);
        $reject_stmt->execute();
        header("Location: doctor_account_app?page=$page&search=" . urlencode($search));
        exit;
    } else {
        die("Error preparing rejection statement: " . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Account Approval</title>
    <link rel="stylesheet" href="css/manage_doctors.css">
    <link rel="stylesheet" href="css/doctor_account_app.css">
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
</head>
<body>

<div class="container">
    <h2>Doctor Account Approval</h2>

    <!-- Search form -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
        Search by ID, Name or Email:
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Pending Doctors table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Doctor Reg Id</th>
                <th>Certificate</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['doctor_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_reg_id']); ?></td>
                    <td>
                        <a href="../images/certificates/<?php echo htmlspecialchars($row['certificate']); ?>" target="_blank" class="view-btn">View Certificate</a>
                    </td>
                    <td>
                        <a href="doctor_account_app?approve_id=<?php echo $row['doctor_id']; ?>&page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" class="approve-btn">Approve</a>
                        <a href="doctor_account_app?reject_id=<?php echo $row['doctor_id']; ?>&page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" class="reject-btn">Reject</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_doctors > $entries_per_page) { ?>
        <div class="pagination">
            <?php
            $total_pages = ceil($total_doctors / $entries_per_page);
            $prev = $page - 1;
            $next = $page + 1;

            if ($page > 1) {
                echo "<a href='doctor_account_app?page=$prev&search=" . urlencode($search) . "'>Previous</a>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($page == $i) ? "active" : "";
                echo "<a href='doctor_account_app?page=$i&search=" . urlencode($search) . "' class='$active'>$i</a>";
            }

            if ($page < $total_pages) {
                echo "<a href='doctor_account_app?page=$next&search=" . urlencode($search) . "'>Next</a>";
            }
            ?>
        </div>
    <?php } ?>

    <a href="admin_dashboard" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>

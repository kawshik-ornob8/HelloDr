<?php
session_start();
include "../config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php'); // Redirect to login if not logged in as an admin
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
        $search_query = " WHERE doctor_id = ?";
        $params[] = (int)$search;
        $types .= "i";
    } else {
        $search_query = " WHERE (full_name LIKE ? OR email LIKE ? OR phone_number LIKE ? OR specialty LIKE ?)";
        $like_search = "%" . $search . "%";
        $params = array_fill(0, 4, $like_search);
        $types .= str_repeat("s", 4);
    }
}

// Fetch doctors with pagination
$query = "SELECT * FROM doctors" . $search_query . " LIMIT ?, ?";
$params[] = $start;
$params[] = $entries_per_page;
$types .= "ii";

$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total doctors (for pagination)
$count_query = "SELECT COUNT(*) AS total_count FROM doctors" . $search_query;
$count_stmt = $conn->prepare($count_query);
if (!empty($params) && count($params) > 2) {
    $count_stmt->bind_param(substr($types, 0, -2), ...array_slice($params, 0, -2)); // Exclude LIMIT params
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_doctors = $count_row['total_count'];

// Delete doctor
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    // Delete the doctor (appointments will be automatically deleted due to ON DELETE CASCADE)
    $delete_query = "DELETE FROM doctors WHERE doctor_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();

    // Redirect after deletion
    header("Location: manage_doctors.php?page=$page&search=" . urlencode($search));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors</title>
    <link rel="stylesheet" href="css/manage_doctors.css">
    <link rel="icon" type="image/x-icon" href="./images/favicon.png">
</head>
<body>

<div class="container">
    <h2>Manage Doctors</h2>

    <!-- Search form -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET">
        Search by ID, Name, Email, or Specialty: 
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Doctors table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone Number</th>
                <th>Specialty</th>
                <th>Degree</th>
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
                    <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                    <td><?php echo htmlspecialchars($row['degree']); ?></td>
                    <td>
                        <a href="manage_doctors.php?delete_id=<?php echo $row['doctor_id']; ?>&page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>" class="delete-btn">Delete</a>
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
                echo "<a href='manage_doctors.php?page=$prev&search=" . urlencode($search) . "'>Previous</a>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($page == $i) ? "active" : "";
                echo "<a href='manage_doctors.php?page=$i&search=" . urlencode($search) . "' class='$active'>$i</a>";
            }

            if ($page < $total_pages) {
                echo "<a href='manage_doctors.php?page=$next&search=" . urlencode($search) . "'>Next</a>";
            }
            ?>
        </div>
    <?php } ?>

    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>

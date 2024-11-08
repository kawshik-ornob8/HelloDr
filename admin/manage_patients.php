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
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $entries_per_page;

// Search and fetch patients from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM patients";  // Use the correct table name: `patients`

// Apply search filter if there is a search term
if (!empty($search)) {
    if (is_numeric($search)) {
        $query .= " WHERE patient_id = $search"; // Use `patient_id` instead of `id`
    } else {
        $query .= " WHERE (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%' OR mobile_number LIKE '%$search%')";
    }
}
$query .= " LIMIT $start, $entries_per_page";

$result = mysqli_query($conn, $query);

// Count total patients (for pagination)
$count_query = "SELECT COUNT(*) AS total_count FROM patients"; // Use the correct table name: `patients`
if (!empty($search)) {
    if (is_numeric($search)) {
        $count_query .= " WHERE patient_id = $search"; // Use `patient_id` instead of `id`
    } else {
        $count_query .= " WHERE (username LIKE '%$search%' OR full_name LIKE '%$search%' OR email LIKE '%$search%' OR mobile_number LIKE '%$search%')";
    }
}

$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_patients = $count_row['total_count'];

// Delete patient
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM patients WHERE patient_id = $delete_id"; // Use `patient_id` instead of `id`
    mysqli_query($conn, $delete_query);
    header("Location: manage_patients.php?page=$page&search=$search");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patients</title>
    <link rel="stylesheet" href="css/manage_patients.css">
</head>
<body>

<div class="container">
    <h2>Manage Patients</h2>

    <!-- Search form -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
        Search by ID or Name: <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Patient table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo isset($row['patient_id']) ? $row['patient_id'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['username']) ? $row['username'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['full_name']) ? $row['full_name'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['email']) ? $row['email'] : 'N/A'; ?></td>
                    <td><?php echo isset($row['mobile_number']) ? $row['mobile_number'] : 'N/A'; ?></td>
                    <td>
                        <a href="manage_patients.php?delete_id=<?php echo isset($row['patient_id']) ? $row['patient_id'] : ''; ?>&page=<?php echo $page; ?>&search=<?php echo $search; ?>" class="delete-btn">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_patients > $entries_per_page) { ?>
        <div class="pagination">
            <?php
            $total_pages = ceil($total_patients / $entries_per_page);
            $prev = $page - 1;
            $next = $page + 1;

            if ($page > 1) {
                echo "<a href='manage_patients.php?page=$prev&search=$search'>Previous</a>";
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($page == $i) ? "active" : "";
                echo "<a href='manage_patients.php?page=$i&search=$search' class='$active'>$i</a>";
            }

            if ($page < $total_pages) {
                echo "<a href='manage_patients.php?page=$next&search=$search'>Next</a>";
            }
            ?>
        </div>
    <?php } ?>
    <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>

<?php
session_start();
include 'config.php';

// Ensure only the owner can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

$current_user_id = intval($_SESSION['user_id']);

// Handle role update
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['new_role'];

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);
    $stmt->execute();
    header("Location: staff_list.php");
    exit();
}

// Handle delete staff request
if (isset($_POST['delete_staff'])) {
    $user_id = intval($_POST['user_id']);
    
    // Prevent deleting your own account
    if ($user_id !== $current_user_id) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }

    header("Location: staff_list.php");
    exit();
}

// Fetch all users except the current logged-in owner
$users = $conn->query("SELECT * FROM users WHERE id != $current_user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff List</title>
    <link rel="stylesheet" href="ownercss.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>W.I.Y Laundry</h2>
        <nav>
            <ul>
                <li><a href="owner_dashboard.php">Dashboard</a></li>
                <li><a href="staff_list.php" class="active">Staff List</a></li>
                <li><a href="attendance.php">Attendance</a></li>
                <li><a href="payroll.php">Payroll</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>Staff List</h1>
        </header>

        <section class="dashboard-section">
            <table border="1" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Update Role</th>
                        <th>Remove Staff</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name']); ?></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td><?= htmlspecialchars($user['role']); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <select name="new_role" required>
                                    <option value="">Select Role</option>
                                    <option value="Owner">Owner</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Pending">Pending</option>
                                </select>
                                <button type="submit" name="update_role">Update</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to remove this staff member?');" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <button type="submit" name="delete_staff" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>

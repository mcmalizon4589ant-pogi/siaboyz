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
    <style>
        .staff-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .staff-table th {
            background: #fafafa;
            color: #333;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .staff-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .staff-table tr:hover {
            background: #f8f9fa;
        }
        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-owner {
            background: #ffc107;
            color: #856404;
        }
        .role-staff {
            background: #28a745;
            color: white;
        }
        .role-pending {
            background: #dc3545;
            color: white;
        }
        .position-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
            background: #e9ecef;
            color: #495057;
        }
        .position-trainee {
            background: #fff3cd;
            color: #856404;
        }
        .position-supervisor, .position-admin, .position-manager {
            background: #d1ecf1;
            color: #0c5460;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        .btn-edit:hover {
            background: #2563eb;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .action-cell {
            white-space: nowrap;
        }
    </style>
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
                <li><a href="payroll_v2.php">Payroll</a></li>
                <li><a href="settings.php">Settings</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>Staff List</h1>
        </header>

        <section class="dashboard-section">
            <table class="staff-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Role</th>
                        <th>Date Hired</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): 
                        $role_class = '';
                        if ($user['role'] == 'Owner') $role_class = 'role-owner';
                        elseif ($user['role'] == 'Staff') $role_class = 'role-staff';
                        else $role_class = 'role-pending';
                        
                        // Position badge class
                        $position_class = 'position-badge';
                        $position_value = isset($user['position']) ? strtolower($user['position']) : '';
                        if ($position_value == 'trainee') $position_class .= ' position-trainee';
                        elseif (in_array($position_value, ['supervisor', 'admin', 'manager'])) $position_class .= ' position-' . $position_value;
                    ?>
                    <tr>
                        <td>#<?= $user['id']; ?></td>
                        <td><strong><?= htmlspecialchars($user['name']); ?></strong></td>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if (isset($user['position']) && $user['position']): ?>
                                <span class="<?= $position_class ?>"><?= htmlspecialchars($user['position']); ?></span>
                            <?php else: ?>
                                <em style="color:#999;">Not set</em>
                            <?php endif; ?>
                        </td>
                        <td><?= isset($user['contact_number']) && $user['contact_number'] ? htmlspecialchars($user['contact_number']) : '<em style="color:#999;">N/A</em>'; ?></td>
                        <td><span class="role-badge <?= $role_class ?>"><?= htmlspecialchars($user['role']); ?></span></td>
                        <td><?= isset($user['date_hired']) && $user['date_hired'] ? date('M d, Y', strtotime($user['date_hired'])) : '<em style="color:#999;">Not hired yet</em>'; ?></td>
                        <td class="action-cell">
                            <a href="edit_staff.php?id=<?= $user['id']; ?>" class="btn btn-edit">Edit</a>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member? This action cannot be undone!');" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                <button type="submit" name="delete_staff" class="btn btn-delete">Delete</button>
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

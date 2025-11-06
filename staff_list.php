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

// Handle delete/archive staff request
if (isset($_POST['delete_staff'])) {
    $user_id = intval($_POST['user_id']);
    $termination_reason = isset($_POST['termination_reason']) ? trim($_POST['termination_reason']) : 'Not specified';
    
    // Prevent deleting your own account
    if ($user_id !== $current_user_id) {
        // Get user data before archiving
        $user_data = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
        
        if ($user_data) {
            // Calculate total days worked
            $total_days = 0;
            if ($user_data['date_hired']) {
                $hired = strtotime($user_data['date_hired']);
                $now = time();
                $total_days = floor(($now - $hired) / (60 * 60 * 24));
            }
            
            // Calculate final salary (get from attendance records)
            $salary_query = $conn->query("SELECT SUM(TIMESTAMPDIFF(HOUR, time_in, time_out)) as total_hours 
                                         FROM attendance WHERE user_id = $user_id");
            $salary_data = $salary_query->fetch_assoc();
            $total_hours = $salary_data['total_hours'] ?? 0;
            $final_salary = $total_hours * 85; // Rate per hour
            
            // Archive the employee data
            $archive_stmt = $conn->prepare("INSERT INTO archived_employees 
                (original_user_id, name, email, contact_number, address, position, role, 
                 date_hired, date_terminated, termination_reason, terminated_by, 
                 total_days_worked, final_salary) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?)");
            
            $archive_stmt->bind_param("issssssssiid", 
                $user_id,
                $user_data['name'],
                $user_data['email'],
                $user_data['contact_number'],
                $user_data['address'],
                $user_data['position'],
                $user_data['role'],
                $user_data['date_hired'],
                $termination_reason,
                $current_user_id,
                $total_days,
                $final_salary
            );
            
            if ($archive_stmt->execute()) {
                // Delete attendance records (or keep them, your choice)
                // $conn->query("DELETE FROM attendance WHERE user_id = $user_id");
                
                // Delete user from active users
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $delete_stmt->bind_param("i", $user_id);
                $delete_stmt->execute();
            }
        }
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
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 30px;
            border-radius: 10px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .modal-header {
            margin-bottom: 20px;
        }
        .modal-header h2 {
            margin: 0;
            color: #dc3545;
        }
        .modal-body textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-height: 100px;
            font-family: inherit;
            resize: vertical;
        }
        .modal-footer {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        .btn-modal-cancel {
            background: #6c757d;
            color: white;
        }
        .btn-modal-cancel:hover {
            background: #5a6268;
        }
    </style>
    <script>
        function showDeleteModal(userId, userName) {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('modalUserId').value = userId;
            document.getElementById('staffNameDisplay').textContent = userName;
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.getElementById('termination_reason').value = ''; // Clear textarea
        }
        
        // Close modal if click outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                closeDeleteModal();
            }
        }
    </script>
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
                            <button type="button" onclick="showDeleteModal(<?= $user['id']; ?>, '<?= htmlspecialchars(addslashes($user['name'])); ?>')" class="btn btn-delete">Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2>Confirm Staff Termination</h2>
        <p>Are you sure you want to terminate <strong id="staffNameDisplay"></strong>?</p>
        <p style="color:#666; font-size:14px; margin-top:10px;">This will archive the employee's records and remove their access to the system.</p>
        
        <form method="POST" id="deleteForm">
            <input type="hidden" name="user_id" id="modalUserId">
            
            <div style="margin:20px 0;">
                <label for="termination_reason" style="display:block; margin-bottom:8px; font-weight:600; color:#333;">
                    Reason for Termination: <span style="color:#e74c3c;">*</span>
                </label>
                <textarea 
                    name="termination_reason" 
                    id="termination_reason" 
                    rows="4" 
                    required
                    placeholder="Enter the reason for termination (e.g., resignation, terminated, end of contract, etc.)"
                    style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-family:inherit; font-size:14px; resize:vertical;"
                ></textarea>
            </div>
            
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeDeleteModal()" class="btn" style="background:#6c757d; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" name="delete_staff" class="btn" style="background:#e74c3c; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:600;">
                    Confirm Termination
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>

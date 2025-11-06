<?php
session_start();
include 'config.php';

// Ensure only owner can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';
$staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Prevent editing owner's own account (use settings.php instead)
if ($staff_id == $_SESSION['user_id']) {
    header("Location: settings.php");
    exit();
}

// Fetch staff information
$staff_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$staff_query->bind_param("i", $staff_id);
$staff_query->execute();
$staff = $staff_query->get_result()->fetch_assoc();

if (!$staff) {
    header("Location: staff_list.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_staff'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $contact_number = trim($_POST['contact_number']);
        $address = trim($_POST['address']);
        
        // Handle custom position
        $position = trim($_POST['position']);
        if ($position == 'Custom' && !empty($_POST['custom_position'])) {
            $position = trim($_POST['custom_position']);
        }
        
        $role = $_POST['role'];
        $date_hired = !empty($_POST['date_hired']) ? $_POST['date_hired'] : NULL;
        
        // Check if email exists for another user
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("si", $email, $staff_id);
        $email_check->execute();
        $email_exists = $email_check->get_result()->num_rows > 0;
        
        if ($email_exists) {
            $error = "Email address is already in use by another account.";
        } else {
            $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, contact_number = ?, address = ?, position = ?, role = ?, date_hired = ? WHERE id = ?");
            $update_query->bind_param("sssssssi", $name, $email, $contact_number, $address, $position, $role, $date_hired, $staff_id);
            
            if ($update_query->execute()) {
                $message = "Staff information updated successfully!";
                // Refresh staff data
                $staff_query->execute();
                $staff = $staff_query->get_result()->fetch_assoc();
            } else {
                $error = "Failed to update staff information.";
            }
        }
    }
    
    // Handle password reset
    if (isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password === $confirm_password) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $pass_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pass_update->bind_param("si", $password_hash, $staff_id);
            
            if ($pass_update->execute()) {
                $message = "Password reset successfully!";
            } else {
                $error = "Failed to reset password.";
            }
        } else {
            $error = "Passwords do not match.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff - <?= htmlspecialchars($staff['name']) ?></title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .edit-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .edit-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .edit-section h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 10px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29,78,216,0.1);
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            margin-right: 10px;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #e9ecef;
            border-radius: 20px;
            font-size: 14px;
            margin-left: 10px;
        }
        .danger-zone {
            border: 2px solid #dc3545;
            background: #fff5f5;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        <div class="edit-container">
            <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1>Edit Staff Member</h1>
                    <p>Editing: <strong><?= htmlspecialchars($staff['name']) ?></strong> 
                       <span class="info-badge">ID: #<?= $staff['id'] ?></span>
                    </p>
                </div>
                <a href="staff_list.php" class="btn btn-secondary">Back to Staff List</a>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Staff Information Form -->
            <div class="edit-section">
                <h2>Staff Information</h2>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($staff['name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($staff['email']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" 
                                   value="<?= htmlspecialchars($staff['contact_number'] ?? '') ?>" 
                                   placeholder="e.g., 09123456789">
                        </div>

                        <div class="form-group">
                            <label for="position">Position / Staff Type</label>
                            <select id="position" name="position" onchange="if(this.value=='Custom') document.getElementById('custom_position').style.display='block'; else document.getElementById('custom_position').style.display='none';">
                                <option value="Trainee" <?= (isset($staff['position']) && $staff['position'] == 'Trainee') ? 'selected' : '' ?>>Trainee</option>
                                <option value="Staff" <?= (isset($staff['position']) && $staff['position'] == 'Staff') ? 'selected' : '' ?>>Staff</option>
                                <option value="Laundry Attendant" <?= (isset($staff['position']) && $staff['position'] == 'Laundry Attendant') ? 'selected' : '' ?>>Laundry Attendant</option>
                                <option value="Supervisor" <?= (isset($staff['position']) && $staff['position'] == 'Supervisor') ? 'selected' : '' ?>>Supervisor</option>
                                <option value="Admin" <?= (isset($staff['position']) && $staff['position'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                <option value="Manager" <?= (isset($staff['position']) && $staff['position'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                                <option value="Custom">Custom Position...</option>
                            </select>
                            <input type="text" id="custom_position" name="custom_position" 
                                   style="display:none; margin-top:10px;" 
                                   placeholder="Enter custom position">
                            <small style="color: #6c757d;">* Select staff type or role in the company</small>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Account Role *</label>
                            <select id="role" name="role" required>
                                <option value="Pending" <?= $staff['role'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Staff" <?= $staff['role'] == 'Staff' ? 'selected' : '' ?>>Staff</option>
                                <option value="Owner" <?= $staff['role'] == 'Owner' ? 'selected' : '' ?>>Owner</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="date_hired">Date Hired</label>
                            <input type="date" id="date_hired" name="date_hired" 
                                   value="<?= isset($staff['date_hired']) ? $staff['date_hired'] : '' ?>">
                            <small style="color: #6c757d;">* Leave empty if not yet hired/confirmed</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" 
                                  placeholder="Enter complete address"><?= htmlspecialchars($staff['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <p style="color: #6c757d; font-size: 14px;">
                            <strong>Account Created:</strong> <?= date('F d, Y - h:i A', strtotime($staff['created_at'])) ?>
                        </p>
                    </div>

                    <button type="submit" name="update_staff" class="btn btn-primary">Save Changes</button>
                </form>
            </div>

            <!-- Password Reset Section -->
            <div class="edit-section danger-zone">
                <h2>Reset Password</h2>
                <p style="color: #dc3545; margin-bottom: 20px;">
                    <strong>Warning:</strong> Resetting this user's password will immediately change their login credentials.
                </p>
                <form method="POST" action="" onsubmit="return confirm('Are you sure you want to reset this user\'s password?');">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Enter new password" minlength="6">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password" minlength="6">
                        </div>
                    </div>

                    <button type="submit" name="reset_password" class="btn btn-danger">Reset Password</button>
                </form>
            </div>

            <!-- Delete Account Section -->
            <div class="edit-section danger-zone">
                <h2>Delete Account</h2>
                <p style="color: #dc3545; margin-bottom: 20px;">
                    <strong>Danger Zone:</strong> Deleting this account will permanently remove all associated data including attendance and payroll records.
                </p>
                <form method="POST" action="staff_list.php" onsubmit="return confirm('Are you ABSOLUTELY sure you want to delete this staff member? This action CANNOT be undone!');">
                    <input type="hidden" name="user_id" value="<?= $staff['id'] ?>">
                    <button type="submit" name="delete_staff" class="btn btn-danger">Delete This Account</button>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

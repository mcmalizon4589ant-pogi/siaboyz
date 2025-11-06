<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';
$error = '';

// Fetch current user information
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $position = trim($_POST['position']);
    
    // Check if email already exists for another user
    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->bind_param("si", $email, $user_id);
    $email_check->execute();
    $email_exists = $email_check->get_result()->num_rows > 0;
    
    if ($email_exists) {
        $error = "Email address is already in use by another account.";
    } else {
        // Update user information
        $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, contact_number = ?, address = ?, position = ? WHERE id = ?");
        $update_query->bind_param("sssssi", $name, $email, $contact_number, $address, $position, $user_id);
        
        if ($update_query->execute()) {
            $_SESSION['name'] = $name; // Update session name
            $message = "Profile updated successfully!";
            
            // Refresh user data
            $user_query->execute();
            $user = $user_query->get_result()->fetch_assoc();
        } else {
            $error = "Failed to update profile. Please try again.";
        }
    }
    
    // Handle password change
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $user['password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $new_password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $pass_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pass_update->bind_param("si", $new_password_hash, $user_id);
                
                if ($pass_update->execute()) {
                    $message .= " Password updated successfully!";
                } else {
                    $error .= " Failed to update password.";
                }
            } else {
                $error .= " New passwords do not match.";
            }
        } else {
            $error .= " Current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Profile</title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .settings-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .settings-section h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 10px;
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
        .form-group textarea {
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
        .form-group textarea:focus {
            outline: none;
            border-color: #1d4ed8;
            box-shadow: 0 0 0 3px rgba(29,78,216,0.1);
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background: #2563eb;
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
        .info-display {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-display .label {
            font-weight: 600;
            color: #555;
        }
        .info-display .value {
            color: #333;
        }
        .readonly-field {
            background: #f8f9fa !important;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php 
    if ($role === 'Owner') {
        include 'sidebar_owner.php';
    } else {
        // Staff sidebar
        ?>
        <aside class="sidebar">
            <h2>W.I.Y Laundry</h2>
            <nav>
                <ul>
                    <li><a href="staff_dashboard.php">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="payroll.php">Payroll</a></li>
                    <li><a href="settings.php" class="active">Settings</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout">Log Out</a>
        </aside>
        <?php
    }
    ?>

    <main class="main-content">
        <div class="settings-container">
            <header>
                <h1>Account Settings</h1>
                <p>Manage your profile information and account settings</p>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Account Information Display -->
            <div class="settings-section">
                <h2>Account Information</h2>
                <div class="info-display">
                    <div class="label">User ID:</div>
                    <div class="value">#<?= $user['id'] ?></div>
                    
                    <div class="label">Account Role:</div>
                    <div class="value"><strong><?= htmlspecialchars($user['role']) ?></strong></div>
                    
                    <div class="label">Date Hired:</div>
                    <div class="value"><?= isset($user['date_hired']) && $user['date_hired'] ? date('F d, Y', strtotime($user['date_hired'])) : 'Not set' ?></div>
                    
                    <div class="label">Account Created:</div>
                    <div class="value"><?= date('F d, Y', strtotime($user['created_at'])) ?></div>
                </div>
            </div>

            <!-- Profile Information Form -->
            <div class="settings-section">
                <h2>Profile Information</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" 
                               value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" 
                               placeholder="e.g., 09123456789">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" 
                                  placeholder="Enter your complete address"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" id="position" name="position" 
                               value="<?= htmlspecialchars($user['position'] ?? 'Staff') ?>" 
                               <?= $role === 'Staff' ? 'class="readonly-field" readonly' : '' ?>>
                        <?php if ($role === 'Staff'): ?>
                            <small style="color: #6c757d;">* Position can only be changed by the owner</small>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn-primary">Save Profile Changes</button>
                </form>
            </div>

            <!-- Change Password Section -->
            <div class="settings-section">
                <h2>Change Password</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                               placeholder="Enter your current password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="Enter new password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn-primary">Update Password</button>
                    
                    <p style="margin-top: 15px; color: #6c757d; font-size: 14px;">
                        <strong>Note:</strong> Leave password fields empty if you don't want to change your password.
                    </p>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>

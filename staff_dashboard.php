<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['name'];
$role = $_SESSION['role'];

if ($role !== 'Staff' && $role !== 'Owner') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - W.I.Y Laundry</title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .dashboard-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.4em;
        }
        .card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .feature-link {
            display: block;
            padding: 12px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }
        .feature-link:hover {
            background: #0056b3;
        }
        .welcome-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .welcome-section h1 {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .welcome-section h2 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .welcome-section p {
            color: #666;
        }
        .role-badge {
            display: inline-block;
            padding: 5px 12px;
            background: #e9ecef;
            color: #495057;
            border-radius: 15px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>W.I.Y Laundry</h2>
        <nav>
            <ul>
                <?php if ($role === 'Owner'): ?>
                    <li><a href="owner_dashboard.php">Dashboard</a></li>
                    <li><a href="staff_list.php">Staff List</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="payroll_v2.php">Payroll</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php else: ?>
                    <li><a href="staff_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="payroll.php">Payroll</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <div class="welcome-section">
            <h1>A Web-Based Payroll Management System with Fingerprint Biometrics Scanner for W.I.Y Laundry Shop.</h1>
            <h2>Welcome, <?= htmlspecialchars($username); ?>!</h2>
            <p><span class="role-badge"><?= htmlspecialchars($role); ?></span></p>
        </div>

        <section class="dashboard-section">
            <div class="card">
                <h3>Attendance</h3>
                <p>View and record your daily attendance using the fingerprint biometrics scanner. Keep track of your time records and maintain accurate attendance history.</p>
                <a href="attendance.php" class="feature-link">Go to Attendance</a>
            </div>

            <div class="card">
                <h3>Payroll</h3>
                <p>Check your salary details and payroll history. View your complete earnings, work hours, and payment records in an organized format.</p>
                <a href="payroll.php" class="feature-link">View Payroll</a>
            </div>

            <?php if ($role === 'Owner'): ?>
            <div class="card">
                <h3>Staff Management</h3>
                <p>View and manage staff members and roles. Add new employees, update information, and maintain staff records efficiently.</p>
                <a href="staff_list.php" class="feature-link">Manage Staff</a>
            </div>
            <?php endif; ?>
        </section>
        
        <?php
        // Get today's attendance status
        $today = date('Y-m-d');
        $attendance_query = $conn->query("SELECT time_in, time_out 
                                        FROM attendance 
                                        WHERE user_id = '$user_id' 
                                        AND DATE(time_in) = '$today'");
        $today_attendance = $attendance_query->fetch_assoc();
        ?>
        
        <section class="dashboard-section">
            <div class="card">
                <h3>Today's Status</h3>
                <?php if ($today_attendance): ?>
                    <p>Time In: <?= date('h:i A', strtotime($today_attendance['time_in'])) ?></p>
                    <?php if ($today_attendance['time_out']): ?>
                        <p>Time Out: <?= date('h:i A', strtotime($today_attendance['time_out'])) ?></p>
                    <?php else: ?>
                        <p>Time Out: Not yet recorded</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No attendance recorded for today</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>

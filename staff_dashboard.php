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
                    <li><a href="payroll.php">Payroll</a></li>
                <?php else: ?>
                    <li><a href="staff_dashboard.php" class="active">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="payroll.php">Payroll</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>A Web-Based Payroll Management System with Fingerprint Biometrics Scanner for W.I.Y Laundry Shop.</h1>
            <h2>Welcome, <?= htmlspecialchars($username); ?>!</h2>
            <p>Role: <?= htmlspecialchars($role); ?></p>
        </header>

        <section class="dashboard-section">
            <div class="card">
                <h3>Attendance</h3>
                <p>View and record your daily attendance.</p>
            <!--   <a href="attendance.php" class="btn">Go to Attendance</a> -->
            </div>

            <div class="card">
                <h3>Payroll</h3>
                <p>Check your salary details and payroll history.</p>
            <!--   <a href="payroll.php" class="btn">View Payroll</a> -->
            </div>

            <?php if ($role === 'Owner'): ?>
            <div class="card">
                <h3>Staff Management</h3>
                <p>View and manage staff members and roles.</p>
            <!--   <a href="staff_list.php" class="btn">Manage Staff</a> -->
            </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>

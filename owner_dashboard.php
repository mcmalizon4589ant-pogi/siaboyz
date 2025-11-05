<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit();
}

$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="ownercss.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <h2>W.I.Y Laundry</h2>
        <nav>
            <ul>
                <li><a href="owner_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="staff_list.php">Staff List</a></li>
                <li><a href="attendance.php">Attendance</a></li>
                <li><a href="payroll.php">Payroll</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>A Web-Based Payroll Management System with Fingerprint Biometrics Scanner for W.I.Y Laundry Shop.</h1>
            <h2>Welcome, <?= htmlspecialchars($name); ?>!</h2>
            <p>Manage your laundry staff operations here.</p>
        </header>

        <section class="content">
            <div class="card-grid">
                <div>
                    <h3>Staff List</h3>
                    <p>Manage staff accounts and roles.</p>
                </div>
                <div>
                    <h3>Attendance</h3>
                    <p>Monitor daily time-ins and time-outs.</p>
                </div>
                <div>
                    <h3>Payroll</h3>
                    <p>Review payroll and hours worked.</p>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>

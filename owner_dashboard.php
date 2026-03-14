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
    <style>
        .biometric-feed {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .biometric-item {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 12px;
        }
        .biometric-meta {
            color: #666;
            font-size: 0.9em;
        }
        .biometric-status.ok {
            color: #28a745;
        }
        .biometric-status.fail {
            color: #dc3545;
        }
    </style>
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
                <li><a href="payroll_v2.php">Payroll</a></li>
                <li><a href="settings.php">Settings</a></li>
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

        <section class="content">
            <div class="card-grid">
                <div>
                    <h3>Recent Biometric Scans</h3>
                    <div id="biometric-feed" class="biometric-feed">Loading...</div>
                </div>
            </div>
        </section>
    </main>
</div>
<script>
    const biometricFeed = document.getElementById('biometric-feed');

    function renderBiometricFeed(items) {
        if (!items.length) {
            biometricFeed.textContent = 'No recent scans yet.';
            return;
        }

        biometricFeed.innerHTML = items.map((item) => {
            const timeText = item.timestamp ? new Date(item.timestamp.replace(' ', 'T')).toLocaleString() : 'Unknown time';
            const statusClass = item.status === 'success' ? 'ok' : 'fail';
            const actionText = item.action ? item.action.replace('_', ' ') : 'scan';
            return `
                <div class="biometric-item">
                    <div><strong>${item.name}</strong> - ${actionText}</div>
                    <div class="biometric-meta">
                        <span class="biometric-status ${statusClass}">${item.status}</span>
                        <span> | ${timeText}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function loadRecentScans() {
        try {
            const response = await fetch('api/biometric_recent.php?limit=8', { credentials: 'same-origin' });
            const data = await response.json();
            if (!data.success) {
                biometricFeed.textContent = 'Unable to load recent scans.';
                return;
            }
            renderBiometricFeed(data.data || []);
        } catch (error) {
            biometricFeed.textContent = 'Unable to load recent scans.';
        }
    }

    loadRecentScans();
    setInterval(loadRecentScans, 5000);
</script>
</body>
</html>

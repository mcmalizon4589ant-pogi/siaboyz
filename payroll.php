<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];

function calc_hours($in, $out) {
    if (!$in || !$out) return 0;
    return round((strtotime($out) - strtotime($in)) / 3600, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll</title>
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
                <?php else: ?>
                    <li><a href="staff_dashboard.php">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="attendance.php">Attendance</a></li>
                <li><a href="payroll.php" class="active">Payroll</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>Payroll Summary</h1>
        </header>

        <?php if ($role === 'Owner'): ?>
            <table border="1" width="100%">
                <tr><th>Staff</th><th>Total Hours</th><th>Rate</th><th>Total Pay</th></tr>
                <?php
                $staffs = $conn->query("SELECT id, name FROM users WHERE role='Staff'");
                while ($s = $staffs->fetch_assoc()) {
                    $att = $conn->query("SELECT time_in, time_out FROM attendance WHERE user_id='{$s['id']}'");
                    $total = 0;
                    while ($a = $att->fetch_assoc()) $total += calc_hours($a['time_in'], $a['time_out']);
                    $rate = 85;
                    $pay = $rate * $total;
                    echo "<tr><td>{$s['name']}</td><td>$total</td><td>₱$rate/hr</td><td>₱$pay</td></tr>";
                }
                ?>
            </table>
        <?php else: ?>
            <?php
            $att = $conn->query("SELECT time_in, time_out FROM attendance WHERE user_id='$user_id'");
            $total = 0;
            while ($a = $att->fetch_assoc()) $total += calc_hours($a['time_in'], $a['time_out']);
            $rate = 85;
            $pay = $rate * $total;
            ?>
            <p>Total Hours Worked: <b><?= $total ?></b></p>
            <p>Hourly Rate: <b>₱<?= $rate ?></b></p>
            <p>Total Pay: <b>₱<?= $pay ?></b></p>
        <?php endif; ?>
    </main>
</div>
</body>
</html>

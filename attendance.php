<?php
session_start();
date_default_timezone_set('Asia/Manila');
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $today = date('Y-m-d');
    $now = date('H:i:s');

    if ($action === 'time_in') {
        $check = $conn->query("SELECT * FROM attendance WHERE user_id='$user_id' AND date='$today'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO attendance (user_id, date, time_in) VALUES ('$user_id', '$today', '$now')");
            $message = "Time-in recorded at $now.";
        } else {
            $message = "Already timed in today.";
        }
    } elseif ($action === 'time_out') {
        $check = $conn->query("SELECT * FROM attendance WHERE user_id='$user_id' AND date='$today'");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE attendance SET time_out='$now' WHERE user_id='$user_id' AND date='$today'");
            $message = "Time-out recorded at $now.";
        } else {
            $message = "You need to time-in first.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance</title>
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
                <li><a href="attendance.php" class="active">Attendance</a></li>
                <li><a href="payroll.php">Payroll</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>Attendance Portal</h1>
            <p>Welcome, <strong><?= htmlspecialchars($name); ?></strong></p>
            <?php if (!empty($message)) echo "<p>$message</p>"; ?>
        </header>

        <form method="POST">
            <button type="submit" name="action" value="time_in">Time In</button>
            <button type="submit" name="action" value="time_out">Time Out</button>
        </form>

        <hr>
        <h3>Attendance Records</h3>
        <table border="1" width="100%">
            <tr>
                <?php if ($role === 'Owner'): ?><th>Name</th><?php endif; ?>
                <th>Date</th><th>Time In</th><th>Time Out</th>
            </tr>
            <?php
            $query = ($role === 'Owner')
                ? "SELECT a.date, a.time_in, a.time_out, u.name FROM attendance a JOIN users u ON a.user_id = u.id ORDER BY a.date DESC"
                : "SELECT date, time_in, time_out FROM attendance WHERE user_id='$user_id' ORDER BY date DESC";
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()):
            ?>
            <tr>
                <?php if ($role === 'Owner') echo "<td>{$row['name']}</td>"; ?>
                <td><?= $row['date']; ?></td>
                <td><?= $row['time_in']; ?></td>
                <td><?= $row['time_out']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </main>
</div>
</body>
</html>

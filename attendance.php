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
    <script>
        // Update clock and timer
        function updateClock() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US');
        }

        // Update work timer if clocked in
        function updateWorkTimer() {
            const timeInElement = document.getElementById('time-in-value');
            if (timeInElement && timeInElement.dataset.timeIn) {
                const timeIn = new Date(timeInElement.dataset.timeIn).getTime();
                const now = new Date().getTime();
                const timeDiff = now - timeIn;
                
                const hours = Math.floor(timeDiff / (1000 * 60 * 60));
                const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                
                document.getElementById('work-timer').textContent = 
                    `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
        }

        // Update every second
        setInterval(() => {
            updateClock();
            updateWorkTimer();
        }, 1000);

        // Initial update
        window.onload = () => {
            updateClock();
            updateWorkTimer();
        };
    </script>
    <style>
        .clock-container {
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .clock-container .date {
            font-size: 1.2em;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .clock-container .time {
            font-size: 2.5em;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .timer-container {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .timer-label {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 5px;
        }
        .timer-display {
            font-size: 1.8em;
            font-family: monospace;
            color: #333;
            font-weight: bold;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        .time {
            animation: pulse 2s infinite;
            display: inline-block;
        }
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin: 10px 0;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .progress-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress-bar-container {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-bar {
            height: 100%;
            background-color: #28a745;
            transition: width 0.3s ease;
        }
        .progress-warning .progress-bar {
            background-color: #ffc107;
        }
        .progress-danger .progress-bar {
            background-color: #dc3545;
        }
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            color: #666;
        }
        .warning-message {
            color: #dc3545;
            font-weight: bold;
            margin-top: 10px;
            display: none;
        }
        .warning-message.show {
            display: block;
        }
        .status-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }
        .status-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .button-container {
            display: flex;
            gap: 10px;
            margin: 20px 0;
        }
        button[type="submit"] {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        button[value="time_in"] {
            background-color: #28a745;
            color: white;
        }
        button[value="time_out"] {
            background-color: #dc3545;
            color: white;
        }
        button:hover {
            opacity: 0.9;
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
        <?php
        // Get today's attendance
        $today = date('Y-m-d');
        $current_time = time();
        $today_record = $conn->query("SELECT time_in, time_out FROM attendance WHERE user_id='$user_id' AND date='$today'")->fetch_assoc();
        ?>
        
        <header>
            <h1>Attendance Portal</h1>
            <p>Welcome, <strong><?= htmlspecialchars($name); ?></strong></p>
            <?php if (!empty($message)) echo "<p class='alert'>$message</p>"; ?>
            
            <div class="clock-container">
                <div class="date" id="current-date"></div>
                <div class="time" id="current-time"></div>
                <?php if ($today_record && !$today_record['time_out']): ?>
                    <!-- <div class="timer-container">
                        <div class="timer-label">Time Since Clock In</div>
                        <div class="timer-display" id="work-timer">00:00:00</div>
                        <span id="time-in-value" data-time-in="<?= $today_record['time_in'] ?>" style="display: none;"></span>
                    </div> -->
                <?php endif; ?>
            </div>
        </header>

        <?php
        // Calculate hours worked
        $hours_worked = 0;
        $status = 'Not Started';
        
        if ($today_record) {
            $time_in = strtotime($today_record['time_in']);
            $time_out = $today_record['time_out'] ? strtotime($today_record['time_out']) : $current_time;
            $hours_worked = round(($time_out - $time_in) / 3600, 2);
            $status = $today_record['time_out'] ? 'Completed' : 'In Progress';
        }

        // Calculate progress percentage
        $max_hours = 8;
        $progress = min(($hours_worked / $max_hours) * 100, 100);
        $progress_class = '';
        
        if ($hours_worked > 7.5 && $hours_worked < 8) {
            $progress_class = 'progress-warning';
        } elseif ($hours_worked >= 8) {
            $progress_class = 'progress-danger';
        }
        ?>

        <div class="status-card">
            <h2>Today's Status</h2>
            <div class="status-info">
                <div class="status-item">
                    <strong>Status:</strong> <?= $status ?>
                </div>
                <div class="status-item">
                    <strong>Time In:</strong> <?= $today_record ? date('h:i A', strtotime($today_record['time_in'])) : 'Not yet' ?>
                </div>
                <div class="status-item">
                    <strong>Time Out:</strong> <?= ($today_record && $today_record['time_out']) ? date('h:i A', strtotime($today_record['time_out'])) : 'Not yet' ?>
                </div>
            </div>
        </div>

        <div class="progress-container <?= $progress_class ?>">
            <h3>Hours Worked Today</h3>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?= $progress ?>%"></div>
            </div>
            <div class="progress-info">
                <span><?= number_format($hours_worked, 2) ?> hours</span>
                <span>Maximum: <?= $max_hours ?> hours</span>
            </div>
            <?php if ($hours_worked >= 8): ?>
                <div class="warning-message show">
                    ⚠️ Warning: You have exceeded the maximum working hours for today!
                </div>
            <?php elseif ($hours_worked > 7.5): ?>
                <div class="warning-message show">
                    ⚠️ Notice: You are approaching the maximum working hours!
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="button-container">
            <button type="submit" name="action" value="time_in" <?= $today_record ? 'disabled' : '' ?>>
                Time In
            </button>
            <button type="submit" name="action" value="time_out" <?= (!$today_record || $today_record['time_out']) ? 'disabled' : '' ?>>
                Time Out
            </button>
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

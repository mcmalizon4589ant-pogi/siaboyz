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
    $hours = round((strtotime($out) - strtotime($in)) / 3600, 2);
    // Limit to 8 hours maximum
    return min($hours, 8);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll</title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .payroll-table {
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .payroll-table th, .payroll-table td {
            padding: 10px;
            text-align: left;
        }
        .payroll-table th {
            background-color: #f5f5f5;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .summary-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .summary-box h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        select {
            padding: 8px;
            margin-bottom: 20px;
            min-width: 200px;
        }
        .mb-4 {
            margin-bottom: 20px;
        }
        .no-records {
            text-align: center;
            padding: 40px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin: 20px 0;
        }
        .no-records p {
            color: #6c757d;
            font-size: 1.1em;
            margin: 0;
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

        <?php
        $rate = 85; // Rate per hour
        $currentMonth = date('m');
        $currentYear = date('Y');
        
        if ($role === 'Owner'): ?>
            <!-- Staff Selection Dropdown -->
            <form method="GET" class="mb-4">
                <select name="staff_id" onchange="this.form.submit()">
                    <option value="">Select Staff Member</option>
                    <?php
                    $staffs = $conn->query("SELECT id, name FROM users WHERE role='Staff'");
                    while ($staff = $staffs->fetch_assoc()) {
                        $selected = (isset($_GET['staff_id']) && $_GET['staff_id'] == $staff['id']) ? 'selected' : '';
                        echo "<option value='{$staff['id']}' {$selected}>{$staff['name']}</option>";
                    }
                    ?>
                </select>
            </form>

            <?php if (isset($_GET['staff_id']) && $_GET['staff_id']): 
                $staff_id = $_GET['staff_id'];
                $staff_query = $conn->query("SELECT name FROM users WHERE id='$staff_id'");
                $staff_name = $staff_query->fetch_assoc()['name'];

                // Check if there are any records for the selected month
                $check_records = $conn->query("SELECT COUNT(*) as count 
                                             FROM attendance 
                                             WHERE user_id='$staff_id' 
                                             AND MONTH(time_in) = $currentMonth 
                                             AND YEAR(time_in) = $currentYear");
                $record_count = $check_records->fetch_assoc()['count'];
            ?>
                <h2>Payroll Details for <?= $staff_name ?></h2>
                
                <?php if ($record_count == 0): ?>
                    <div class="no-records">
                        <p>No attendance records found for <?= $staff_name ?> in <?= date('F Y') ?>.</p>
                    </div>
                <?php else: ?>
                <table border="1" width="100%" class="payroll-table">
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Hours Worked</th>
                        <th>Daily Pay</th>
                    </tr>
                    <?php
                    $total_hours = 0;
                    $total_pay = 0;
                    
                    $attendance = $conn->query("SELECT DATE(time_in) as date, time_in, time_out 
                                             FROM attendance 
                                             WHERE user_id='$staff_id' 
                                             AND MONTH(time_in) = $currentMonth 
                                             AND YEAR(time_in) = $currentYear 
                                             ORDER BY time_in DESC");
                    
                    while ($day = $attendance->fetch_assoc()) {
                        $hours = calc_hours($day['time_in'], $day['time_out']);
                        $daily_pay = $hours * $rate;
                        $total_hours += $hours;
                        $total_pay += $daily_pay;
                        
                        echo "<tr>";
                        echo "<td>" . date('M d, Y', strtotime($day['date'])) . "</td>";
                        echo "<td>" . date('h:i A', strtotime($day['time_in'])) . "</td>";
                        echo "<td>" . ($day['time_out'] ? date('h:i A', strtotime($day['time_out'])) : 'Not Out') . "</td>";
                        echo "<td>" . number_format($hours, 2) . "</td>";
                        echo "<td>₱" . number_format($daily_pay, 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                    <tr class="total-row">
                        <td colspan="3"><strong>Totals</strong></td>
                        <td><strong><?= number_format($total_hours, 2) ?></strong></td>
                        <td><strong>₱<?= number_format($total_pay, 2) ?></strong></td>
                    </tr>
                </table>
                
                <div class="summary-box">
                    <h3>Monthly Summary</h3>
                    <p>Total Hours Worked: <strong><?= number_format($total_hours, 2) ?> hrs</strong></p>
                    <p>Hourly Rate: <strong>₱<?= number_format($rate, 2) ?></strong></p>
                    <p>Total Pay: <strong>₱<?= number_format($total_pay, 2) ?></strong></p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
        <?php else: // Staff View ?>
            <h2>My Payroll Details for <?= date('F Y') ?></h2>
            <?php
            // Check if there are any records for the current staff
            $check_records = $conn->query("SELECT COUNT(*) as count 
                                         FROM attendance 
                                         WHERE user_id='$user_id' 
                                         AND MONTH(time_in) = $currentMonth 
                                         AND YEAR(time_in) = $currentYear");
            $record_count = $check_records->fetch_assoc()['count'];
            
            if ($record_count == 0): ?>
                <div class="no-records">
                    <p>You have no attendance records for <?= date('F Y') ?>.</p>
                </div>
            <?php else: ?>
            <table border="1" width="100%" class="payroll-table">
                <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Hours Worked</th>
                    <th>Daily Pay</th>
                </tr>
                <?php
                $total_hours = 0;
                $total_pay = 0;
                
                $attendance = $conn->query("SELECT DATE(time_in) as date, time_in, time_out 
                                         FROM attendance 
                                         WHERE user_id='$user_id' 
                                         AND MONTH(time_in) = $currentMonth 
                                         AND YEAR(time_in) = $currentYear 
                                         ORDER BY time_in DESC");
                
                while ($day = $attendance->fetch_assoc()) {
                    $hours = calc_hours($day['time_in'], $day['time_out']);
                    $daily_pay = $hours * $rate;
                    $total_hours += $hours;
                    $total_pay += $daily_pay;
                    
                    echo "<tr>";
                    echo "<td>" . date('M d, Y', strtotime($day['date'])) . "</td>";
                    echo "<td>" . date('h:i A', strtotime($day['time_in'])) . "</td>";
                    echo "<td>" . ($day['time_out'] ? date('h:i A', strtotime($day['time_out'])) : 'Not Out') . "</td>";
                    echo "<td>" . number_format($hours, 2) . "</td>";
                    echo "<td>₱" . number_format($daily_pay, 2) . "</td>";
                    echo "</tr>";
                }
                ?>
                <tr class="total-row">
                    <td colspan="3"><strong>Totals</strong></td>
                    <td><strong><?= number_format($total_hours, 2) ?></strong></td>
                    <td><strong>₱<?= number_format($total_pay, 2) ?></strong></td>
                </tr>
            </table>
            
            <div class="summary-box">
                <h3>Monthly Summary</h3>
                <p>Total Hours Worked: <strong><?= number_format($total_hours, 2) ?> hrs</strong></p>
                <p>Hourly Rate: <strong>₱<?= number_format($rate, 2) ?></strong></p>
                <p>Total Pay: <strong>₱<?= number_format($total_pay, 2) ?></strong></p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>

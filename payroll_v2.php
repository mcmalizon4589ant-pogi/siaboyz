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
    return min($hours, 8); // Max 8 hours per day
}

// Get cutoff period
$current_cutoff = isset($_GET['cutoff']) ? $_GET['cutoff'] : 'current';
$selected_staff = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : null;

// Calculate date ranges for cutoffs
$current_year = date('Y');
$current_month = date('m');
$current_day = date('d');

// Determine which cutoff period we're in
if ($current_day <= 15) {
    // We're in the 1-15 period
    $current_cutoff_start = date('Y-m-01');
    $current_cutoff_end = date('Y-m-15');
    $prev_cutoff_start = date('Y-m-16', strtotime('first day of last month'));
    $prev_cutoff_end = date('Y-m-t', strtotime('last day of last month'));
} else {
    // We're in the 16-end period
    $current_cutoff_start = date('Y-m-16');
    $current_cutoff_end = date('Y-m-t');
    $prev_cutoff_start = date('Y-m-01');
    $prev_cutoff_end = date('Y-m-15');
}

// Set date range based on selected cutoff
if ($current_cutoff == 'current') {
    $start_date = $current_cutoff_start;
    $end_date = $current_cutoff_end;
    $period_label = date('F d', strtotime($start_date)) . ' - ' . date('d, Y', strtotime($end_date));
} else {
    $start_date = $prev_cutoff_start;
    $end_date = $prev_cutoff_end;
    $period_label = date('F d', strtotime($start_date)) . ' - ' . date('d, Y', strtotime($end_date));
}

$rate = 85; // Rate per hour
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll System</title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .payroll-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .cutoff-selector, .staff-selector {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        .cutoff-selector select, .staff-selector select {
            padding: 8px 12px;
            margin-left: 10px;
            min-width: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .payroll-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .payroll-table th {
            background-color: #fafafa;
            color: #333;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .payroll-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
        .payroll-table tr:hover {
            background-color: #f8f9fa;
        }
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
            font-size: 1.1em;
        }
        .summary-box {
            background: #fff;
            color: #333;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #e5e5e5;
        }
        .summary-box h3 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-item:last-child {
            border-bottom: none;
            font-size: 1.3em;
            padding-top: 15px;
            margin-top: 10px;
            border-top: 2px solid #333;
            font-weight: 700;
        }
        .no-records {
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        .no-records p {
            color: #6c757d;
            font-size: 1.2em;
            margin: 10px 0;
        }
        .btn-print {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s;
        }
        .btn-print:hover {
            background: #2563eb;
        }
        @media print {
            .sidebar, .cutoff-selector, .staff-selector, .btn-print {
                display: none;
            }
            .summary-box {
                background: #fff !important;
                color: black !important;
                border: 2px solid #333;
            }
            .payroll-table th {
                background: #fafafa !important;
                color: #333 !important;
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
                <?php if ($role === 'Owner'): ?>
                    <li><a href="owner_dashboard.php">Dashboard</a></li>
                    <li><a href="staff_list.php">Staff List</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="payroll_v2.php" class="active">Payroll</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php else: ?>
                    <li><a href="staff_dashboard.php">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="payroll.php">Payroll</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1>Payroll System - Cutoff Based</h1>
            <p>Payroll Period: <strong><?= $period_label ?></strong></p>
        </header>

        <div class="payroll-header">
            <!-- Cutoff Period Selector -->
            <div class="cutoff-selector">
                <label>Cutoff Period:</label>
                <select onchange="window.location.href='?cutoff=' + this.value + '<?= $selected_staff ? '&staff_id='.$selected_staff : '' ?>'">
                    <option value="current" <?= $current_cutoff == 'current' ? 'selected' : '' ?>>Current Period</option>
                    <option value="previous" <?= $current_cutoff == 'previous' ? 'selected' : '' ?>>Previous Period</option>
                </select>
            </div>

            <?php if ($role === 'Owner'): ?>
            <!-- Staff Selection Dropdown -->
            <div class="staff-selector">
                <label>Select Staff:</label>
                <select onchange="window.location.href='?cutoff=<?= $current_cutoff ?>&staff_id=' + this.value">
                    <option value="">-- All Staff --</option>
                    <?php
                    $staffs = $conn->query("SELECT id, name FROM users WHERE role='Staff' ORDER BY name");
                    while ($staff = $staffs->fetch_assoc()) {
                        $selected = ($selected_staff == $staff['id']) ? 'selected' : '';
                        echo "<option value='{$staff['id']}' {$selected}>{$staff['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php
        if ($role === 'Owner' && $selected_staff) {
            // Show specific staff payroll
            $target_user_id = $selected_staff;
            $staff_query = $conn->query("SELECT name FROM users WHERE id='$target_user_id'");
            $staff_info = $staff_query->fetch_assoc();
            $display_name = $staff_info['name'];
        } elseif ($role === 'Owner' && !$selected_staff) {
            // Show all staff summary
            $display_name = 'All Staff';
            $target_user_id = null;
        } else {
            // Staff viewing their own
            $target_user_id = $user_id;
            $display_name = $name;
        }

        // Check for records
        $where_clause = $target_user_id ? "user_id='$target_user_id' AND" : "";
        $check_query = "SELECT COUNT(*) as count FROM attendance 
                       WHERE $where_clause DATE(time_in) BETWEEN '$start_date' AND '$end_date'";
        $check_result = $conn->query($check_query);
        $record_count = $check_result->fetch_assoc()['count'];

        if ($record_count == 0):
        ?>
            <div class="no-records">
                <p>📋 No attendance records found</p>
                <p>for <?= $display_name ?> in this payroll period.</p>
            </div>
        <?php else: ?>
            
            <?php if ($target_user_id): ?>
                <!-- Individual Staff Detailed View -->
                <h2>Payroll for: <?= htmlspecialchars($display_name) ?></h2>
                
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Hours Worked</th>
                            <th>Daily Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_hours = 0;
                        $total_pay = 0;
                        
                        $attendance_query = "SELECT DATE(time_in) as date, time_in, time_out 
                                           FROM attendance 
                                           WHERE user_id='$target_user_id' 
                                           AND DATE(time_in) BETWEEN '$start_date' AND '$end_date'
                                           ORDER BY time_in DESC";
                        $attendance = $conn->query($attendance_query);
                        
                        while ($day = $attendance->fetch_assoc()) {
                            $hours = calc_hours($day['time_in'], $day['time_out']);
                            $daily_pay = $hours * $rate;
                            $total_hours += $hours;
                            $total_pay += $daily_pay;
                            
                            echo "<tr>";
                            echo "<td>" . date('M d, Y (D)', strtotime($day['date'])) . "</td>";
                            echo "<td>" . date('h:i A', strtotime($day['time_in'])) . "</td>";
                            echo "<td>" . ($day['time_out'] ? date('h:i A', strtotime($day['time_out'])) : '<em>Not Out</em>') . "</td>";
                            echo "<td>" . number_format($hours, 2) . " hrs</td>";
                            echo "<td>₱" . number_format($daily_pay, 2) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3"><strong>TOTAL</strong></td>
                            <td><strong><?= number_format($total_hours, 2) ?> hrs</strong></td>
                            <td><strong>₱<?= number_format($total_pay, 2) ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="summary-box">
                    <h3>Payslip Summary</h3>
                    <div class="summary-item">
                        <span>Employee Name:</span>
                        <span><?= htmlspecialchars($display_name) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Pay Period:</span>
                        <span><?= $period_label ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Hours:</span>
                        <span><?= number_format($total_hours, 2) ?> hours</span>
                    </div>
                    <div class="summary-item">
                        <span>Hourly Rate:</span>
                        <span>₱<?= number_format($rate, 2) ?></span>
                    </div>
                    <div class="summary-item">
                        <span>TOTAL PAY:</span>
                        <span>₱<?= number_format($total_pay, 2) ?></span>
                    </div>
                    <button class="btn-print" onclick="window.print()">Print Payslip</button>
                </div>
                
            <?php else: ?>
                <!-- All Staff Summary View (Owner Only) -->
                <h2>All Staff Payroll Summary</h2>
                
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>Total Hours</th>
                            <th>Hourly Rate</th>
                            <th>Total Pay</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $grand_total_hours = 0;
                        $grand_total_pay = 0;
                        
                        $staff_list = $conn->query("SELECT id, name FROM users WHERE role='Staff' ORDER BY name");
                        
                        while ($staff = $staff_list->fetch_assoc()) {
                            $sid = $staff['id'];
                            $staff_hours = 0;
                            
                            $staff_attendance = $conn->query("SELECT time_in, time_out 
                                                             FROM attendance 
                                                             WHERE user_id='$sid' 
                                                             AND DATE(time_in) BETWEEN '$start_date' AND '$end_date'");
                            
                            while ($record = $staff_attendance->fetch_assoc()) {
                                $staff_hours += calc_hours($record['time_in'], $record['time_out']);
                            }
                            
                            $staff_pay = $staff_hours * $rate;
                            $grand_total_hours += $staff_hours;
                            $grand_total_pay += $staff_pay;
                            
                            if ($staff_hours > 0) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($staff['name']) . "</td>";
                                echo "<td>" . number_format($staff_hours, 2) . " hrs</td>";
                                echo "<td>₱" . number_format($rate, 2) . "</td>";
                                echo "<td>₱" . number_format($staff_pay, 2) . "</td>";
                                echo "<td><a href='?cutoff=$current_cutoff&staff_id=$sid' style='color: #007bff; text-decoration: none;'>View Details →</a></td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td><strong>GRAND TOTAL</strong></td>
                            <td><strong><?= number_format($grand_total_hours, 2) ?> hrs</strong></td>
                            <td></td>
                            <td><strong>₱<?= number_format($grand_total_pay, 2) ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="summary-box">
                    <h3>Overall Summary</h3>
                    <div class="summary-item">
                        <span>Pay Period:</span>
                        <span><?= $period_label ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Staff Hours:</span>
                        <span><?= number_format($grand_total_hours, 2) ?> hours</span>
                    </div>
                    <div class="summary-item">
                        <span>TOTAL PAYROLL:</span>
                        <span>₱<?= number_format($grand_total_pay, 2) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </main>
</div>
</body>
</html>

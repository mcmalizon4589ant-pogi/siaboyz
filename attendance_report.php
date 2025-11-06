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

// Set work schedule parameters
$work_start_time = '08:00:00'; // 8:00 AM
$late_threshold = '08:15:00';   // Late if after 8:15 AM
$work_end_time = '17:00:00';    // 5:00 PM
$early_leave_threshold = '16:45:00'; // Early if before 4:45 PM

// Get date range filter
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$selected_staff = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : null;

// For staff, only show their own data
if ($role !== 'Owner') {
    $selected_staff = $user_id;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Report</title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .filter-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-container label {
            font-weight: 600;
            color: #333;
        }
        .filter-container select,
        .filter-container input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 2em;
            color: #333;
        }
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        .stat-card.present h3 { color: #28a745; }
        .stat-card.absent h3 { color: #dc3545; }
        .stat-card.late h3 { color: #ffc107; }
        .stat-card.early h3 { color: #17a2b8; }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .report-table th {
            background: #fafafa;
            color: #333;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
        .report-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-present {
            background: #d4edda;
            color: #155724;
        }
        .badge-absent {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-late {
            background: #fff3cd;
            color: #856404;
        }
        .badge-early {
            background: #d1ecf1;
            color: #0c5460;
        }
        .badge-ontime {
            background: #d4edda;
            color: #155724;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        @media print {
            .sidebar, .filter-container {
                display: none;
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
                    <li><a href="attendance_report.php" class="active">Attendance Report</a></li>
                    <li><a href="payroll_v2.php">Payroll</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php else: ?>
                    <li><a href="staff_dashboard.php">Dashboard</a></li>
                    <li><a href="attendance.php">Attendance</a></li>
                    <li><a href="attendance_report.php" class="active">My Attendance Report</a></li>
                    <li><a href="payroll.php">Payroll</a></li>
                    <li><a href="settings.php">Settings</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <main class="main-content">
        <header>
            <h1><?= $role === 'Owner' ? 'Attendance Report' : 'My Attendance Report' ?></h1>
            <p>Track attendance, late arrivals, and absences</p>
        </header>

        <!-- Filters -->
        <div class="filter-container">
            <label>Month:</label>
            <input type="month" value="<?= $filter_month ?>" 
                   onchange="window.location.href='?month=' + this.value + '<?= $selected_staff && $role === 'Owner' ? '&staff_id='.$selected_staff : '' ?>'">
            
            <?php if ($role === 'Owner'): ?>
                <label>Staff:</label>
                <select onchange="window.location.href='?month=<?= $filter_month ?>&staff_id=' + this.value">
                    <option value="">All Staff</option>
                    <?php
                    $staff_list = $conn->query("SELECT id, name FROM users WHERE role='Staff' ORDER BY name");
                    while ($staff = $staff_list->fetch_assoc()) {
                        $selected = ($selected_staff == $staff['id']) ? 'selected' : '';
                        echo "<option value='{$staff['id']}' {$selected}>{$staff['name']}</option>";
                    }
                    ?>
                </select>
            <?php endif; ?>
        </div>

        <?php
        // Build query based on filters
        $where_conditions = ["DATE_FORMAT(a.time_in, '%Y-%m') = '$filter_month'"];
        if ($selected_staff) {
            $where_conditions[] = "a.user_id = $selected_staff";
        } elseif ($role !== 'Owner') {
            $where_conditions[] = "a.user_id = $user_id";
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        // Get all attendance records
        $query = "SELECT a.*, u.name as staff_name, DATE(a.time_in) as date,
                  TIME(a.time_in) as time_in_only, TIME(a.time_out) as time_out_only
                  FROM attendance a
                  JOIN users u ON a.user_id = u.id
                  $where_clause
                  ORDER BY a.time_in DESC";
        
        $records = $conn->query($query);
        
        // Calculate statistics
        $total_days = 0;
        $present_days = 0;
        $late_count = 0;
        $early_leave_count = 0;
        $perfect_attendance = 0;
        
        $attendance_data = [];
        while ($row = $records->fetch_assoc()) {
            $attendance_data[] = $row;
            $present_days++;
            
            $time_in = $row['time_in_only'];
            $time_out = $row['time_out_only'];
            
            // Check if late
            $is_late = ($time_in > $late_threshold);
            if ($is_late) $late_count++;
            
            // Check if early leave
            $is_early = ($time_out && $time_out < $early_leave_threshold);
            if ($is_early) $early_leave_count++;
            
            // Check perfect attendance (on time and stayed until end)
            if (!$is_late && !$is_early && $time_out) {
                $perfect_attendance++;
            }
        }
        
        // Calculate working days in month
        $month_start = $filter_month . '-01';
        $month_end = date('Y-m-t', strtotime($month_start));
        $working_days = 0;
        
        $current = strtotime($month_start);
        $end = strtotime($month_end);
        
        while ($current <= $end) {
            $day = date('N', $current);
            if ($day < 6) { // Monday to Friday
                $working_days++;
            }
            $current = strtotime('+1 day', $current);
        }
        
        $absent_days = $working_days - $present_days;
        ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card present">
                <h3><?= $present_days ?></h3>
                <p>Days Present</p>
            </div>
            <div class="stat-card absent">
                <h3><?= $absent_days ?></h3>
                <p>Days Absent</p>
            </div>
            <div class="stat-card late">
                <h3><?= $late_count ?></h3>
                <p>Late Arrivals</p>
            </div>
            <div class="stat-card early">
                <h3><?= $early_leave_count ?></h3>
                <p>Early Departures</p>
            </div>
        </div>

        <!-- Detailed Report Table -->
        <h2>Detailed Attendance Records</h2>
        <?php if (count($attendance_data) > 0): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <?php if ($role === 'Owner' && !$selected_staff): ?>
                        <th>Staff Name</th>
                    <?php endif; ?>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_data as $record): 
                    $time_in = $record['time_in_only'];
                    $time_out = $record['time_out_only'];
                    
                    // Determine status
                    $is_late = ($time_in > $late_threshold);
                    $is_early = ($time_out && $time_out < $early_leave_threshold);
                    
                    $status = 'Present';
                    $status_class = 'badge-present';
                    $remarks = [];
                    
                    if ($is_late) {
                        $status = 'Late';
                        $status_class = 'badge-late';
                        $late_by = (strtotime($time_in) - strtotime($late_threshold)) / 60;
                        $remarks[] = 'Late by ' . round($late_by) . ' minutes';
                    }
                    
                    if ($is_early) {
                        $early_by = (strtotime($early_leave_threshold) - strtotime($time_out)) / 60;
                        $remarks[] = 'Left ' . round($early_by) . ' minutes early';
                    }
                    
                    if (!$time_out) {
                        $remarks[] = 'No time out recorded';
                    }
                    
                    if (empty($remarks) && $time_out) {
                        $remarks[] = 'Perfect attendance';
                        $status_class = 'badge-ontime';
                    }
                ?>
                <tr>
                    <?php if ($role === 'Owner' && !$selected_staff): ?>
                        <td><strong><?= htmlspecialchars($record['staff_name']) ?></strong></td>
                    <?php endif; ?>
                    <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                    <td><?= date('l', strtotime($record['date'])) ?></td>
                    <td><?= date('h:i A', strtotime($record['time_in'])) ?></td>
                    <td><?= $time_out ? date('h:i A', strtotime($record['time_out'])) : '<em>-</em>' ?></td>
                    <td><span class="badge <?= $status_class ?>"><?= $status ?></span></td>
                    <td><?= implode(', ', $remarks) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <p>No attendance records found for the selected period.</p>
        </div>
        <?php endif; ?>

        <?php if ($role === 'Owner' && !$selected_staff): ?>
        <!-- Absent Staff List -->
        <h2 style="margin-top: 40px;">Staff Absent in <?= date('F Y', strtotime($filter_month)) ?></h2>
        <?php
        // Get all staff and check who's absent
        $all_staff = $conn->query("SELECT id, name FROM users WHERE role='Staff' ORDER BY name");
        $absent_staff = [];
        
        while ($staff = $all_staff->fetch_assoc()) {
            $staff_id = $staff['id'];
            $check = $conn->query("SELECT COUNT(*) as count FROM attendance 
                                  WHERE user_id='$staff_id' 
                                  AND DATE_FORMAT(time_in, '%Y-%m') = '$filter_month'");
            $result = $check->fetch_assoc();
            $staff_present = $result['count'];
            $staff_absent = $working_days - $staff_present;
            
            if ($staff_absent > 0) {
                $absent_staff[] = [
                    'name' => $staff['name'],
                    'present' => $staff_present,
                    'absent' => $staff_absent
                ];
            }
        }
        
        if (count($absent_staff) > 0): ?>
        <table class="report-table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Days Present</th>
                    <th>Days Absent</th>
                    <th>Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($absent_staff as $staff): 
                    $rate = ($staff['present'] / $working_days) * 100;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($staff['name']) ?></strong></td>
                    <td><?= $staff['present'] ?></td>
                    <td><?= $staff['absent'] ?></td>
                    <td><?= number_format($rate, 1) ?>%</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">
            <p>All staff have perfect attendance for this month!</p>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </main>
</div>
</body>
</html>

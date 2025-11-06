<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Owner') {
    header("Location: login.php");
    exit;
}

$owner_id = $_SESSION['user_id'];
$owner_name = $_SESSION['name'];

// Fetch archived employees
$sql = "SELECT * FROM archived_employees ORDER BY date_terminated DESC, archived_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Staff - W.I.Y Laundry Shop</title>
    <link rel="stylesheet" href="ownercss.css">
    <style>
        .archived-container {
            display: flex;
            min-height: 100vh;
        }
        
        .archived-main {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .archived-header {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .archived-header h1 {
            margin: 0 0 5px 0;
            color: #1d4ed8;
            font-size: 28px;
        }
        
        .archived-header p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #2563eb;
        }
        
        .archived-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .archived-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .archived-table thead {
            background: #fafafa;
        }
        
        .archived-table th {
            padding: 14px;
            text-align: left;
            font-weight: 600;
            color: #1d4ed8;
            border-bottom: 2px solid #e5e7eb;
            font-size: 14px;
        }
        
        .archived-table td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            color: #333;
            font-size: 14px;
        }
        
        .archived-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .position-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .position-trainee {
            background: #fff3cd;
            color: #856404;
        }
        
        .position-supervisor, .position-admin, .position-manager {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #f8d7da;
            color: #721c24;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
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
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 32px;
            color: #1d4ed8;
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .reason-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .details-btn {
            padding: 6px 12px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s;
        }
        
        .details-btn:hover {
            background: #2563eb;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-content h2 {
            margin-top: 0;
            color: #1d4ed8;
        }
        
        .modal-close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #999;
            cursor: pointer;
            line-height: 20px;
        }
        
        .modal-close:hover {
            color: #333;
        }
        
        .detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            width: 180px;
            flex-shrink: 0;
        }
        
        .detail-value {
            color: #333;
            flex: 1;
        }
    </style>
</head>
<body>
<div class="archived-container">
    <?php include 'sidebar_owner.php'; ?>
    
    <main class="archived-main">
        <div class="archived-header">
            <h1>Archived Staff Records</h1>
            <p>History of terminated employees</p>
        </div>
        
        <?php
        // Calculate statistics
        $total_archived = $result->num_rows;
        
        // Get current year terminations
        $current_year = date('Y');
        $year_sql = "SELECT COUNT(*) as count FROM archived_employees WHERE YEAR(date_terminated) = $current_year";
        $year_result = $conn->query($year_sql);
        $year_count = $year_result->fetch_assoc()['count'];
        
        // Get average days worked
        $avg_sql = "SELECT AVG(total_days_worked) as avg_days FROM archived_employees WHERE total_days_worked IS NOT NULL";
        $avg_result = $conn->query($avg_sql);
        $avg_days = $avg_result->fetch_assoc()['avg_days'];
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= $total_archived ?></h3>
                <p>Total Archived Staff</p>
            </div>
            <div class="stat-card">
                <h3><?= $year_count ?></h3>
                <p>Terminated in <?= $current_year ?></p>
            </div>
            <div class="stat-card">
                <h3><?= $avg_days ? round($avg_days) : 0 ?></h3>
                <p>Avg. Days Worked</p>
            </div>
        </div>
        
        <section class="archived-section">
            <h2 style="margin-top:0; color:#1d4ed8;">Archived Employees</h2>
            
            <?php if ($total_archived > 0): ?>
            <table class="archived-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Date Hired</th>
                        <th>Date Terminated</th>
                        <th>Days Worked</th>
                        <th>Final Salary</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $result->data_seek(0); // Reset pointer
                    while($archived = $result->fetch_assoc()): 
                        // Position badge class
                        $position_class = 'position-badge';
                        $position_value = isset($archived['position']) ? strtolower($archived['position']) : '';
                        if ($position_value == 'trainee') $position_class .= ' position-trainee';
                        elseif (in_array($position_value, ['supervisor', 'admin', 'manager'])) $position_class .= ' position-' . $position_value;
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($archived['name']); ?></strong></td>
                        <td>
                            <?php if ($archived['position']): ?>
                                <span class="<?= $position_class ?>"><?= htmlspecialchars($archived['position']); ?></span>
                            <?php else: ?>
                                <em style="color:#999;">N/A</em>
                            <?php endif; ?>
                        </td>
                        <td><?= $archived['date_hired'] ? date('M d, Y', strtotime($archived['date_hired'])) : '<em style="color:#999;">N/A</em>'; ?></td>
                        <td><?= $archived['date_terminated'] ? date('M d, Y', strtotime($archived['date_terminated'])) : '<em style="color:#999;">N/A</em>'; ?></td>
                        <td><?= $archived['total_days_worked'] !== null ? number_format($archived['total_days_worked']) . ' days' : '<em style="color:#999;">N/A</em>'; ?></td>
                        <td>₱<?= $archived['final_salary'] ? number_format($archived['final_salary'], 2) : '0.00'; ?></td>
                        <td class="reason-cell" title="<?= htmlspecialchars($archived['termination_reason']); ?>">
                            <?= htmlspecialchars($archived['termination_reason']); ?>
                        </td>
                        <td>
                            <button class="details-btn" onclick='showDetails(<?= json_encode($archived, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>View Details</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-data">
                <p>No archived staff records found.</p>
            </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<!-- Details Modal -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <h2 id="modalName"></h2>
        <div id="modalDetails"></div>
    </div>
</div>

<script>
function showDetails(archived) {
    const modal = document.getElementById('detailsModal');
    const modalName = document.getElementById('modalName');
    const modalDetails = document.getElementById('modalDetails');
    
    modalName.textContent = archived.name;
    
    let html = '';
    
    html += '<div class="detail-row"><div class="detail-label">Email:</div><div class="detail-value">' + (archived.email || 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Contact Number:</div><div class="detail-value">' + (archived.contact_number || 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Address:</div><div class="detail-value">' + (archived.address || 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Position:</div><div class="detail-value">' + (archived.position || 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Role:</div><div class="detail-value">' + (archived.role || 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Date Hired:</div><div class="detail-value">' + (archived.date_hired ? new Date(archived.date_hired).toLocaleDateString('en-US', {month: 'long', day: 'numeric', year: 'numeric'}) : 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Date Terminated:</div><div class="detail-value">' + (archived.date_terminated ? new Date(archived.date_terminated).toLocaleDateString('en-US', {month: 'long', day: 'numeric', year: 'numeric'}) : 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Total Days Worked:</div><div class="detail-value">' + (archived.total_days_worked !== null ? archived.total_days_worked.toLocaleString() + ' days' : 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Final Salary:</div><div class="detail-value">₱' + (archived.final_salary ? parseFloat(archived.final_salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Termination Reason:</div><div class="detail-value">' + (archived.termination_reason || 'N/A') + '</div></div>';
    html += '<div class="detail-row"><div class="detail-label">Archived At:</div><div class="detail-value">' + (archived.archived_at ? new Date(archived.archived_at).toLocaleString('en-US', {month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'}) : 'N/A') + '</div></div>';
    
    if (archived.notes) {
        html += '<div class="detail-row"><div class="detail-label">Notes:</div><div class="detail-value">' + archived.notes + '</div></div>';
    }
    
    modalDetails.innerHTML = html;
    modal.style.display = 'block';
}

function closeModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('detailsModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>
</body>
</html>

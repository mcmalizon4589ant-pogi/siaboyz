<?php
// sidebar_owner.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" style="width: 260px; background-color: #f8f9fa; padding: 20px; height: 100vh; position: fixed;">
    <h2 style="font-weight: 700; font-size: 22px; margin-bottom: 30px;">W.I.Y Laundry</h2>

    <nav>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 15px;">
                <a href="owner_dashboard.php"
                   class="<?= $currentPage == 'owner_dashboard.php' ? 'active' : '' ?>"
                   style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'owner_dashboard.php' ? '#e0ecff' : 'transparent' ?>;">
                   Dashboard
                </a>
            </li>
            <li style="margin-bottom: 15px;">
                <a href="staff_list.php"
                   class="<?= $currentPage == 'staff_list.php' ? 'active' : '' ?>"
                   style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'staff_list.php' ? '#e0ecff' : 'transparent' ?>;">
                   Staff List
                </a>
            </li>
            <li style="margin-bottom: 15px;">
                <a href="attendance.php"
                   class="<?= $currentPage == 'attendance.php' ? 'active' : '' ?>"
                   style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'attendance.php' ? '#e0ecff' : 'transparent' ?>;">
                   Attendance
                </a>
            </li>
            <li style="margin-bottom: 15px;">
                <a href="payroll_v2.php"
                   class="<?= $currentPage == 'payroll_v2.php' ? 'active' : '' ?>"
                   style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'payroll_v2.php' ? '#e0ecff' : 'transparent' ?>;">
                   Payroll
                </a>
            </li>
            <li style="margin-bottom: 15px;">
                <a href="settings.php"
                   class="<?= $currentPage == 'settings.php' ? 'active' : '' ?>"
                   style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'settings.php' ? '#e0ecff' : 'transparent' ?>;">
                   Settings
                </a>
            </li>
        </ul>
    </nav>

    <a href="logout.php" style="display: block; margin-top: 50px; color: red; text-decoration: none;">Log Out</a>
</aside>

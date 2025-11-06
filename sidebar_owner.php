<?php
// sidebar_owner.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" style="width: 260px; background-color: #f8f9fa; padding: 20px; height: 100vh; position: fixed; overflow-y: auto;">
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
                <a href="#" onclick="toggleSettingsSubmenu(event)"
                   class="<?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? 'active' : '' ?>"
                   style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? '#e0ecff' : 'transparent' ?>; position: relative;">
                   Settings
                   <span id="settingsArrow" style="float: right; transition: transform 0.3s;">▼</span>
                </a>
                <ul id="settingsSubmenu" style="list-style: none; padding-left: 20px; margin: 5px 0 0 0; display: <?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? 'block' : 'none' ?>;">
                    <li style="margin-bottom: 10px;">
                        <a href="settings.php"
                           style="display: block; padding: 8px 15px; border-radius: 6px; text-decoration: none; color: #666; font-size: 14px; background-color: <?= $currentPage == 'settings.php' ? '#d0e0ff' : 'transparent' ?>;">
                           Profile
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="archived_staff.php"
                           style="display: block; padding: 8px 15px; border-radius: 6px; text-decoration: none; color: #666; font-size: 14px; background-color: <?= $currentPage == 'archived_staff.php' ? '#d0e0ff' : 'transparent' ?>;">
                           Archives
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <a href="logout.php" style="display: block; position: absolute; bottom: 20px; color: red; text-decoration: none; font-weight: 600;">Log Out</a>
</aside>

<script>
function toggleSettingsSubmenu(e) {
    e.preventDefault();
    const submenu = document.getElementById('settingsSubmenu');
    const arrow = document.getElementById('settingsArrow');
    
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.display = 'none';
        arrow.style.transform = 'rotate(0deg)';
    }
}
</script>

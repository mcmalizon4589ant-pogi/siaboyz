<?php
// sidebar_owner.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" style="width: 260px; background-color: #f8f9fa; padding: 20px 20px 70px 20px; height: 100vh; position: fixed; overflow-y: auto; box-sizing: border-box;">
    <div>
        <h2 style="font-weight: 700; font-size: 22px; margin-bottom: 30px;">W.I.Y Laundry</h2>

        <nav>
            <ul style="list-style: none; padding: 0; margin: 0;">
                <li style="margin-bottom: 15px;">
                    <a href="owner_dashboard.php"
                       class="<?= $currentPage == 'owner_dashboard.php' ? 'active' : '' ?>"
                       style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'owner_dashboard.php' ? '#e0ecff' : 'transparent' ?>; transition: background-color 0.3s;">
                       Dashboard
                    </a>
                </li>
                <li style="margin-bottom: 15px;">
                    <a href="staff_list.php"
                       class="<?= $currentPage == 'staff_list.php' ? 'active' : '' ?>"
                       style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'staff_list.php' ? '#e0ecff' : 'transparent' ?>; transition: background-color 0.3s;">
                       Staff List
                    </a>
                </li>
                <li style="margin-bottom: 15px;">
                    <a href="attendance.php"
                       class="<?= $currentPage == 'attendance.php' ? 'active' : '' ?>"
                       style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'attendance.php' ? '#e0ecff' : 'transparent' ?>; transition: background-color 0.3s;">
                       Attendance
                    </a>
                </li>
                <li style="margin-bottom: 15px;">
                    <a href="payroll_v2.php"
                       class="<?= $currentPage == 'payroll_v2.php' ? 'active' : '' ?>"
                       style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= $currentPage == 'payroll_v2.php' ? '#e0ecff' : 'transparent' ?>; transition: background-color 0.3s;">
                       Payroll
                    </a>
                </li>
                <li style="margin-bottom: 85px; position: relative;">
                    <a href="#" onclick="toggleSettingsSubmenu(event)"
                       class="<?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? 'active' : '' ?>"
                       style="display: block; padding: 10px 15px; border-radius: 8px; text-decoration: none; color: #333; background-color: <?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? '#e0ecff' : 'transparent' ?>; transition: background-color 0.3s;">
                       Settings
                       <span id="settingsArrow" style="float: right; transition: transform 0.3s;">▼</span>
                    </a>
                    <ul id="settingsSubmenu" style="list-style: none; padding: 0; margin: 0; position: absolute; top: 45px; left: 0; width: 100%; opacity: <?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? '1' : '0' ?>; visibility: <?= in_array($currentPage, ['settings.php', 'archived_staff.php']) ? 'visible' : 'hidden' ?>; transition: opacity 0.3s ease, visibility 0.3s ease;">
                        <li style="margin-bottom: 8px; padding-left: 20px;">
                            <a href="settings.php"
                               style="display: block; padding: 8px 15px; border-radius: 6px; text-decoration: none; color: #666; font-size: 14px; background-color: <?= $currentPage == 'settings.php' ? '#d0e0ff' : 'transparent' ?>; transition: background-color 0.3s;"
                               onmouseover="if('<?= $currentPage ?>' !== 'settings.php') this.style.backgroundColor='#f0f0f0';"
                               onmouseout="if('<?= $currentPage ?>' !== 'settings.php') this.style.backgroundColor='transparent';">
                               Profile
                            </a>
                        </li>
                        <li style="margin-bottom: 8px; padding-left: 20px;">
                            <a href="archived_staff.php"
                               style="display: block; padding: 8px 15px; border-radius: 6px; text-decoration: none; color: #666; font-size: 14px; background-color: <?= $currentPage == 'archived_staff.php' ? '#d0e0ff' : 'transparent' ?>; transition: background-color 0.3s;"
                               onmouseover="if('<?= $currentPage ?>' !== 'archived_staff.php') this.style.backgroundColor='#f0f0f0';"
                               onmouseout="if('<?= $currentPage ?>' !== 'archived_staff.php') this.style.backgroundColor='transparent';">
                               Archives
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>

    <a href="logout.php" style="display: block; position: fixed; bottom: 20px; left: 20px; width: 220px; color: red; text-decoration: none; font-weight: 600; z-index: 999;">Log Out</a>
</aside>

<script>
function toggleSettingsSubmenu(e) {
    e.preventDefault();
    const submenu = document.getElementById('settingsSubmenu');
    const arrow = document.getElementById('settingsArrow');
    
    if (submenu.style.opacity === '0' || submenu.style.opacity === '') {
        submenu.style.opacity = '1';
        submenu.style.visibility = 'visible';
        arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.opacity = '0';
        submenu.style.visibility = 'hidden';
        arrow.style.transform = 'rotate(0deg)';
    }
}

// Set arrow state on page load
document.addEventListener('DOMContentLoaded', function() {
    const submenu = document.getElementById('settingsSubmenu');
    const arrow = document.getElementById('settingsArrow');
    if (submenu && arrow) {
        const currentOpacity = window.getComputedStyle(submenu).opacity;
        if (currentOpacity === '1') {
            arrow.style.transform = 'rotate(180deg)';
        }
    }
});
</script>

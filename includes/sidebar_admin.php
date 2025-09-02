<?php
// Check if user is logged in and is an Admin (Position_ID 4)
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: ../logout.php");
    exit();
}
$current_page = basename($_SERVER['PHP_SELF']);

// เตรียมจำนวนแจ้งเตือนค้างอ่านสำหรับ badge บน sidebar
$sideNotifCount = 0;
try {
    require_once __DIR__ . '/db.php';
    $adminId = $_SESSION['user_id'];
    $res = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE emp_id = '{$conn->real_escape_string($adminId)}' AND is_read = 0");
    if ($res) {
        $row = $res->fetch_assoc();
        $sideNotifCount = (int)($row['c'] ?? 0);
    }
} catch (Throwable $e) {
    $sideNotifCount = 0;
}
?>
<div class="d-flex">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-white" style="width: 280px; height: 100vh; position:fixed; overflow-y:auto;">
        <a href="dashboard.php" class="d-flex align-items-center mb-1 me-md-auto link-dark text-decoration-none">
            <span class="fs-4">Leave System</span>
        </a>
        <div class="text-muted small mb-2">ผู้ดูแลระบบ</div>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-speedometer2 me-2"></i>แดชบอร์ด
                </a>
            </li>
            <li>
                <a href="manage_leave.php" class="nav-link <?php echo ($current_page == 'manage_leave.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-calendar-check me-2"></i>จัดการการลา
                </a>
            </li>
            <li>
                <a href="manage_employees.php" class="nav-link <?php echo ($current_page == 'manage_employees.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-people me-2"></i>จัดการพนักงาน
                </a>
            </li>
            <li>
                <a href="holidays.php" class="nav-link <?php echo ($current_page == 'holidays.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-calendar3 me-2"></i>จัดการวันหยุด
                </a>
            </li>
            <li>
                <a href="notifications.php" class="nav-link d-flex align-items-center <?php echo ($current_page == 'notifications.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-bell me-2"></i>การแจ้งเตือน
                    <span id="notification-badge-side"
                          class="badge rounded-pill bg-danger ms-auto"
                          style="<?php echo ($sideNotifCount > 0 ? 'display:inline-block;' : 'display:none;'); ?>">
                        <?php echo $sideNotifCount; ?>
                    </span>
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)</strong>
            </a>
            <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
                <li><a class="dropdown-item" href="../logout.php">ออกจากระบบ</a></li>
            </ul>
        </div>
    </div>
    <div style="width: 20px;"></div>
    <div class="container-fluid">

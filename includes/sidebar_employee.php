<?php
// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง และไม่ใช่แอดมิน (Position_ID 4 คือ Admin)
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] == 4) {
    header("Location: ../logout.php");
    exit();
}
// ดึงชื่อไฟล์ของหน้าปัจจุบันเพื่อใช้ไฮไลท์เมนูที่กำลังใช้งาน
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="d-flex">
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-white" style="width: 280px; height: 100vh; position:fixed;">
        <a href="dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
            <span class="fs-4">Leave System</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-house-door me-2"></i>หน้าหลัก
                </a>
            </li>
            <li>
                <a href="leave_request.php" class="nav-link <?php echo ($current_page == 'leave_request.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-file-earmark-text me-2"></i>ขอลางาน
                </a>
            </li>
            <li>
                <a href="history.php" class="nav-link <?php echo ($current_page == 'history.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-clock-history me-2"></i>ประวัติการลา
                </a>
            </li>
            <li>
                <a href="holidays.php" class="nav-link <?php echo ($current_page == 'holidays.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-calendar3 me-2"></i>วันหยุด
                </a>
            </li>
            <li>
                <a href="notifications.php" class="nav-link <?php echo ($current_page == 'employee/notifications.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-calendar3 me-2"></i>การแจ้งเตือน
                </a>
            </li>
        </ul>
        <hr>
        <?php
            // Get unread notification count
            $count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE emp_id = ? AND is_read = 0");
            $count_stmt->bind_param("s", $_SESSION['user_id']);
            $count_stmt->execute();
            $unread_count = $count_stmt->get_result()->fetch_assoc()['unread_count'];
            $count_stmt->close();
        ?>
    <div class="dropdown">
             <a href="notifications.php" class="d-flex align-items-center me-3 link-dark text-decoration-none position-relative">
                <i class="bi bi-bell-fill fs-5"></i>
                <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6em;">
                    <?php echo $unread_count; ?>
                </span>
                <?php endif; ?>
            </a>
            <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
            <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
                <li><a class="dropdown-item" href="profile.php">ข้อมูลส่วนตัว</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../logout.php">ออกจากระบบ</a></li>
            </ul>
        </div>
    </div>

    <div style="width: 20px;"></div>

    <div class="container-fluid">
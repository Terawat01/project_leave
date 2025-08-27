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

    <!-- Sidebar -->
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
                <a href="notifications.php" class="nav-link <?php echo ($current_page == 'notifications.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-bell me-2"></i>การแจ้งเตือน
                </a>
            </li>
            <li>
                <a href="profile.php" class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : 'link-dark'; ?>">
                    <i class="bi bi-person-circle me-2"></i>ข้อมูลส่วนตัว
                </a>
            </li>
        </ul>
        <hr>
        <!-- เหลือแค่ปุ่มออกจากระบบ -->
        <a class="btn btn-outline-danger w-100" href="../logout.php">
            <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
        </a>
    </div>

    <!-- Main Content -->
    <div style="width: 20px;"></div>
    <div class="container-fluid">

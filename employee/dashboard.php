<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

$emp_id = $_SESSION['user_id'];

// Get pending requests
$pending_stmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM emp_leave WHERE Emp_ID = ? AND Leave_Status_ID = '3'");
$pending_stmt->bind_param("s", $emp_id);
$pending_stmt->execute();
$pending_count = $pending_stmt->get_result()->fetch_assoc()['pending_count'];

// Get leave taken this year
$current_year = date('Y');
$taken_stmt = $conn->prepare("SELECT SUM(DATEDIFF(End_Leave_date, Start_leave_date) + 1) as days_taken FROM emp_leave WHERE Emp_ID = ? AND Leave_Status_ID = '1' AND YEAR(Start_leave_date) = ?");
$taken_stmt->bind_param("ss", $emp_id, $current_year);
$taken_stmt->execute();
$days_taken = $taken_stmt->get_result()->fetch_assoc()['days_taken'] ?? 0;

// Assume total leave entitlement (e.g., 15 days total per year)
$total_leave_entitlement = 15;
$remaining_leave = $total_leave_entitlement - $days_taken;

// Get recent leave requests
$recent_leaves_stmt = $conn->prepare("SELECT lt.Leave_Type_Name, el.Start_leave_date, ls.Leave_Type_Name as status
FROM emp_leave el
JOIN leave_type lt ON el.Leave_Type_ID = lt.Leave_Type_ID
JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
WHERE el.Emp_ID = ?
ORDER BY el.Request_date DESC LIMIT 5");
$recent_leaves_stmt->bind_param("s", $emp_id);
$recent_leaves_stmt->execute();
$recent_leaves = $recent_leaves_stmt->get_result();
?>

<div class="main-content p-4">
    <h4 class="mb-4">หน้าหลัก</h4>
    <p class="text-muted">ภาพรวมการลางานของคุณ</p>

<div class="row justify-content-center">
    <div class="col-md-3">
        <div class="card text-center p-3 mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?php echo $remaining_leave; ?> วัน</h5>
                <p class="card-text">วันลาคงเหลือ</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?php echo $pending_count; ?> รายการ</h5>
                <p class="card-text">คำขอรอดำเนินการ</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title"><?php echo $days_taken; ?> วัน</h5>
                <p class="card-text">ลาแล้วในปีนี้</p>
            </div>
        </div>
    </div>
</div>


    <div class="card mt-4">
        <div class="card-header">
            คำขอลาล่าสุดของคุณ
        </div>
        <div class="card-body">
            <?php if ($recent_leaves->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ประเภทการลา</th>
                        <th>วันที่เริ่มลา</th>
                        <th>สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $recent_leaves->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Leave_Type_Name']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['Start_leave_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-center text-muted">ยังไม่มีประวัติการขอลา</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: ../logout.php");
    exit();
}

require_once '../includes/db.php';

/* ========= 1) รับค่าฟิลเตอร์ ========= */
$search_name   = $_GET['search'] ?? '';   // ← ค้นหาจากชื่อพนักงาน
$status_filter = $_GET['status'] ?? '';
$type_filter   = $_GET['type']   ?? '';
$from_date     = $_GET['from']   ?? '';
$to_date       = $_GET['to']     ?? '';

/* ========= 2) ดึงรายการสำหรับ dropdown ========= */
$statuses    = $conn->query("SELECT Leave_Status_ID, Leave_Type_Name FROM leave_status");
$leave_types = $conn->query("SELECT Leave_Type_ID, Leave_Type_Name FROM leave_type");

/* ========= 3) Query หลัก + ตัวกรอง (Prepared) ========= */
$sql = "
    SELECT 
        el.Leave_ID,
        e.Emp_Name,
        lt.Leave_Type_Name,
        el.Start_leave_date,
        el.End_Leave_date,
        el.Reason,
        el.Leave_Status_ID,
        ls.Leave_Type_Name AS status_name
    FROM emp_leave el
    JOIN employee e     ON el.Emp_ID = e.Emp_id
    JOIN leave_type lt  ON el.Leave_Type_ID = lt.Leave_Type_ID
    JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
    WHERE 1=1
";

$params = [];
$types  = "";

// ค้นหาจากชื่อพนักงาน
if ($search_name !== '') {
    $sql    .= " AND e.Emp_Name LIKE ? ";
    $params[] = "%".$search_name."%";
    $types   .= "s";
}

// กรองสถานะ
if ($status_filter !== '') {
    $sql    .= " AND el.Leave_Status_ID = ? ";
    $params[] = $status_filter;
    $types   .= "s";
}

// กรองประเภทการลา
if ($type_filter !== '') {
    $sql    .= " AND el.Leave_Type_ID = ? ";
    $params[] = $type_filter;
    $types   .= "s";
}

// กรองช่วงวันที่แบบทับซ้อนช่วง
if ($from_date && $to_date) {
    $sql    .= " AND NOT (el.End_Leave_date < ? OR el.Start_leave_date > ?) ";
    $params[] = $from_date; $types .= "s";
    $params[] = $to_date;   $types .= "s";
} elseif ($from_date) {
    $sql    .= " AND el.End_Leave_date >= ? ";
    $params[] = $from_date; $types .= "s";
} elseif ($to_date) {
    $sql    .= " AND el.Start_leave_date <= ? ";
    $params[] = $to_date;   $types .= "s";
}

// เรียง: รอดำเนินการ → อนุมัติ → อื่น ๆ + วันที่เริ่มล่าสุดก่อน
$sql .= "
    ORDER BY
        CASE el.Leave_Status_ID
            WHEN '3' THEN 1
            WHEN '1' THEN 2
            ELSE 3
        END,
        el.Start_leave_date DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* ========= 4) แสดงผล ========= */
require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

// helper badge สี
function renderStatusBadge($id, $text) {
    if ($id == '1') return '<span class="badge bg-success">'.$text.'</span>';
    if ($id == '2') return '<span class="badge bg-danger">'.$text.'</span>';
    if ($id == '3') return '<span class="badge bg-warning text-dark">'.$text.'</span>';
    return '<span class="badge bg-secondary">'.$text.'</span>';
}
?>
<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">จัดการการลา</h4>
    </div>

    <!-- ฟอร์มค้นหา/กรอง (ปุ่มอยู่ในฟอร์มเดียวกัน) -->
    <form method="get" action="manage_leave.php" class="card mb-4" id="filterForm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <h5 class="card-title mb-2 mb-sm-0">
                    <i class="bi bi-funnel"></i> ค้นหาและกรอง
                </h5>
                <div class="ms-auto">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                    <a href="manage_leave.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> ล้างค่า
                    </a>
                </div>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">ค้นหาชื่อพนักงาน</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="เช่น สมชาย"
                        value="<?= htmlspecialchars($search_name) ?>"
                    >
                </div>

                <div class="col-md-2">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <?php if ($statuses && $statuses->num_rows > 0): ?>
                            <?php while($s = $statuses->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($s['Leave_Status_ID']) ?>"
                                    <?= ($status_filter !== '' && $status_filter == $s['Leave_Status_ID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['Leave_Type_Name']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">ประเภทการลา</label>
                    <select name="type" class="form-select">
                        <option value="">ทั้งหมด</option>
                        <?php if ($leave_types && $leave_types->num_rows > 0): ?>
                            <?php while($t = $leave_types->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($t['Leave_Type_ID']) ?>"
                                    <?= ($type_filter !== '' && $type_filter == $t['Leave_Type_ID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['Leave_Type_Name']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">วันที่เริ่ม (From)</label>
                    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
                </div>

                <div class="col-md-2">
                    <label class="form-label">วันที่สิ้นสุด (To)</label>
                    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
                </div>
            </div>
        </div>
    </form>

    <!-- ตารางรายการ -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>พนักงาน</th>
                            <th>ประเภท</th>
                            <th>วันที่เริ่มลา - วันที่สิ้นสุด</th>
                            <th>เหตุผล</th>
                            <th>สถานะ</th>
                            <th class="text-center">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Emp_Name']) ?></td>
                                <td><?= htmlspecialchars($row['Leave_Type_Name']) ?></td>
                                <td><?= htmlspecialchars($row['Start_leave_date']) ?> - <?= htmlspecialchars($row['End_Leave_date']) ?></td>
                                <td><?= htmlspecialchars($row['Reason'] ?: '-') ?></td>
                                <td><?= renderStatusBadge($row['Leave_Status_ID'], $row['status_name']) ?></td>
                                <td class="text-center">
                                    <?php if ($row['Leave_Status_ID'] == '3'): ?>
                                        <a href="manage_leave.php?action=approve&id=<?= urlencode($row['Leave_ID']) ?>" class="btn btn-success btn-sm">อนุมัติ</a>
                                        <a href="manage_leave.php?action=reject&id=<?= urlencode($row['Leave_ID']) ?>" class="btn btn-danger btn-sm">ปฏิเสธ</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">ไม่พบข้อมูลตามเงื่อนไข</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php';
$stmt->close();
$conn->close();

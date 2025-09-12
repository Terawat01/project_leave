<?php
// --- Auth: Admin only ---
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: ../logout.php");
    exit();
}

require_once '../includes/db.php';

/* =========================================================
   1) Handle actions (approve / reject / delete) — NO OUTPUT
   ========================================================= */
$action = $_GET['action'] ?? null;
$leave_id = $_GET['id'] ?? null;

if ($action && $leave_id) {
    if ($action === 'approve' || $action === 'reject') {
        $approved_date = date('Y-m-d');

        // ดึงข้อมูลเพื่อแจ้งเตือน
        $stmt = $conn->prepare("SELECT Emp_ID, Leave_Type_ID, Start_leave_date, End_Leave_date 
                                FROM emp_leave WHERE Leave_ID = ?");
        $stmt->bind_param("s", $leave_id);
        $stmt->execute();
        $leave_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($leave_row) {
            $emp_id_to_notify = $leave_row['Emp_ID'];
            $leave_type_id    = $leave_row['Leave_Type_ID'];
            $start_date_txt   = date('d M Y', strtotime($leave_row['Start_leave_date']));
            $end_date_txt     = date('d M Y', strtotime($leave_row['End_Leave_date']));

            // ชื่อประเภทการลา
            $stmt = $conn->prepare("SELECT Leave_Type_Name FROM leave_type WHERE Leave_Type_ID = ?");
            $stmt->bind_param("s", $leave_type_id);
            $stmt->execute();
            $leave_type_name = $stmt->get_result()->fetch_assoc()['Leave_Type_Name'] ?? 'ไม่ระบุ';
            $stmt->close();

            if ($action === 'approve') {
                $new_status_id = '1';
                $notif_type = 'approved';
                $notif_title = 'คำขอลาได้รับการอนุมัติ';
                $notif_msg = "คำขอลา $leave_type_name ของคุณ ($start_date_txt - $end_date_txt) ได้รับการอนุมัติแล้ว";
            } else { // reject
                $new_status_id = '2';
                $notif_type = 'rejected';
                $notif_title = 'คำขอลาถูกปฏิเสธ';
                $notif_msg = "คำขอลา $leave_type_name ของคุณ ($start_date_txt - $end_date_txt) ไม่ได้รับการอนุมัติ";
            }

            // อัปเดตสถานะ
            $stmt = $conn->prepare("UPDATE emp_leave SET Leave_Status_ID = ?, Approved_date = ? WHERE Leave_ID = ?");
            $stmt->bind_param("sss", $new_status_id, $approved_date, $leave_id);
            $stmt->execute();
            $stmt->close();

            // บันทึกแจ้งเตือน
            $stmt = $conn->prepare("INSERT INTO notifications (emp_id, type, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("ssss", $emp_id_to_notify, $notif_type, $notif_title, $notif_msg);
            $stmt->execute();
            $stmt->close();

            // ส่ง WebSocket
            $payload = json_encode(['emp_id' => $emp_id_to_notify, 'title' => $notif_title, 'message' => $notif_msg]);
            $ch = curl_init('http://localhost:8080');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_exec($ch);
            curl_close($ch);
        }

        header("Location: manage_leave.php");
        exit();
    }

    if ($action === 'delete') {
        // ดึงไฟล์แนบเพื่อลบออกจากโฟลเดอร์
        $stmt = $conn->prepare("SELECT Document_File FROM emp_leave WHERE Leave_ID = ?");
        $stmt->bind_param("s", $leave_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // ลบแถวในฐานข้อมูล
        $stmt = $conn->prepare("DELETE FROM emp_leave WHERE Leave_ID = ?");
        $stmt->bind_param("s", $leave_id);
        $stmt->execute();
        $stmt->close();

        // ลบไฟล์แนบ (ถ้ามี)
        if (!empty($row['Document_File'])) {
            $path = realpath(__DIR__ . '/../uploads/' . $row['Document_File']);
            $uploadsDir = realpath(__DIR__ . '/../uploads');
            if ($path && $uploadsDir && strpos($path, $uploadsDir) === 0 && file_exists($path)) {
                @unlink($path);
            }
        }

        header("Location: manage_leave.php?msg=deleted");
        exit();
    }
}

/* ===========================================
   2) Filters (สถานะ, ประเภท, ช่วงวันที่, ค้นหาชื่อ/เหตุผล)
   =========================================== */
$q            = trim($_GET['q'] ?? '');           // คีย์เวิร์ด: ชื่อพนักงาน หรือเหตุผล
$status       = $_GET['status'] ?? '';
$type         = $_GET['type'] ?? '';
$from_date    = $_GET['from'] ?? '';
$to_date      = $_GET['to'] ?? '';

$statuses    = $conn->query("SELECT Leave_Status_ID, Leave_Type_Name FROM leave_status");
$leave_types = $conn->query("SELECT Leave_Type_ID, Leave_Type_Name FROM leave_type");

/* ==============================
   3) Query รายการตามตัวกรอง
   ============================== */
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
    JOIN employee e      ON el.Emp_ID = e.Emp_id
    JOIN leave_type lt   ON el.Leave_Type_ID = lt.Leave_Type_ID
    JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
    WHERE 1=1
";

$params = [];
$typesBind = "";

// ค้นหาโดยชื่อพนักงานหรือเหตุผล
if ($q !== '') {
    $sql .= " AND (e.Emp_Name LIKE ? OR el.Reason LIKE ?) ";
    $kw = "%$q%";
    $params[] = $kw; $typesBind .= "s";
    $params[] = $kw; $typesBind .= "s";
}

// สถานะ
if ($status !== '') {
    $sql .= " AND el.Leave_Status_ID = ? ";
    $params[] = $status; $typesBind .= "s";
}

// ประเภท
if ($type !== '') {
    $sql .= " AND el.Leave_Type_ID = ? ";
    $params[] = $type; $typesBind .= "s";
}

// ช่วงวันที่ (ทับซ้อนช่วง)
if ($from_date && $to_date) {
    $sql .= " AND NOT (el.End_Leave_date < ? OR el.Start_leave_date > ?) ";
    $params[] = $from_date; $typesBind .= "s";
    $params[] = $to_date;   $typesBind .= "s";
} elseif ($from_date) {
    $sql .= " AND el.End_Leave_date >= ? ";
    $params[] = $from_date; $typesBind .= "s";
} elseif ($to_date) {
    $sql .= " AND el.Start_leave_date <= ? ";
    $params[] = $to_date;   $typesBind .= "s";
}

// เรียงลำดับ
$sql .= "
    ORDER BY
        CASE el.Leave_Status_ID WHEN '3' THEN 1 WHEN '1' THEN 2 ELSE 3 END,
        el.Start_leave_date DESC
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($typesBind, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

// helper badge
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
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <span class="text-success small"><i class="bi bi-check-circle"></i> ลบรายการเรียบร้อยแล้ว</span>
        <?php endif; ?>
    </div>

    <!-- ฟอร์มค้นหา/กรอง -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0"><i class="bi bi-funnel"></i> ค้นหาและกรอง</h5>
                <div>
                    <button type="submit" form="filterForm" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                    <a href="manage_leave.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> ล้างค่า
                    </a>
                </div>
            </div>

            <form id="filterForm" class="row g-3 align-items-end" method="get" action="manage_leave.php">
                <div class="col-md-3">
                    <label class="form-label">ค้นหา (ชื่อพนักงาน/เหตุผล)</label>
                    <input type="text" name="q" class="form-control" placeholder="เช่น ก้อง หรือ ลาป่วย"
                           value="<?= htmlspecialchars($q) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <?php if ($statuses && $statuses->num_rows > 0): while($s = $statuses->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($s['Leave_Status_ID']) ?>"
                                <?= ($status !== '' && $status == $s['Leave_Status_ID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['Leave_Type_Name']) ?>
                            </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">ประเภทการลา</label>
                    <select name="type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <?php if ($leave_types && $leave_types->num_rows > 0): while($t = $leave_types->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($t['Leave_Type_ID']) ?>"
                                <?= ($type !== '' && $type == $t['Leave_Type_ID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['Leave_Type_Name']) ?>
                            </option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label">วันที่เริ่ม</label>
                    <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from_date) ?>">
                </div>
                <div class="col-md-1">
                    <label class="form-label">สิ้นสุด</label>
                    <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to_date) ?>">
                </div>
            </form>
        </div>
    </div>

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
                                        <a href="manage_leave.php?action=approve&id=<?= urlencode($row['Leave_ID']) ?>"
                                           class="btn btn-success btn-sm">อนุมัติ</a>
                                        <a href="manage_leave.php?action=reject&id=<?= urlencode($row['Leave_ID']) ?>"
                                           class="btn btn-danger btn-sm">ปฏิเสธ</a>
                                    <?php endif; ?>
                                    <a href="manage_leave.php?action=delete&id=<?= urlencode($row['Leave_ID']) ?>"
                                       class="btn btn-outline-danger btn-sm ms-1"
                                       onclick="return confirm('ยืนยันการลบรายการลานี้? การลบจะถาวร');">
                                       ลบ
                                    </a>
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

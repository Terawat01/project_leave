<?php
require_once '../includes/db.php';

// ----- ยกเลิกคำขอ (ต้องทำก่อนมี output) -----
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    session_start();
    $leave_id_to_cancel = $_GET['id'];
    $current_emp_id = $_SESSION['user_id'] ?? '';

    if ($current_emp_id) {
        $stmtCancel = $conn->prepare("DELETE FROM emp_leave WHERE Leave_ID = ? AND Emp_ID = ? AND Leave_Status_ID = '3'");
        $stmtCancel->bind_param("ss", $leave_id_to_cancel, $current_emp_id);
        $stmtCancel->execute();
        $stmtCancel->close();
    }
    header("Location: history.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

$emp_id = $_SESSION['user_id'] ?? '';

// ----- รับค่าตัวกรองจาก URL -----
$search_query = $_GET['search'] ?? '';  // ค้นหา (ประเภท/เหตุผล)
$status_filter = $_GET['status'] ?? '';
$type_filter   = $_GET['type']   ?? '';
$from_date     = $_GET['from']   ?? '';
$to_date       = $_GET['to']     ?? '';

// ----- Query หลักพร้อมตัวกรอง -----
$sql = "
    SELECT
        el.Leave_ID,
        el.Start_leave_date,
        el.End_Leave_date,
        DATEDIFF(el.End_Leave_date, el.Start_leave_date) + 1 as total_days,
        el.Reason,
        el.Request_date,
        el.Approved_date,
        el.Document_File,
        lt.Leave_Type_Name,
        ls.Leave_Type_Name as status,
        el.Leave_Status_ID
    FROM emp_leave el
    JOIN leave_type lt ON el.Leave_Type_ID = lt.Leave_Type_ID
    JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
    WHERE el.Emp_ID = ?
";

$params = [$emp_id];
$types  = "s";

// ค้นหาข้อความ (ประเภทการลา/เหตุผล)
if ($search_query !== '') {
    $sql .= " AND (lt.Leave_Type_Name LIKE ? OR el.Reason LIKE ?)";
    $search_term = "%".$search_query."%";
    $params[] = $search_term; $types .= "s";
    $params[] = $search_term; $types .= "s";
}

// กรองสถานะ
if ($status_filter !== '') {
    $sql   .= " AND el.Leave_Status_ID = ? ";
    $params[] = $status_filter; $types .= "s";
}

// กรองประเภทการลา
if ($type_filter !== '') {
    $sql   .= " AND el.Leave_Type_ID = ? ";
    $params[] = $type_filter; $types .= "s";
}

// กรองช่วงวันที่แบบทับซ้อนช่วง (เหมือนฝั่งแอดมิน)
if ($from_date && $to_date) {
    $sql   .= " AND NOT (el.End_Leave_date < ? OR el.Start_leave_date > ?) ";
    $params[] = $from_date; $types .= "s";
    $params[] = $to_date;   $types .= "s";
} elseif ($from_date) {
    $sql   .= " AND el.End_Leave_date >= ? ";
    $params[] = $from_date; $types .= "s";
} elseif ($to_date) {
    $sql   .= " AND el.Start_leave_date <= ? ";
    $params[] = $to_date;   $types .= "s";
}

$sql .= " ORDER BY el.Start_leave_date DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // ป้องกันหน้าแตกกรณี prepare ล้มเหลว
    $result = new class {
        public $num_rows = 0;
        public function fetch_assoc(){ return null; }
    };
}

// ดึงสถานะ/ประเภทสำหรับ dropdown
$statuses    = $conn->query("SELECT Leave_Status_ID, Leave_Type_Name FROM leave_status");
$leave_types = $conn->query("SELECT Leave_Type_ID, Leave_Type_Name FROM leave_type");
?>

<div class="main-content p-4">
    <h4 class="mb-1">ประวัติการลา</h4>
    <p class="text-muted">ประวัติการลาทั้งหมดของคุณ</p>

    <!-- ฟอร์มค้นหา/กรอง -->
    <form method="GET" action="history.php" class="card mb-4" id="filterForm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                <h5 class="card-title mb-2 mb-sm-0">
                    <i class="bi bi-funnel"></i> ค้นหาและกรอง
                </h5>
                <div class="ms-auto">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                    <a href="history.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> ล้างค่า
                    </a>
                </div>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search" class="form-label">ค้นหา</label>
                    <input
                        type="text"
                        class="form-control"
                        name="search" id="search"
                        placeholder="ค้นหาประเภทการลาหรือเหตุผล..."
                        value="<?php echo htmlspecialchars($search_query); ?>">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">สถานะ</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <?php if ($statuses && $statuses->num_rows > 0): ?>
                            <?php while($s = $statuses->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($s['Leave_Status_ID']); ?>"
                                    <?php echo ($status_filter === $s['Leave_Status_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['Leave_Type_Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="type" class="form-label">ประเภทการลา</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <?php if ($leave_types && $leave_types->num_rows > 0): ?>
                            <?php while($lt = $leave_types->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($lt['Leave_Type_ID']); ?>"
                                    <?php echo ($type_filter === $lt['Leave_Type_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lt['Leave_Type_Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">วันที่เริ่ม (From)</label>
                    <input type="date" class="form-control" name="from" value="<?php echo htmlspecialchars($from_date); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">วันที่สิ้นสุด (To)</label>
                    <input type="date" class="form-control" name="to" value="<?php echo htmlspecialchars($to_date); ?>">
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history"></i>
            ประวัติการลา (<?php echo (int)$result->num_rows; ?> รายการ)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ประเภทการลา</th>
                            <th>วันที่เริ่มลา - วันที่สิ้นสุด</th>
                            <th>จำนวนวัน</th>
                            <th>เหตุผล</th>
                            <th>ไฟล์แนบ</th>
                            <th>สถานะ</th>
                            <th>วันที่อนุมัติ</th>
                            <th>วันที่ส่งคำขอ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Leave_Type_Name']); ?></td>
                                <td><?php
                                    echo date('d/m/Y', strtotime($row['Start_leave_date'])) .
                                         " ถึง " .
                                         date('d/m/Y', strtotime($row['End_Leave_date']));
                                ?></td>
                                <td><?php echo (int)$row['total_days']; ?></td>
                                <td><?php echo htmlspecialchars($row['Reason'] ?: '-'); ?></td>
                                <td>
                                    <?php if (!empty($row['Document_File'])):
                                        $file_path = "../uploads/" . $row['Document_File'];
                                        $ext = strtolower(pathinfo($row['Document_File'], PATHINFO_EXTENSION));
                                    ?>
                                        <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                                            <a href="#"
                                               data-bs-toggle="modal"
                                               data-bs-target="#imageModal"
                                               data-img="<?php echo $file_path; ?>">
                                                <img src="<?php echo $file_path; ?>" alt="แนบไฟล์"
                                                     style="width:50px; height:auto; border:1px solid #ccc; border-radius:5px;">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo $file_path; ?>" target="_blank" class="btn btn-sm btn-info">
                                                เปิดไฟล์
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php
                                    echo ($row['Approved_date'] && $row['Approved_date'] !== '0000-00-00')
                                        ? date('d/m/Y', strtotime($row['Approved_date'])) : '-';
                                ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['Request_date'])); ?></td>
                                <td>
                                    <?php if ($row['Leave_Status_ID'] == '3'): ?>
                                        <a href="?action=cancel&id=<?php echo urlencode($row['Leave_ID']); ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('คุณต้องการยกเลิกคำขอนี้ใช่หรือไม่?')">
                                           ยกเลิก
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">ไม่มีข้อมูลประวัติการลา</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal สำหรับดูรูปภาพแนบ -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">ไฟล์แนบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="modalImage" class="img-fluid" alt="ไฟล์แนบ">
      </div>
    </div>
  </div>
</div>

<script>
var imageModal = document.getElementById('imageModal');
imageModal.addEventListener('show.bs.modal', function (event) {
    var triggerLink = event.relatedTarget;
    var imgSrc = triggerLink.getAttribute('data-img');
    var modalImage = document.getElementById('modalImage');
    modalImage.src = imgSrc;
});
</script>

<?php
require_once '../includes/footer.php';
if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
$conn->close();
?>

<?php
// --- Admin only ---
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['position_id'] ?? null) != 4) {
    header("Location: ../logout.php");
    exit();
}

require_once '../includes/db.php';

/* ------------------ รับค่าตัวกรอง ------------------ */
$q        = trim($_GET['q'] ?? '');     // คีย์เวิร์ด: ชื่อ/รหัสพนักงาน/ประเภท/เหตุผล
$status   = $_GET['status'] ?? '';
$type     = $_GET['type']   ?? '';
$from     = $_GET['from']   ?? '';
$to       = $_GET['to']     ?? '';

/* ------------------ สร้างคำสั่ง SQL ------------------ */
/* หมายเหตุ: แอดมินเห็นทุกเรคคอร์ด จึงไม่กรองด้วย Emp_ID */
$sql = "
  SELECT
    el.Leave_ID,
    e.Emp_id,
    e.Emp_Name,
    lt.Leave_Type_Name,
    el.Start_leave_date,
    el.End_Leave_date,
    DATEDIFF(el.End_Leave_date, el.Start_leave_date) + 1 AS total_days,
    el.Reason,
    el.Request_date,
    el.Approved_date,
    el.Document_File,
    el.Leave_Status_ID,
    ls.Leave_Type_Name AS status_name
  FROM emp_leave el
  JOIN employee e      ON el.Emp_ID = e.Emp_id
  JOIN leave_type lt   ON el.Leave_Type_ID = lt.Leave_Type_ID
  JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
  WHERE 1=1
";

$params = [];
$types  = "";

/* ค้นหาข้อความ: ชื่อ/รหัสพนักงาน/ประเภท/เหตุผล */
if ($q !== '') {
    $sql .= " AND (e.Emp_Name LIKE ? OR e.Emp_id LIKE ? OR lt.Leave_Type_Name LIKE ? OR el.Reason LIKE ?) ";
    $kw = "%$q%";
    $params[] = $kw; $types .= "s";
    $params[] = $kw; $types .= "s";
    $params[] = $kw; $types .= "s";
    $params[] = $kw; $types .= "s";
}

/* กรองสถานะ */
if ($status !== '') {
    $sql .= " AND el.Leave_Status_ID = ? ";
    $params[] = $status; $types .= "s";
}

/* กรองประเภท */
if ($type !== '') {
    $sql .= " AND el.Leave_Type_ID = ? ";
    $params[] = $type; $types .= "s";
}

/* กรองช่วงวันที่แบบ “ทับซ้อนช่วง” */
if ($from && $to) {
    $sql .= " AND NOT (el.End_Leave_date < ? OR el.Start_leave_date > ?) ";
    $params[] = $from; $types .= "s";
    $params[] = $to;   $types .= "s";
} elseif ($from) {
    $sql .= " AND el.End_Leave_date >= ? ";
    $params[] = $from; $types .= "s";
} elseif ($to) {
    $sql .= " AND el.Start_leave_date <= ? ";
    $params[] = $to;   $types .= "s";
}

$sql .= " ORDER BY el.Start_leave_date DESC, el.Leave_ID DESC ";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

/* dropdown */
$statuses    = $conn->query("SELECT Leave_Status_ID, Leave_Type_Name FROM leave_status");
$leave_types = $conn->query("SELECT Leave_Type_ID, Leave_Type_Name FROM leave_type");

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';
?>
<div class="main-content p-4">
  <h4 class="mb-1">ประวัติการลา (ทั้งหมดในระบบ)</h4>

  <!-- ฟอร์มค้นหา/กรอง (โครงเหมือนฝั่งพนักงาน) -->
  <form method="GET" action="leave_history.php" class="card mb-4" id="filterForm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h5 class="card-title mb-2 mb-sm-0">
          <i class="bi bi-funnel"></i> ค้นหาและกรอง
        </h5>
        <div class="ms-auto">
          <button type="submit" class="btn btn-primary me-2">
            <i class="bi bi-search"></i> ค้นหา
          </button>
          <a href="leave_history.php" class="btn btn-secondary">
            <i class="bi bi-x-circle"></i> ล้างค่า
          </a>
        </div>
      </div>

      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">ค้นหา</label>
          <input type="text" name="q" class="form-control"
                 placeholder="ชื่อ/รหัสพนักงาน, ประเภทการลา หรือเหตุผล..."
                 value="<?= htmlspecialchars($q) ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">สถานะ</label>
          <select name="status" class="form-select">
            <option value="">ทุกสถานะ</option>
            <?php if ($statuses && $statuses->num_rows): while($s=$statuses->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($s['Leave_Status_ID']) ?>"
                <?= ($status !== '' && $status == $s['Leave_Status_ID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['Leave_Type_Name']) ?>
              </option>
            <?php endwhile; endif; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">ประเภทการลา</label>
          <select name="type" class="form-select">
            <option value="">ทุกประเภท</option>
            <?php if ($leave_types && $leave_types->num_rows): while($t=$leave_types->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($t['Leave_Type_ID']) ?>"
                <?= ($type !== '' && $type == $t['Leave_Type_ID']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['Leave_Type_Name']) ?>
              </option>
            <?php endwhile; endif; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">วันที่เริ่ม (From)</label>
          <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">วันที่สิ้นสุด (To)</label>
          <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
        </div>
      </div>
    </div>
  </form>

  <!-- ตารางประวัติทั้งหมด -->
  <div class="card">
    <div class="card-header">
      <i class="bi bi-clock-history"></i>
      ประวัติการลา (<?= (int)$result->num_rows ?> รายการ)
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
          <thead>
            <tr>
              <th>พนักงาน</th>
              <th>ประเภทการลา</th>
              <th>วันที่เริ่มลา - วันที่สิ้นสุด</th>
              <th>จำนวนวัน</th>
              <th>เหตุผล</th>
              <th>ไฟล์แนบ</th>
              <th>สถานะ</th>
              <th>วันที่อนุมัติ</th>
              <th>วันที่ส่งคำขอ</th>
            </tr>
          </thead>
          <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td>
                  <?= htmlspecialchars($row['Emp_Name']) ?>
                  <div class="text-muted small"><?= htmlspecialchars($row['Emp_id']) ?></div>
                </td>
                <td><?= htmlspecialchars($row['Leave_Type_Name']) ?></td>
                <td>
                  <?= date('d/m/Y', strtotime($row['Start_leave_date'])) ?>
                  -
                  <?= date('d/m/Y', strtotime($row['End_Leave_date'])) ?>
                </td>
                <td><?= (int)$row['total_days'] ?></td>
                <td><?= htmlspecialchars($row['Reason'] ?: '-') ?></td>
                <td>
                  <?php if (!empty($row['Document_File'])):
                      $file_path = "../uploads/" . $row['Document_File'];
                      $ext = strtolower(pathinfo($row['Document_File'], PATHINFO_EXTENSION));
                  ?>
                    <?php if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                      <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" data-img="<?= $file_path ?>">
                        <img src="<?= $file_path ?>" alt="แนบไฟล์"
                             style="width:50px;height:auto;border:1px solid #ccc;border-radius:5px">
                      </a>
                    <?php else: ?>
                      <a href="<?= $file_path ?>" target="_blank" class="btn btn-sm btn-info">
                        เปิดไฟล์
                      </a>
                    <?php endif; ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $badge =
                      ($row['Leave_Status_ID']=='1') ? 'success' :
                      (($row['Leave_Status_ID']=='2') ? 'danger' :
                      (($row['Leave_Status_ID']=='3') ? 'warning text-dark' : 'secondary'));
                  ?>
                  <span class="badge bg-<?= $badge ?>">
                    <?= htmlspecialchars($row['status_name']) ?>
                  </span>
                </td>
                <td>
                  <?= ($row['Approved_date'] && $row['Approved_date']!=='0000-00-00')
                        ? date('d/m/Y', strtotime($row['Approved_date'])) : '-' ?>
                </td>
                <td><?= date('d/m/Y', strtotime($row['Request_date'])) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" class="text-center text-muted">ไม่มีข้อมูล</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal สำหรับดูรูปภาพแนบ -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="" id="modalImage" class="img-fluid" alt="ไฟล์แนบ">
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('imageModal').addEventListener('show.bs.modal', function (event) {
  const link = event.relatedTarget;
  const modalImage = document.getElementById('modalImage');
  modalImage.src = link.getAttribute('data-img') || '';
});
</script>

<?php
require_once '../includes/footer.php';
$stmt->close();
$conn->close();

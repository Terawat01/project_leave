<?php
// --- Admin Auth ---
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: ../logout.php");
    exit();
}

require_once '../includes/db.php';

/* ====== Summary Cards ====== */
$total_requests = $conn->query("SELECT COUNT(*) AS c FROM emp_leave")->fetch_assoc()['c'] ?? 0;
$pending_count  = $conn->query("SELECT COUNT(*) AS c FROM emp_leave WHERE Leave_Status_ID='3'")->fetch_assoc()['c'] ?? 0;
$approved_count = $conn->query("SELECT COUNT(*) AS c FROM emp_leave WHERE Leave_Status_ID='1'")->fetch_assoc()['c'] ?? 0;
$employee_count = $conn->query("SELECT COUNT(*) AS c FROM employee")->fetch_assoc()['c'] ?? 0;

/* ====== Monthly Stats (ทั้งปี ปัจจุบัน) ====== */
$stats_year   = date('Y');
$month_labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
$month_data   = array_fill(0, 12, 0);

// นับจำนวนคำขอลา ตามเดือนของ Start_leave_date
$res = $conn->query("
  SELECT MONTH(Start_leave_date) AS m, COUNT(*) AS c
  FROM emp_leave
  WHERE YEAR(Start_leave_date) = '{$conn->real_escape_string($stats_year)}'
  GROUP BY MONTH(Start_leave_date)
");
while ($r = $res->fetch_assoc()) {
    $month_data[(int)$r['m'] - 1] = (int)$r['c'];
}

/* ====== Type Stats (ทั้งระบบ) ====== */
$type_labels = [];
$type_ids    = [];
$type_data   = [];

$res = $conn->query("
  SELECT lt.Leave_Type_ID, lt.Leave_Type_Name, COUNT(el.Leave_ID) AS c
  FROM leave_type lt
  LEFT JOIN emp_leave el ON el.Leave_Type_ID = lt.Leave_Type_ID
  GROUP BY lt.Leave_Type_ID, lt.Leave_Type_Name
");
while ($r = $res->fetch_assoc()) {
    $type_ids[]    = $r['Leave_Type_ID'];
    $type_labels[] = $r['Leave_Type_Name'];
    $type_data[]   = (int)$r['c'];
}

/* ====== Status Stats (ทั้งระบบ) ====== */
$status_labels = [];
$status_ids    = [];
$status_data   = [];

$res = $conn->query("
  SELECT ls.Leave_Status_ID, ls.Leave_Type_Name, COUNT(el.Leave_ID) AS c
  FROM leave_status ls
  LEFT JOIN emp_leave el ON el.Leave_Status_ID = ls.Leave_Status_ID
  GROUP BY ls.Leave_Status_ID, ls.Leave_Type_Name
");
while ($r = $res->fetch_assoc()) {
    $status_ids[]    = $r['Leave_Status_ID'];
    $status_labels[] = $r['Leave_Type_Name'];
    $status_data[]   = (int)$r['c'];
}

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';
?>
<div class="main-content p-4">
  <h4 class="mb-4">แดชบอร์ดผู้ดูแลระบบ</h4>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm h-100 position-relative" role="button"
           onclick="location.href='manage_leave.php'">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">คำขอทั้งหมด</h6>
            <i class="bi bi-calendar2-check fs-3"></i>
          </div>
          <div class="display-6 fw-bold mt-2"><?= (int)$total_requests ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100 position-relative" role="button"
           onclick="location.href='manage_leave.php?status=3'">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">รอดำเนินการ</h6>
            <i class="bi bi-hourglass-split fs-3"></i>
          </div>
          <div class="display-6 fw-bold mt-2"><?= (int)$pending_count ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100 position-relative" role="button"
           onclick="location.href='manage_leave.php?status=1'">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">อนุมัติแล้ว</h6>
            <i class="bi bi-check2-circle fs-3"></i>
          </div>
          <div class="display-6 fw-bold mt-2"><?= (int)$approved_count ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm h-100 position-relative" role="button"
           onclick="location.href='manage_employees.php'">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">พนักงานทั้งหมด</h6>
            <i class="bi bi-people fs-3"></i>
          </div>
          <div class="display-6 fw-bold mt-2"><?= (int)$employee_count ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts -->
  <div class="row g-5">
    <!-- ทำให้กราฟรายเดือนกว้างเต็มบรรทัด -->
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">สถิติรายเดือน (<?= htmlspecialchars($stats_year) ?>)</div>
        <div class="card-body">
          <canvas id="monthlyChart" height="150"></canvas>
          <div class="text-muted small mt-2">คลิกแท่งกราฟของเดือนใดเพื่อไปดูรายการของเดือนนั้นบนหน้าจัดการการลา</div>
        </div>
      </div>
    </div>

    <!-- ประเภทการลา & สถานะ -->
    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header fw-bold">ประเภทการลา</div>
        <div class="card-body">
          <canvas id="typeChart" height="100"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ===== Helper: หา from/to ของเดือน =====
function lastDayOfMonth(year, monthIndex) {
  return new Date(year, monthIndex + 1, 0).getDate();
}
function yyyyMmRange(year, monthIndex) {
  const mm = String(monthIndex + 1).padStart(2, '0');
  const last = String(lastDayOfMonth(year, monthIndex)).padStart(2, '0');
  return { from: `${year}-${mm}-01`, to: `${year}-${mm}-${last}` };
}

// ===== Monthly Chart =====
const monthLabels = <?= json_encode($month_labels) ?>;
const monthData   = <?= json_encode($month_data) ?>;
const statsYear   = <?= json_encode($stats_year) ?>;

const ctxMonth = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctxMonth, {
  type: 'bar',
  data: {
    labels: monthLabels,
    datasets: [{ label: 'จำนวนคำขอลา', data: monthData, backgroundColor: '#0d6efd' }]
  },
  options: {
    responsive: true,
    onClick: (evt, elements) => {
      const points = monthlyChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
      if (!points.length) return;
      const first = points[0];
      const monthIndex = first.index;
      const { from, to } = yyyyMmRange(statsYear, monthIndex);
      const url = new URL('/project_leave/admin/manage_leave.php', location.origin);
      url.searchParams.set('from', from);
      url.searchParams.set('to', to);
      location.href = url.toString();
    },
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// ===== Type Chart (คลิกเพื่อกรองตามประเภท) =====
const typeLabels = <?= json_encode($type_labels, JSON_UNESCAPED_UNICODE) ?>;
const typeIds    = <?= json_encode($type_ids) ?>;
const typeData   = <?= json_encode($type_data) ?>;

const ctxType = document.getElementById('typeChart').getContext('2d');
const typeChart = new Chart(ctxType, {
  type: 'pie',
  data: {
    labels: typeLabels,
    datasets: [{ data: typeData, backgroundColor: ['#0d6efd','#198754','#dc3545','#ffc107','#6f42c1','#20c997'] }]
  },
  options: {
    responsive: true,
    onClick: (evt, elements) => {
      if (!elements.length) return;
      const idx = elements[0].index;
      const leaveTypeId = typeIds[idx];
      const url = new URL('/project_leave/admin/manage_leave.php', location.origin);
      url.searchParams.set('type', leaveTypeId);
      location.href = url.toString();
    },
    plugins: { legend: { position: 'bottom' } }
  }
});

</script>

<?php
require_once '../includes/footer.php';
$conn->close();

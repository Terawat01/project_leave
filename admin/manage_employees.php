<?php
// admin/manage_employees.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

// ===== Handle Delete (ก่อนมี output) =====
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $emp_id_to_delete = $_GET['id'];
    if ($emp_id_to_delete !== 'em005') {
        $stmt = $conn->prepare("DELETE FROM employee WHERE Emp_id = ?");
        $stmt->bind_param("s", $emp_id_to_delete);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_employees.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

// ===== ดึงข้อมูลพนักงาน (join prefix/position) =====
$sql = "
SELECT e.*,
       p.Position_Name,
       pr.Prefix_Name
FROM employee e
LEFT JOIN position p ON e.Position_ID = p.Position_ID
LEFT JOIN prefix   pr ON e.Prefix_ID   = pr.Prefix_ID
ORDER BY e.Emp_id ASC
";
$result = $conn->query($sql);
?>
<style>
  .table-wrap { max-height: 600px; overflow: auto; }
  .table thead th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
  .emp-photo { width: 90px; height: 90px; object-fit: cover; border: 1px solid #ddd; border-radius: 6px; }
  .detail-row-label { width: 160px; color:#6c757d; }
</style>

<div class="main-content p-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>จัดการข้อมูลพนักงาน</h4>
      <a href="add_employee.php" class="btn btn-primary">+ เพิ่มพนักงาน</a>
  </div>

  <div class="card">
    <div class="card-body table-wrap">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Emp_id</th>
            <th>ชื่อ-นามสกุล</th>
            <th>ตำแหน่ง</th>
            <th>Email</th>
            <th class="text-end">ดำเนินการ</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
              // เตรียมพาธรูป
              $pic_dir = "../pic_emp/";
              $pic = (!empty($row['Emp_pic']) && file_exists($pic_dir.$row['Emp_pic'])) ? ($pic_dir.$row['Emp_pic']) : "";
            ?>
              <tr
                data-emp='<?= json_encode([
                  "Emp_id"          => $row["Emp_id"],
                  "Prefix_Name"     => $row["Prefix_Name"] ?? "",
                  "Prefix_ID"       => $row["Prefix_ID"] ?? "",
                  "Emp_Name"        => $row["Emp_Name"] ?? "",
                  "Emp_LastName"    => $row["Emp_LastName"] ?? "",
                  "Position_Name"   => $row["Position_Name"] ?? "",
                  "Position_ID"     => $row["Position_ID"] ?? "",
                  "Email"           => $row["Email"] ?? "",
                  "Address"         => $row["Address"] ?? "",
                  "Created_at"      => $row["Created_at"] ?? "",
                  "Gender"          => $row["Gender"] ?? "",
                  "Birthdate"       => $row["Birthdate"] ?? "",
                  "ID_Card_Number"  => $row["ID_Card_Number"] ?? "",
                  "Emp_pic"         => $row["Emp_pic"] ?? "",
                  "Emp_pic_url"     => $pic
                ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>'
              >
                <td><?= htmlspecialchars($row['Emp_id']) ?></td>
                <td><?= htmlspecialchars(($row['Prefix_Name'] ? $row['Prefix_Name'] : '').' '.$row['Emp_Name'].' '.$row['Emp_LastName']) ?></td>
                <td><?= htmlspecialchars($row['Position_Name'] ?? $row['Position_ID']) ?></td>
                <td><?= htmlspecialchars($row['Email']) ?></td>
                <td class="text-end">
                  <button type="button" class="btn btn-sm btn-outline-secondary btn-detail" data-bs-toggle="modal" data-bs-target="#detailModal">
                    รายละเอียด
                  </button>
                  <a href="edit_employee.php?id=<?= urlencode($row['Emp_id']) ?>" class="btn btn-sm btn-outline-primary">แก้ไข</a>
                  <a href="?action=delete&id=<?= urlencode($row['Emp_id']) ?>" class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบพนักงานคนนี้?')">ลบ</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูลพนักงาน</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ===== Modal รายละเอียด ===== -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel">รายละเอียดพนักงาน</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-3 text-center">
            <img id="dEmpPic" src="" alt="รูปพนักงาน" class="emp-photo mb-2" onerror="this.style.display='none'">
            <div id="dEmpPicNone" class="text-muted small" style="display:none;">ไม่มีรูป</div>
          </div>
          <div class="col-md-9">
            <table class="table table-borderless mb-0">
              <tr><td class="detail-row-label">Emp_id</td><td id="dEmpId"></td></tr>
              <tr><td class="detail-row-label">คำนำหน้า</td><td id="dPrefix"></td></tr>
              <tr><td class="detail-row-label">ชื่อ-นามสกุล</td><td id="dFullName"></td></tr>
              <tr><td class="detail-row-label">ตำแหน่ง</td><td id="dPosition"></td></tr>
              <tr><td class="detail-row-label">Email</td><td id="dEmail"></td></tr>
              <tr><td class="detail-row-label">ที่อยู่</td><td id="dAddress"></td></tr>
              <tr><td class="detail-row-label">เริ่มงาน (Created_at)</td><td id="dCreated"></td></tr>
              <tr><td class="detail-row-label">เพศ</td><td id="dGender"></td></tr>
              <tr><td class="detail-row-label">วันเกิด</td><td id="dBirth"></td></tr>
              <tr><td class="detail-row-label">เลขบัตรประชาชน</td><td id="dIdCard"></td></tr>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a id="dEditLink" href="#" class="btn btn-primary">แก้ไขข้อมูลนี้</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>

<script>
// เติมข้อมูลลง Modal เมื่อกดปุ่ม "รายละเอียด"
document.addEventListener('DOMContentLoaded', function(){
  const detailButtons = document.querySelectorAll('.btn-detail');
  detailButtons.forEach(btn => {
    btn.addEventListener('click', function(){
      const tr = this.closest('tr');
      const data = JSON.parse(tr.getAttribute('data-emp'));

      // ข้อมูลพื้นฐาน
      document.getElementById('dEmpId').textContent    = data.Emp_id || '-';
      document.getElementById('dPrefix').textContent    = data.Prefix_Name || data.Prefix_ID || '-';
      const fullName = ((data.Prefix_Name ? data.Prefix_Name + ' ' : '') + (data.Emp_Name || '') + ' ' + (data.Emp_LastName || '')).trim();
      document.getElementById('dFullName').textContent  = fullName || '-';
      document.getElementById('dPosition').textContent  = data.Position_Name || data.Position_ID || '-';
      document.getElementById('dEmail').textContent     = data.Email || '-';
      document.getElementById('dAddress').textContent   = data.Address || '-';
      document.getElementById('dCreated').textContent   = data.Created_at || '-';
      document.getElementById('dGender').textContent    = data.Gender || '-';
      document.getElementById('dBirth').textContent     = data.Birthdate || '-';
      document.getElementById('dIdCard').textContent    = data.ID_Card_Number || '-';

      // รูปภาพ
      const img = document.getElementById('dEmpPic');
      const noImg = document.getElementById('dEmpPicNone');
      if (data.Emp_pic_url) {
        img.src = data.Emp_pic_url;
        img.style.display = 'inline-block';
        noImg.style.display = 'none';
      } else {
        img.style.display = 'none';
        noImg.style.display = 'block';
      }

      // ลิงก์แก้ไข
      const editLink = document.getElementById('dEditLink');
      editLink.href = 'edit_employee.php?id=' + encodeURIComponent(data.Emp_id);
    });
  });
});
</script>

<?php
require_once '../includes/footer.php';
$conn->close();

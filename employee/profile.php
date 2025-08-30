<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

$emp_id = $_SESSION['user_id'];

// Fetch employee data
$stmt = $conn->prepare("
    SELECT e.*, p.Position_Name, pr.Prefix_Name 
    FROM employee e
    LEFT JOIN position p ON e.Position_ID = p.Position_ID
    LEFT JOIN prefix pr ON e.Prefix_ID = pr.Prefix_ID
    WHERE e.Emp_id = ?
");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "<div class='main-content p-4'><div class='alert alert-danger'>ไม่พบข้อมูลพนักงาน</div></div>";
    exit();
}

// ตรวจสอบรูปพนักงาน
$pic_folder = "../pic_emp/";
$emp_img_path = (!empty($employee['Emp_pic']) && file_exists($pic_folder . $employee['Emp_pic'])) 
    ? $pic_folder . $employee['Emp_pic'] 
    : "../assets/images/avatar_placeholder.png"; // fallback placeholder
?>
<div class="main-content p-4">
    <h4 class="mb-1">ข้อมูลส่วนตัว</h4>
    <p class="text-muted">ดูข้อมูลส่วนตัวของคุณ</p>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-person-badge-fill me-2"></i> ข้อมูลพนักงาน
        </div>
        <div class="card-body">
            <div class="row">
                <!-- รูปพนักงาน -->
                <div class="col-md-4 text-center mb-3">
                    <img src="<?php echo $emp_img_path; ?>" alt="รูปพนักงาน" 
                         class="img-fluid rounded" 
                         style="max-width:200px; border:1px solid #ccc; padding:5px;">
                </div>

                <!-- ข้อมูลพนักงาน -->
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">รหัสพนักงาน</label>
                            <p class="fw-bold"><?php echo htmlspecialchars($employee['Emp_id']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ชื่อ</label>
                            <p><?php echo htmlspecialchars($employee['Prefix_Name'] . $employee['Emp_Name']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">นามสกุล</label>
                            <p><?php echo htmlspecialchars($employee['Emp_LastName'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email</label>
                            <p><?php echo htmlspecialchars($employee['Email'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ตำแหน่ง</label>
                            <p><?php echo htmlspecialchars($employee['Position_Name'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">เป็นพนักงานเมื่อ</label>
                            <p><?php echo htmlspecialchars($employee['Created_at'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">รหัสบัตรประชาชน</label>
                            <p><?php echo htmlspecialchars($employee['ID_Card_Number'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">ที่อยู่</label>
                            <p><?php echo htmlspecialchars($employee['Address'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">วัน/เดือน/ปีเกิด</label>
                            <p><?php echo htmlspecialchars($employee['Birthdate'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">เพศ</label>
                            <p><?php echo htmlspecialchars($employee['Gender'] ?: 'ไม่มีข้อมูล'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
$conn->close();
?>

<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_SESSION['user_id'];
    $leave_type_id = $_POST['leave_type'];
    $leave_time_id = $_POST['leave_time'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];
    $document_filename = '';

    // --- File Upload Logic ---
if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
    $allowed_types = [
        'image/jpeg', 'image/png',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (in_array($_FILES['document']['type'], $allowed_types) && $_FILES['document']['size'] <= $max_size) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // ฟังก์ชันสร้างชื่อไฟล์สุ่ม 8 ตัวอักษร (A-Z, a-z, 0-9)
        function randomString($length = 8) {
            return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
        }

        $file_extension = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
        $document_filename = randomString(8) . '.' . $file_extension;

        if (!move_uploaded_file($_FILES['document']['tmp_name'], $upload_dir . $document_filename)) {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
        }
    } else {
        $error = "ไฟล์ไม่ถูกต้องหรือมีขนาดใหญ่เกิน 5MB";
    }
} else {
    $document_filename = ''; // ถ้าไม่แนบไฟล์
}
    // -------------------------

    if (empty($error)) {
        if (empty($leave_type_id) || empty($start_date) || empty($end_date) || empty($leave_time_id)) {
            $error = "กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน";
        } elseif (strtotime($end_date) < strtotime($start_date)) {
            $error = "วันที่สิ้นสุดต้องไม่น้อยกว่าวันที่เริ่มต้น";
        } else {
            $leave_id = "L" . time();
            $request_date = date('Y-m-d');
            $status_id = '3'; // Pending

            $stmt = $conn->prepare("INSERT INTO emp_leave (Leave_ID, Leave_Type_ID, Leave_Time_ID, Emp_ID, Leave_Status_ID, Reason, Start_leave_date, End_Leave_date, Request_date, Document_File) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", $leave_id, $leave_type_id, $leave_time_id, $emp_id, $status_id, $reason, $start_date, $end_date, $request_date, $document_filename);

            if ($stmt->execute()) {
                $message = "ส่งคำขอลางานเรียบร้อยแล้ว";
            } else {
                $error = "เกิดข้อผิดพลาดในการส่งคำขอ: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Fetch master data for dropdowns
$leave_types_result = $conn->query("SELECT Leave_Type_ID, Leave_Type_Name FROM leave_type");
$leave_times_result = $conn->query("SELECT Leave_time_ID, Leave_Type_ID as time_range FROM leave_time_type");

?>
<div class="main-content p-4">
    <h4 class="mb-1">ขอลางาน</h4>
    <p class="text-muted">กรอกข้อมูลการขอลางานของคุณ</p>

    <?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-file-earmark-text-fill"></i> แบบฟอร์มขอลางาน
        </div>
        <div class="card-body">
            <form method="POST" action="leave_request.php" class="mt-3" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="leave_type" class="form-label">ประเภทการลา <span class="text-danger">*</span></label>
                        <select class="form-select" id="leave_type" name="leave_type" required>
                            <option value="">-- เลือกประเภทการลา --</option>
                            <?php while($row = $leave_types_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['Leave_Type_ID']; ?>"><?php echo htmlspecialchars($row['Leave_Type_Name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                     <div class="col-md-6 mb-3">
                        <label for="requester" class="form-label">ผู้ขอลา</label>
                        <input type="text" class="form-control" id="requester" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">วันที่เริ่มต้น <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">วันที่สิ้นสุด <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="leave_time" class="form-label">ระยะเวลา <span class="text-danger">*</span></label>
                        <select class="form-select" id="leave_time" name="leave_time" required>
                             <option value="">-- เลือกระยะเวลา --</option>
                             <?php while($row = $leave_times_result->fetch_assoc()): ?>
                            <option value="<?php echo $row['Leave_time_ID']; ?>"><?php echo htmlspecialchars($row['time_range']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reason" class="form-label">เหตุผลการลา</label>
                    <textarea class="form-control" id="reason" name="reason" rows="3" placeholder="ระบุเหตุผลการลา (ไม่บังคับ)"></textarea>
                </div>
                 <div class="mb-3">
                    <label for="notify_confirm" class="form-label">ยืนยันคำขอแจ้งเตือน</label>
                    <input type="text" class="form-control" id="notify_confirm" name="notify_confirm">
                </div>
                <div class="mb-3">
                    <label for="document" class="form-label">แนบไฟล์เอกสาร</label>
                    <input class="form-control" type="file" id="document" name="document">
                    <div class="form-text">รองรับไฟล์ รูปภาพ, PDF, Word (ขนาดไม่เกิน 5MB)</div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">ส่งคำขอลา</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php';
?>
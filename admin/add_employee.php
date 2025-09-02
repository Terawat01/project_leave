<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';

$message = '';
$error = '';

function randomString($length = 8){
  return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emp_id       = trim($_POST['emp_id']);
    $prefix_id    = $_POST['prefix_id'];
    $position_id  = $_POST['position_id'];
    $emp_name     = trim($_POST['emp_name']);
    $emp_lastname = trim($_POST['emp_lastname']);
    $email        = trim($_POST['email']);
    $password     = $_POST['password'];
    $address      = trim($_POST['address'] ?? '');
    $gender       = trim($_POST['gender'] ?? '');
    $birthdate    = $_POST['birthdate'] ?? '';
    $id_card      = trim($_POST['id_card_number'] ?? '');
    $created_at   = $_POST['created_at'] ?: date('Y-m-d');

    // validate เบื้องต้น
    if ($emp_id === '' || $emp_name === '' || $emp_lastname === '') {
        $error = "กรุณากรอก รหัสพนักงาน / ชื่อ / นามสกุล";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "อีเมลไม่ถูกต้อง";
    } elseif (strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    }

    // ตรวจซ้ำ emp_id / email
    if(!$error){
        $chk = $conn->prepare("SELECT 1 FROM employee WHERE Emp_id=? OR Email=? LIMIT 1");
        $chk->bind_param("ss", $emp_id, $email);
        $chk->execute();
        $dup = $chk->get_result()->fetch_assoc();
        $chk->close();
        if($dup){
            $error = "Emp_id หรือ Email นี้ถูกใช้งานแล้ว";
        }
    }

    // อัปโหลดรูป
    $emp_pic = '';
    if(!$error && isset($_FILES['emp_pic']) && $_FILES['emp_pic']['error'] !== UPLOAD_ERR_NO_FILE){
        if($_FILES['emp_pic']['error'] === UPLOAD_ERR_OK){
            $ext = strtolower(pathinfo($_FILES['emp_pic']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif'];
            $max_size = 3 * 1024 * 1024;
            if(!in_array($ext, $allowed)){
                $error = "ไฟล์รูปต้องเป็น jpg, jpeg, png หรือ gif";
            } elseif($_FILES['emp_pic']['size'] > $max_size){
                $error = "ขนาดรูปต้องไม่เกิน 3MB";
            } else {
                $emp_pic = randomString(8).".".$ext;
                $upload_dir = "../pic_emp/";
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                if(!move_uploaded_file($_FILES['emp_pic']['tmp_name'], $upload_dir.$emp_pic)){
                    $error = "อัปโหลดรูปไม่สำเร็จ";
                }
            }
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ (code: ".$_FILES['emp_pic']['error'].")";
        }
    }

    if(!$error){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO employee
          (Emp_id, Prefix_ID, Position_ID, Emp_Name, Emp_LastName, Email, Address, Created_at, Gender, Birthdate, ID_Card_Number, Password, Emp_pic)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", $emp_id, $prefix_id, $position_id, $emp_name, $emp_lastname, $email, $address, $created_at, $gender, $birthdate, $id_card, $hashed_password, $emp_pic);

        if($stmt->execute()){
            $message = "เพิ่มพนักงานใหม่เรียบร้อยแล้ว";
        } else {
            $error = "เกิดข้อผิดพลาด: ".$stmt->error;
        }
        $stmt->close();
    }
}

// Fetch positions and prefixes
$positions = $conn->query("SELECT * FROM position");
$prefixes  = $conn->query("SELECT * FROM prefix");

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';
?>
<div class="main-content p-4">
  <h4 class="mb-4">เพิ่มพนักงานใหม่</h4>
  <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error)   ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="add_employee.php" enctype="multipart/form-data">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">รหัสพนักงาน</label>
            <input type="text" name="emp_id" class="form-control" required>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">คำนำหน้า</label>
            <select name="prefix_id" class="form-select" required>
              <?php while($px = $prefixes->fetch_assoc()) echo "<option value='{$px['Prefix_ID']}'>{$px['Prefix_Name']}</option>"; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">ตำแหน่ง</label>
            <select name="position_id" class="form-select" required>
              <?php while($p = $positions->fetch_assoc()) echo "<option value='{$p['Position_ID']}'>{$p['Position_Name']}</option>"; ?>
            </select>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">ชื่อ</label>
            <input type="text" name="emp_name" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">นามสกุล</label>
            <input type="text" name="emp_lastname" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">อีเมล</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">รหัสผ่าน</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">เพศ</label>
            <select name="gender" class="form-select">
              <option value="">-</option>
              <option value="ชาย">ชาย</option>
              <option value="หญิง">หญิง</option>
              <option value="อื่น ๆ">อื่น ๆ</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">วัน/เดือน/ปีเกิด</label>
            <input type="date" name="birthdate" class="form-control">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">เลขบัตรประชาชน</label>
            <input type="text" name="id_card_number" class="form-control" maxlength="13" pattern="\d{0,13}" placeholder="กรอกเป็นตัวเลข 13 หลัก">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">วันที่เริ่มงาน (Created_at)</label>
            <input type="date" name="created_at" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>

          <div class="col-12 mb-3">
            <label class="form-label">ที่อยู่</label>
            <input type="text" name="address" class="form-control">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">รูปพนักงาน</label>
            <input type="file" name="emp_pic" class="form-control" accept="image/*">
            <small class="text-muted">รองรับ jpg, jpeg, png, gif (ไม่เกิน 3MB)</small>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">บันทึก</button>
        <a href="manage_employees.php" class="btn btn-secondary">กลับ</a>
      </form>
    </div>
  </div>
</div>
<?php require_once '../includes/footer.php'; ?>

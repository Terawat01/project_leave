<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: login.php");
    exit();
}
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

$message = '';
$error = '';
$emp_id_to_edit = $_GET['id'] ?? null;

if (!$emp_id_to_edit) {
    header("Location: manage_employees.php");
    exit();
}

// employee
$stmt = $conn->prepare("SELECT * FROM employee WHERE Emp_id = ?");
$stmt->bind_param("s", $emp_id_to_edit);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$employee) {
    echo "<div class='main-content p-4'><div class='alert alert-danger'>ไม่พบข้อมูลพนักงาน</div></div>";
    exit();
}

// dropdown data
$positions = $conn->query("SELECT * FROM position");
$prefixes  = $conn->query("SELECT * FROM prefix");

// handle submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id       = $_POST['emp_id'];
    $prefix_id    = $_POST['prefix_id'];
    $position_id  = $_POST['position_id'];
    $emp_name     = trim($_POST['emp_name']);
    $emp_lastname = trim($_POST['emp_lastname']);
    $email        = trim($_POST['email']);
    $address      = trim($_POST['address'] ?? '');
    $gender       = trim($_POST['gender'] ?? '');
    $birthdate    = $_POST['birthdate'] ?? '';
    $id_card      = trim($_POST['id_card_number'] ?? '');
    $created_at   = $_POST['created_at'] ?: $employee['Created_at'];
    $new_password = $_POST['password'] ?? '';

    if ($emp_name === '' || $emp_lastname === '') {
        $error = "กรุณากรอกชื่อและนามสกุล";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "อีเมลไม่ถูกต้อง";
    }

    // upload image
    $emp_pic = $employee['Emp_pic'];
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
                $new_filename = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8) . '.' . $ext;
                $upload_dir = "../pic_emp/";
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                if(move_uploaded_file($_FILES['emp_pic']['tmp_name'], $upload_dir.$new_filename)){
                    if(!empty($employee['Emp_pic']) && file_exists($upload_dir.$employee['Emp_pic'])){
                        @unlink($upload_dir.$employee['Emp_pic']);
                    }
                    $emp_pic = $new_filename;
                } else {
                    $error = "อัปโหลดรูปไม่สำเร็จ";
                }
            }
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ (code: ".$_FILES['emp_pic']['error'].")";
        }
    }

    if(!$error){
        if(!empty($new_password)){
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE employee
                SET Prefix_ID=?, Position_ID=?, Emp_Name=?, Emp_LastName=?, Email=?, Address=?, Created_at=?, Gender=?, Birthdate=?, ID_Card_Number=?, Password=?, Emp_pic=?
                WHERE Emp_id=?");
            $stmt->bind_param("sssssssssssss",
                $prefix_id, $position_id, $emp_name, $emp_lastname, $email, $address, $created_at, $gender, $birthdate, $id_card, $hashed, $emp_pic, $emp_id);
        } else {
            $stmt = $conn->prepare("UPDATE employee
                SET Prefix_ID=?, Position_ID=?, Emp_Name=?, Emp_LastName=?, Email=?, Address=?, Created_at=?, Gender=?, Birthdate=?, ID_Card_Number=?, Emp_pic=?
                WHERE Emp_id=?");
            $stmt->bind_param("ssssssssssss",
                $prefix_id, $position_id, $emp_name, $emp_lastname, $email, $address, $created_at, $gender, $birthdate, $id_card, $emp_pic, $emp_id);
        }

        if($stmt->execute()){
            $message = "อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว";
            // refresh
            $employee = array_merge($employee, [
              'Prefix_ID'       => $prefix_id,
              'Position_ID'     => $position_id,
              'Emp_Name'        => $emp_name,
              'Emp_LastName'    => $emp_lastname,
              'Email'           => $email,
              'Address'         => $address,
              'Created_at'      => $created_at,
              'Gender'          => $gender,
              'Birthdate'       => $birthdate,
              'ID_Card_Number'  => $id_card,
              'Emp_pic'         => $emp_pic
            ]);
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดต: ".$stmt->error;
        }
        $stmt->close();
    }
}
?>
<div class="main-content p-4">
  <h4 class="mb-4">แก้ไขข้อมูลพนักงาน (<?= htmlspecialchars($employee['Emp_id']) ?>)</h4>
  <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="alert alert-danger"><?= htmlspecialchars($error)   ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" action="edit_employee.php?id=<?= htmlspecialchars($employee['Emp_id']) ?>" enctype="multipart/form-data">
        <input type="hidden" name="emp_id" value="<?= htmlspecialchars($employee['Emp_id']) ?>">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label class="form-label">คำนำหน้า</label>
            <select name="prefix_id" class="form-select" required>
              <?php while($px = $prefixes->fetch_assoc()): ?>
                <option value="<?= $px['Prefix_ID'] ?>" <?= ($px['Prefix_ID'] == $employee['Prefix_ID']) ? 'selected':''; ?>>
                  <?= htmlspecialchars($px['Prefix_Name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">ตำแหน่ง</label>
            <select name="position_id" class="form-select" required>
              <?php while($p = $positions->fetch_assoc()): ?>
                <option value="<?= $p['Position_ID'] ?>" <?= ($p['Position_ID'] == $employee['Position_ID']) ? 'selected':''; ?>>
                  <?= htmlspecialchars($p['Position_Name']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label class="form-label">วันที่เริ่มงาน (Created_at)</label>
            <input type="date" name="created_at" class="form-control" value="<?= htmlspecialchars($employee['Created_at']) ?>">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">ชื่อ</label>
            <input type="text" name="emp_name" class="form-control" value="<?= htmlspecialchars($employee['Emp_Name']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">นามสกุล</label>
            <input type="text" name="emp_lastname" class="form-control" value="<?= htmlspecialchars($employee['Emp_LastName']) ?>" required>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">อีเมล</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($employee['Email']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">ตั้งรหัสผ่านใหม่</label>
            <input type="password" name="password" class="form-control" placeholder="ปล่อยว่างถ้าไม่เปลี่ยน">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">เพศ</label>
            <select name="gender" class="form-select">
              <?php
                $g = $employee['Gender'];
              ?>
              <option value=""        <?= $g==''?'selected':''; ?>>-</option>
              <option value="ชาย"     <?= $g=='ชาย'?'selected':''; ?>>ชาย</option>
              <option value="หญิง"    <?= $g=='หญิง'?'selected':''; ?>>หญิง</option>
              <option value="อื่น ๆ"  <?= $g=='อื่น ๆ'?'selected':''; ?>>อื่น ๆ</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">วัน/เดือน/ปีเกิด</label>
            <input type="date" name="birthdate" class="form-control" value="<?= htmlspecialchars($employee['Birthdate']) ?>">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">เลขบัตรประชาชน</label>
            <input type="text" name="id_card_number" class="form-control" maxlength="13" pattern="\d{0,13}" value="<?= htmlspecialchars($employee['ID_Card_Number']) ?>">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label">ที่อยู่</label>
            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($employee['Address']) ?>">
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">รูปภาพพนักงาน</label><br>
            <?php
              $pic_dir = "../pic_emp/";
              if(!empty($employee['Emp_pic']) && file_exists($pic_dir.$employee['Emp_pic'])):
            ?>
              <img src="<?= $pic_dir.htmlspecialchars($employee['Emp_pic']) ?>" alt="รูปพนักงาน"
                   style="width:100px;height:auto;border:1px solid #ccc;margin-bottom:5px;"><br>
            <?php endif; ?>
            <input type="file" name="emp_pic" class="form-control" accept="image/*">
            <small class="text-muted">อัปโหลดเพื่อเปลี่ยนรูป (jpg, jpeg, png, gif ≤ 3MB)</small>
          </div>
        </div>

        <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
        <a href="manage_employees.php" class="btn btn-secondary">ยกเลิก</a>
      </form>
    </div>
  </div>
</div>
<?php
require_once '../includes/footer.php';
$conn->close();
?>

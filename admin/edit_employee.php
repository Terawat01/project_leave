<?php
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

// Fetch current employee data
$stmt = $conn->prepare("SELECT * FROM employee WHERE Emp_id = ?");
$stmt->bind_param("s", $emp_id_to_edit);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo "<div class='main-content p-4'><div class='alert alert-danger'>ไม่พบข้อมูลพนักงาน</div></div>";
    exit();
}

// Fetch all positions for the dropdown
$positions = $conn->query("SELECT * FROM position");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $position_id = $_POST['position_id'];
    $emp_name = $_POST['emp_name'];
    $emp_lastname = $_POST['emp_lastname'];
    $email = $_POST['email'];
    $new_password = $_POST['password'];

// Handle new image upload
$emp_pic = $employee['Emp_pic']; // keep old if no new upload
if(isset($_FILES['emp_pic']) && $_FILES['emp_pic']['error'] == 0){
    $ext = strtolower(pathinfo($_FILES['emp_pic']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif'];
    if(in_array($ext, $allowed)){
        // สุ่มชื่อไฟล์ 8 ตัวอักษร + นามสกุลไฟล์
        $new_filename = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8) . '.' . $ext;
        $upload_dir = "../pic_emp/"; // เปลี่ยนโฟลเดอร์เป็น pic_emp
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        move_uploaded_file($_FILES['emp_pic']['tmp_name'], $upload_dir . $new_filename);

        // ลบไฟล์เก่าถ้ามี
        if(!empty($employee['Emp_pic']) && file_exists($upload_dir.$employee['Emp_pic'])){
            unlink($upload_dir.$employee['Emp_pic']);
        }

        $emp_pic = $new_filename;
    } else {
        $error = "ไฟล์รูปภาพต้องเป็นนามสกุล jpg, jpeg, png หรือ gif";
    }
}

    if(!$error){
        if(!empty($new_password)){
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE employee SET Position_ID=?, Emp_Name=?, Emp_LastName=?, Email=?, Password=?, Emp_pic=? WHERE Emp_id=?");
            $stmt->bind_param("sssssss", $position_id, $emp_name, $emp_lastname, $email, $hashed_password, $emp_pic, $emp_id);
        } else {
            $stmt = $conn->prepare("UPDATE employee SET Position_ID=?, Emp_Name=?, Emp_LastName=?, Email=?, Emp_pic=? WHERE Emp_id=?");
            $stmt->bind_param("ssssss", $position_id, $emp_name, $emp_lastname, $email, $emp_pic, $emp_id);
        }

        if($stmt->execute()){
            $message = "อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว";
            // refresh employee data
            $employee['Emp_Name'] = $emp_name;
            $employee['Emp_LastName'] = $emp_lastname;
            $employee['Email'] = $email;
            $employee['Position_ID'] = $position_id;
            $employee['Emp_pic'] = $emp_pic;
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปเดต: ".$stmt->error;
        }
        $stmt->close();
    }
}
?>

<div class="main-content p-4">
    <h4 class="mb-4">แก้ไขข้อมูลพนักงาน (<?php echo htmlspecialchars($employee['Emp_id']); ?>)</h4>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="edit_employee.php?id=<?php echo htmlspecialchars($employee['Emp_id']); ?>" enctype="multipart/form-data">
                <input type="hidden" name="emp_id" value="<?php echo htmlspecialchars($employee['Emp_id']); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ชื่อ</label>
                        <input type="text" name="emp_name" class="form-control" value="<?php echo htmlspecialchars($employee['Emp_Name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">นามสกุล</label>
                        <input type="text" name="emp_lastname" class="form-control" value="<?php echo htmlspecialchars($employee['Emp_LastName']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">อีเมล</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($employee['Email']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ตำแหน่ง</label>
                        <select name="position_id" class="form-select" required>
                            <?php while($p = $positions->fetch_assoc()): ?>
                                <option value="<?php echo $p['Position_ID']; ?>" <?php echo ($p['Position_ID'] == $employee['Position_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['Position_Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ตั้งรหัสผ่านใหม่</label>
                        <input type="password" name="password" class="form-control" placeholder="ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">รูปภาพพนักงาน</label><br>
                        <?php if(!empty($employee['Emp_pic']) && file_exists("../uploads/".$employee['Emp_pic'])): ?>
                            <img src="../uploads/<?php echo $employee['Emp_pic']; ?>" alt="รูปพนักงาน" style="width:100px; height:auto; border:1px solid #ccc; margin-bottom:5px;"><br>
                        <?php endif; ?>
                        <input type="file" name="emp_pic" class="form-control" accept="image/*">
                        <small class="text-muted">อัปโหลดเพื่อเปลี่ยนรูปภาพ</small>
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

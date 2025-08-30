<?php
require_once '../includes/db.php';

$message = '';
$error = '';

function randomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $str = '';
    for ($i = 0; $i < $length; $i++) {
        $str .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $str;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $prefix_id = $_POST['prefix_id'];
    $position_id = $_POST['position_id'];
    $emp_name = $_POST['emp_name'];
    $emp_lastname = $_POST['emp_lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $created_at = date('Y-m-d');

    // Handle file upload
    $emp_pic = '';
    if (isset($_FILES['emp_pic']) && $_FILES['emp_pic']['error'] == 0) {
        $ext = pathinfo($_FILES['emp_pic']['name'], PATHINFO_EXTENSION);
        $emp_pic = randomString() . "." . $ext;
        $upload_dir = "../pic_emp/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        move_uploaded_file($_FILES['emp_pic']['tmp_name'], $upload_dir . $emp_pic);
    }

    $stmt = $conn->prepare("INSERT INTO employee (Emp_id, Prefix_ID, Position_ID, Emp_Name, Emp_LastName, Email, Created_at, Password, Emp_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $emp_id, $prefix_id, $position_id, $emp_name, $emp_lastname, $email, $created_at, $hashed_password, $emp_pic);

    if ($stmt->execute()) {
        $message = "เพิ่มพนักงานใหม่เรียบร้อยแล้ว";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch positions and prefixes
$positions = $conn->query("SELECT * FROM position");
$prefixes = $conn->query("SELECT * FROM prefix");

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="main-content p-4">
    <h4 class="mb-4">เพิ่มพนักงานใหม่</h4>
    <?php if ($message) echo "<div class='alert alert-success'>{$message}</div>"; ?>
    <?php if ($error) echo "<div class='alert alert-danger'>{$error}</div>"; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="add_employee.php" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">รหัสพนักงาน</label>
                        <input type="text" name="emp_id" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ตำแหน่ง</label>
                        <select name="position_id" class="form-select" required>
                            <?php while($p = $positions->fetch_assoc()) echo "<option value='{$p['Position_ID']}'>{$p['Position_Name']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">คำนำหน้า</label>
                        <select name="prefix_id" class="form-select" required>
                            <?php while($px = $prefixes->fetch_assoc()) echo "<option value='{$px['Prefix_ID']}'>{$px['Prefix_Name']}</option>"; ?>
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
                        <label class="form-label">รูปพนักงาน</label>
                        <input type="file" name="emp_pic" class="form-control" accept="image/*">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="manage_employees.php" class="btn btn-secondary">กลับ</a>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>

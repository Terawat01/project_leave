<?php
require_once '../includes/db.php';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $prefix_id = $_POST['prefix_id'];
    $position_id = $_POST['position_id'];
    $emp_name = $_POST['emp_name'];
    $emp_lastname = $_POST['emp_lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $created_at = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO employee (Emp_id, Prefix_ID, Position_ID, Emp_Name, Emp_LastName, Email, Created_at, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $emp_id, $prefix_id, $position_id, $emp_name, $emp_lastname, $email, $created_at, $hashed_password);

    if ($stmt->execute()) {
        $message = "เพิ่มพนักงานใหม่เรียบร้อยแล้ว";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch positions and prefixes for dropdowns
$positions = $conn->query("SELECT * FROM position");
$prefixes = $conn->query("SELECT * FROM prefix");

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';
?>
<div class="main-content p-4">
    <h4 class="mb-4">เพิ่มพนักงานใหม่</h4>
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="add_employee.php">
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">รหัสพนักงาน</label><input type="text" name="emp_id" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">ตำแหน่ง</label>
                        <select name="position_id" class="form-select" required>
                            <?php while($p = $positions->fetch_assoc()) echo "<option value='{$p['Position_ID']}'>{$p['Position_Name']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label">คำนำหน้า</label>
                         <select name="prefix_id" class="form-select" required>
                            <?php while($px = $prefixes->fetch_assoc()) echo "<option value='{$px['Prefix_ID']}'>{$px['Prefix_Name']}</option>"; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3"><label class="form-label">ชื่อ</label><input type="text" name="emp_name" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">นามสกุล</label><input type="text" name="emp_lastname" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">อีเมล</label><input type="email" name="email" class="form-control" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label">รหัสผ่าน</label><input type="password" name="password" class="form-control" required></div>
                </div>
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="manage_employees.php" class="btn btn-secondary">กลับ</a>
            </form>
        </div>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
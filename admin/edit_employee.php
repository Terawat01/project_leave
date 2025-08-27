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

// Handle Form Submission (POST Request)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_id = $_POST['emp_id'];
    $position_id = $_POST['position_id'];
    $emp_name = $_POST['emp_name'];
    $emp_lastname = $_POST['emp_lastname'];
    $email = $_POST['email'];
    $new_password = $_POST['password'];

    // SQL for updating employee data
    if (!empty($new_password)) {
        // If new password is provided, hash it and update
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE employee SET Position_ID=?, Emp_Name=?, Emp_LastName=?, Email=?, Password=? WHERE Emp_id=?");
        $stmt->bind_param("ssssss", $position_id, $emp_name, $emp_lastname, $email, $hashed_password, $emp_id);
    } else {
        // If password is not provided, update other fields only
        $stmt = $conn->prepare("UPDATE employee SET Position_ID=?, Emp_Name=?, Emp_LastName=?, Email=? WHERE Emp_id=?");
        $stmt->bind_param("sssss", $position_id, $emp_name, $emp_lastname, $email, $emp_id);
    }

    if ($stmt->execute()) {
        $message = "อัปเดตข้อมูลพนักงานเรียบร้อยแล้ว";
    } else {
        $error = "เกิดข้อผิดพลาดในการอัปเดต: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch current employee data to pre-fill the form (GET Request)
$stmt = $conn->prepare("SELECT * FROM employee WHERE Emp_id = ?");
$stmt->bind_param("s", $emp_id_to_edit);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    echo "<div class='main-content p-4'><div class='alert alert-danger'>ไม่พบข้อมูลพนักงาน</div></div>";
    exit();
}

// Fetch all positions for the dropdown
$positions = $conn->query("SELECT * FROM position");
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
            <form method="POST" action="edit_employee.php?id=<?php echo htmlspecialchars($employee['Emp_id']); ?>">
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
                </div>
                <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                <a href="manage_employees.php" class="btn btn-secondary">ยกเลิก</a>
            </form>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php';
$stmt->close();
$conn->close();
?>
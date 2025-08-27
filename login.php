<?php
// require_once 'includes/db.php'; // เราจะเรียกใช้ session_start() ที่นี่โดยตรง
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- Database Connection ---
    $servername = "127.0.0.1";
    $username = "root";
    $password_db = ""; // ใส่รหัสผ่านฐานข้อมูลของคุณที่นี่ (ถ้ามี)
    $dbname = "leave";
    
    $conn = new mysqli($servername, $username, $password_db, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    // -------------------------

    if (empty($_POST['emp_id']) || empty($_POST['password'])) {
        $error = "กรุณากรอกรหัสพนักงานและรหัสผ่าน";
    } else {
        $emp_id = $_POST['emp_id'];
        $password_form = $_POST['password'];

        $stmt = $conn->prepare("SELECT Emp_id, Emp_Name, Position_ID, Password FROM employee WHERE Emp_id = ?");
        $stmt->bind_param("s", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // ตรวจสอบรหัสผ่านที่กรอกกับรหัสผ่านที่เข้ารหัสในฐานข้อมูล
            if (password_verify($password_form, $user['Password'])) {
                // หากรหัสผ่านถูกต้อง, สร้าง session
                $_SESSION['user_id'] = $user['Emp_id'];
                $_SESSION['user_name'] = $user['Emp_Name'];
                $_SESSION['position_id'] = $user['Position_ID'];

                // ตรวจสอบตำแหน่งเพื่อส่งไปยังหน้าแดชบอร์ดที่ถูกต้อง
                // Position ID 4 คือ 'ผู้จัดการร้าน' (Admin)
                if ($user['Position_ID'] == '4') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: employee/dashboard.php");
                }
                exit();

            } else {
                // รหัสผ่านไม่ถูกต้อง
                $error = "รหัสพนักงานหรือรหัสผ่านไม่ถูกต้อง";
            }
        } else {
            // ไม่พบรหัสพนักงานนี้ในระบบ
            $error = "รหัสพนักงานหรือรหัสผ่านไม่ถูกต้อง";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Leave System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #e3e8f0; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <h2>Leave System</h2>
                <p class="text-muted">ระบบจัดการการลาของพนักงาน</p>
            </div>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="card-title text-center mb-4">เข้าสู่ระบบ</h5>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="emp_id" class="form-label">รหัสพนักงาน</label>
                            <input type="text" class="form-control" id="emp_id" name="emp_id" placeholder="เช่น em001" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
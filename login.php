<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 3600); // Session lifetime 1 hour
    session_set_cookie_params(3600);
    session_start();
}

// If user already logged in → redirect based on position
if (isset($_SESSION['user_id']) && isset($_SESSION['position_id'])) {
    if ($_SESSION['position_id'] == 4) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: employee/dashboard.php");
    }
    exit();
}

$error = "";

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $servername = "127.0.0.1";
    $username = "root";
    $password_db = "";
    $dbname = "leave";

    // Connect to database
    $conn = new mysqli($servername, $username, $password_db, $dbname);
    if ($conn->connect_error) {
        die("❌ Database Connection Failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");

    // Get input values
    $emp_id = trim($_POST['emp_id']);
    $password_form = trim($_POST['password']);

    // Validate inputs
    if (empty($emp_id) || empty($password_form)) {
        $error = "กรุณากรอกรหัสพนักงานและรหัสผ่าน";
    } else {
        // Query employee data
        $stmt = $conn->prepare("SELECT Emp_id, Emp_Name, Position_ID, Password FROM employee WHERE Emp_id = ?");
        $stmt->bind_param("s", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password_form, $user['Password'])) {
                // Create secure session
                $_SESSION['user_id'] = $user['Emp_id'];
                $_SESSION['user_name'] = $user['Emp_Name'];
                $_SESSION['position_id'] = $user['Position_ID'];

                // Redirect based on role
                if ($user['Position_ID'] == 4) {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: employee/dashboard.php");
                }
                exit();
            } else {
                $error = "รหัสพนักงานหรือรหัสผ่านไม่ถูกต้อง";
            }
        } else {
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
    <title>เข้าสู่ระบบ | Leave System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f5f7fa;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            padding: 15px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
    </style>
</head>
<body>
<div class="container login-container">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header">
                ระบบจัดการการลา
            </div>
            <div class="card-body p-4">
                <h5 class="text-center mb-3">เข้าสู่ระบบ</h5>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="emp_id" class="form-label">รหัสพนักงาน</label>
                        <input type="text" class="form-control" id="emp_id" name="emp_id" placeholder="เช่น EM001" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">รหัสผ่าน</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">เข้าสู่ระบบ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById("togglePassword").addEventListener("click", function() {
    const pwdInput = document.getElementById("password");
    const icon = document.getElementById("toggleIcon");
    if (pwdInput.type === "password") {
        pwdInput.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        pwdInput.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
});
</script>
</body>
</html>

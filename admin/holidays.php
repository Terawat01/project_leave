<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

// Handle Add Holiday
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_holiday'])) {
    $name = $_POST['holiday_name'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $stmt = $conn->prepare("INSERT INTO dayoff (Dayoff_Name, Dayoff_start_date, Dayoff_end_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $start_date, $end_date);
    $stmt->execute();
    header("Location: holidays.php");
    exit();
}

// Handle Delete Holiday
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM dayoff WHERE Dayoff_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: holidays.php");
    exit();
}


require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

$holidays = $conn->query("SELECT * FROM dayoff ORDER BY Dayoff_start_date ASC");
?>
<div class="main-content p-4">
    <h4 class="mb-4">จัดการวันหยุด</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">เพิ่มวันหยุดใหม่</div>
                <div class="card-body">
                    <form method="POST" action="holidays.php">
                        <div class="mb-3">
                            <label class="form-label">ชื่อวันหยุด</label>
                            <input type="text" name="holiday_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">วันที่เริ่มต้น</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                         <div class="mb-3">
                            <label class="form-label">วันที่สิ้นสุด</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                        <button type="submit" name="add_holiday" class="btn btn-primary">บันทึก</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">วันหยุดทั้งหมด</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr><th>ชื่อวันหยุด</th><th>วันที่</th><th>ดำเนินการ</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($holidays->num_rows > 0): ?>
                            <?php while($row = $holidays->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Dayoff_Name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Dayoff_start_date']); ?></td>
                                <td>
                                    <a href="?action=delete&id=<?php echo $row['Dayoff_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณแน่ใจหรือไม่?')">ลบ</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">ไม่มีข้อมูลวันหยุด</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php';
$conn->close();
?>
<?php
require_once '../includes/db.php';

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $emp_id_to_delete = $_GET['id'];
    // To prevent deleting the main admin account, add a check
    if ($emp_id_to_delete == 'em005') {
        // Optionally set an error message
    } else {
        $stmt = $conn->prepare("DELETE FROM employee WHERE Emp_id = ?");
        $stmt->bind_param("s", $emp_id_to_delete);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manage_employees.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

$result = $conn->query("SELECT e.*, p.Position_Name FROM employee e JOIN position p ON e.Position_ID = p.Position_ID ORDER BY e.Emp_id ASC");
?>
<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>จัดการข้อมูลพนักงาน</h4>
        <a href="add_employee.php" class="btn btn-primary">+ เพิ่มพนักงาน</a>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>รหัสพนักงาน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ตำแหน่ง</th>
                        <th>อีเมล</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Emp_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['Emp_Name'] . " " . $row['Emp_LastName']); ?></td>
                            <td><?php echo htmlspecialchars($row['Position_Name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td>
                                <a href="?action=delete&id=<?php echo $row['Emp_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบพนักงานคนนี้?')">ลบ</a>
                                <a href="edit_employee.php?id=<?php echo $row['Emp_id']; ?>" class="btn btn-sm btn-outline-primary">แก้ไข</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">ไม่มีข้อมูลพนักงาน</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
    require_once '../includes/footer.php';
    $conn->close();
?>
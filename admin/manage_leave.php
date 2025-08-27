<?php
require_once '../includes/db.php';

// Handle Actions (Approve/Reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $leave_id = $_GET['id'];
    $approved_date = date('Y-m-d');
    $new_status_id = '';

    if ($action == 'approve') {
        $new_status_id = '1'; // Approved
    } elseif ($action == 'reject') {
        $new_status_id = '2'; // Rejected
    }

    if (!empty($new_status_id)) {
        $stmt = $conn->prepare("UPDATE emp_leave SET Leave_Status_ID = ?, Approved_date = ? WHERE Leave_ID = ?");
        $stmt->bind_param("sss", $new_status_id, $approved_date, $leave_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_leave.php"); // Redirect to avoid re-submission
        exit();
    }
}


require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

// Fetch all leave requests
$result = $conn->query("
    SELECT el.*, e.Emp_Name, lt.Leave_Type_Name, ls.Leave_Type_Name as status
    FROM emp_leave el
    JOIN employee e ON el.Emp_ID = e.Emp_id
    JOIN leave_type lt ON el.Leave_Type_ID = lt.Leave_Type_ID
    JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
    ORDER BY
        CASE el.Leave_Status_ID
            WHEN '3' THEN 1 -- Pending first
            WHEN '1' THEN 2 -- Approved second
            ELSE 3
        END,
        el.Request_date DESC
");

?>
<div class="main-content p-4">
    <h4 class="mb-4">จัดการการลา</h4>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>พนักงาน</th>
                        <th>ประเภท</th>
                        <th>วันที่ลา</th>
                        <th>เหตุผล</th>
                        <th>สถานะ</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Emp_Name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Leave_Type_Name']); ?></td>
                            <td><?php echo htmlspecialchars($row['Start_leave_date']) . " - " . htmlspecialchars($row['End_Leave_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['Reason'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td>
                                <?php if ($row['Leave_Status_ID'] == '3'): // If pending ?>
                                    <a href="?action=approve&id=<?php echo $row['Leave_ID']; ?>" class="btn btn-success btn-sm">อนุมัติ</a>
                                    <a href="?action=reject&id=<?php echo $row['Leave_ID']; ?>" class="btn btn-danger btn-sm">ปฏิเสธ</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center text-muted">ไม่มีข้อมูลการลา</td></tr>
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
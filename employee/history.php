<?php
require_once '../includes/db.php';

// Handle Cancel Action
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $leave_id_to_cancel = $_GET['id'];
    $current_emp_id = $_SESSION['user_id'];

    // Delete the leave request only if it belongs to the current user and is pending
    $stmt = $conn->prepare("DELETE FROM emp_leave WHERE Leave_ID = ? AND Emp_ID = ? AND Leave_Status_ID = '3'");
    $stmt->bind_param("ss", $leave_id_to_cancel, $current_emp_id);
    $stmt->execute();
    $stmt->close();
    header("Location: history.php"); // Redirect to refresh the page
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

$emp_id = $_SESSION['user_id'];

// Get filter values from URL
$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';

// Base SQL query
$sql = "
    SELECT
        el.Leave_ID,
        el.Start_leave_date,
        el.End_Leave_date,
        DATEDIFF(el.End_Leave_date, el.Start_leave_date) + 1 as total_days,
        el.Reason,
        el.Request_date,
        el.Approved_date,
        lt.Leave_Type_Name,
        ls.Leave_Type_Name as status,
        el.Leave_Status_ID
    FROM emp_leave el
    JOIN leave_type lt ON el.Leave_Type_ID = lt.Leave_Type_ID
    JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
    WHERE el.Emp_ID = ?
";

// Build dynamic WHERE clauses for filtering
$params = [$emp_id];
$types = "s";

if (!empty($search_query)) {
    $sql .= " AND (lt.Leave_Type_Name LIKE ? OR el.Reason LIKE ?)";
    $search_term = "%" . $search_query . "%";
    array_push($params, $search_term, $search_term);
    $types .= "ss";
}
if (!empty($status_filter)) {
    $sql .= " AND el.Leave_Status_ID = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if (!empty($type_filter)) {
    $sql .= " AND el.Leave_Type_ID = ?";
    $params[] = $type_filter;
    $types .= "s";
}

$sql .= " ORDER BY el.Start_leave_date DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Fetch statuses and types for dropdowns
$statuses = $conn->query("SELECT * FROM leave_status");
$leave_types = $conn->query("SELECT * FROM leave_type");

?>
<div class="main-content p-4">
    <h4 class="mb-1">ประวัติการลา</h4>
    <p class="text-muted">ประวัติการลาทั้งหมดของคุณ</p>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-funnel"></i> ค้นหาและกรอง</h5>
            <form method="GET" action="history.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="search" class="form-label">ค้นหา</label>
                    <input type="text" class="form-control" name="search" id="search" placeholder="ค้นหาประเภทการลาหรือเหตุผล..." value="<?php echo htmlspecialchars($search_query); ?>">
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">สถานะ</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <?php while($s = $statuses->fetch_assoc()): ?>
                            <option value="<?php echo $s['Leave_Status_ID']; ?>" <?php echo ($status_filter == $s['Leave_Status_ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['Leave_Type_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                 <div class="col-md-3">
                    <label for="type" class="form-label">ประเภทการลา</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">ทุกประเภท</option>
                        <?php while($lt = $leave_types->fetch_assoc()): ?>
                            <option value="<?php echo $lt['Leave_Type_ID']; ?>" <?php echo ($type_filter == $lt['Leave_Type_ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lt['Leave_Type_Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">ค้นหา</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
           <i class="bi bi-clock-history"></i> ประวัติการลา (<?php echo $result->num_rows; ?> รายการ)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ประเภทการลา</th>
                            <th>วันที่ลา</th>
                            <th>จำนวนวัน</th>
                            <th>เหตุผล</th>
                            <th>สถานะ</th>
                            <th>วันที่อนุมัติ</th>
                            <th>วันที่ส่งคำขอ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($result) && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['Leave_Type_Name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['Start_leave_date'])) . " ถึง " . date('d/m/Y', strtotime($row['End_Leave_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['total_days']); ?></td>
                                <td><?php echo htmlspecialchars($row['Reason'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo ($row['Approved_date'] != '0000-00-00') ? date('d/m/Y', strtotime($row['Approved_date'])) : '-'; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['Request_date'])); ?></td>
                                <td>
                                    <?php if ($row['Leave_Status_ID'] == '3'): // If status is 'Pending' ?>
                                        <a href="?action=cancel&id=<?php echo $row['Leave_ID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('คุณต้องการยกเลิกคำขอนี้ใช่หรือไม่?')">
                                            ยกเลิก
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted">ไม่มีข้อมูลประวัติการลา</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
require_once '../includes/footer.php';
if (isset($stmt)) $stmt->close();
$conn->close();
?>
<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $leave_id = $_GET['id'];
    $approved_date = date('Y-m-d');
    $new_status_id = '';
    $notification_title = '';
    $notification_message = '';
    $notification_type = '';

    // Get Emp_ID for notification
    $stmt = $conn->prepare("SELECT Emp_ID, Leave_Type_ID, Start_leave_date, End_Leave_date FROM emp_leave WHERE Leave_ID = ?");
    $stmt->bind_param("s", $leave_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave_row = $result->fetch_assoc();
    $stmt->close();

    if (!$leave_row) {
        header("Location: manage_leave.php");
        exit();
    }

    $emp_id_to_notify = $leave_row['Emp_ID'];
    $leave_type_id = $leave_row['Leave_Type_ID'];
    $start_date = date('d M Y', strtotime($leave_row['Start_leave_date']));
    $end_date   = date('d M Y', strtotime($leave_row['End_Leave_date']));

    // Get leave type name
    $stmt = $conn->prepare("SELECT Leave_Type_Name FROM leave_type WHERE Leave_Type_ID = ?");
    $stmt->bind_param("s", $leave_type_id);
    $stmt->execute();
    $leave_type = $stmt->get_result()->fetch_assoc()['Leave_Type_Name'];
    $stmt->close();

    // Define status & notification content
    if ($action === 'approve') {
        $new_status_id = '1';
        $notification_type = 'approved';
        $notification_title = 'คำขอลาได้รับการอนุมัติ';
        $notification_message = "คำขอลา $leave_type ของคุณ ($start_date - $end_date) ได้รับการอนุมัติแล้ว";
    } elseif ($action === 'reject') {
        $new_status_id = '2';
        $notification_type = 'rejected';
        $notification_title = 'คำขอลาถูกปฏิเสธ';
        $notification_message = "คำขอลา $leave_type ของคุณ ($start_date - $end_date) ไม่ได้รับการอนุมัติ";
    }

    // Update leave status
    if (!empty($new_status_id)) {
        $stmt = $conn->prepare("UPDATE emp_leave SET Leave_Status_ID = ?, Approved_date = ? WHERE Leave_ID = ?");
        $stmt->bind_param("sss", $new_status_id, $approved_date, $leave_id);
        $stmt->execute();
        $stmt->close();

        // Insert into notifications table
        $stmt = $conn->prepare("INSERT INTO notifications (emp_id, type, title, message, is_read) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("ssss", $emp_id_to_notify, $notification_type, $notification_title, $notification_message);
        $stmt->execute();
        $stmt->close();

        // Send WebSocket notification
        $payload = json_encode([
            'emp_id' => $emp_id_to_notify,
            'title' => $notification_title,
            'message' => $notification_message
        ]);

        $ch = curl_init('http://localhost:8080');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_exec($ch);
        curl_close($ch);

        header("Location: manage_leave.php");
        exit();
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';

// Fetch all leave requests (แก้ชื่อคอลัมน์)
$result = $conn->query("
    SELECT el.*, e.Emp_Name, lt.Leave_Type_Name, ls.Leave_Type_Name AS status
    FROM emp_leave el
    JOIN employee e ON el.Emp_ID = e.Emp_id
    JOIN leave_type lt ON el.Leave_Type_ID = lt.Leave_Type_ID
    JOIN leave_status ls ON el.Leave_Status_ID = ls.Leave_Status_ID
    ORDER BY
        CASE el.Leave_Status_ID
            WHEN '3' THEN 1
            WHEN '1' THEN 2
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
                        <th>วันที่เริ่มลา - วันที่สิ้นสุด</th>
                        <th>เหตุผล</th>
                        <th>สถานะ</th>
                        <th>ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Emp_Name']) ?></td>
                            <td><?= htmlspecialchars($row['Leave_Type_Name']) ?></td>
                            <td><?= htmlspecialchars($row['Start_leave_date']) ?> - <?= htmlspecialchars($row['End_Leave_date']) ?></td>
                            <td><?= htmlspecialchars($row['Reason'] ?: '-') ?></td>
                            <td>
                                <?php
                                    if ($row['Leave_Status_ID'] == '1') {
                                        echo "<span class='badge bg-success'>อนุมัติ</span>";
                                    } elseif ($row['Leave_Status_ID'] == '2') {
                                        echo "<span class='badge bg-danger'>ไม่อนุมัติ</span>";
                                    } else {
                                        echo "<span class='badge bg-warning text-dark'>รออนุมัติ</span>";
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['Leave_Status_ID'] == '3'): ?>
                                    <a href="?action=approve&id=<?= $row['Leave_ID'] ?>" class="btn btn-success btn-sm">อนุมัติ</a>
                                    <a href="?action=reject&id=<?= $row['Leave_ID'] ?>" class="btn btn-danger btn-sm">ปฏิเสธ</a>
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

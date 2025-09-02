<?php
require_once '../includes/db.php';

if (!isset($_POST['id'], $_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit();
}

$leave_id = $_POST['id'];
$action = $_POST['action'];

if (!ctype_digit($leave_id) || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'พารามิเตอร์ไม่ถูกต้อง']);
    exit();
}

$status_id = $action === 'approve' ? '1' : '2';
$approved_date = date('Y-m-d');

// อัปเดตสถานะ
$stmt = $conn->prepare("UPDATE emp_leave SET Leave_Status_ID = ?, Approved_date = ? WHERE Leave_ID = ?");
$stmt->bind_param("sss", $status_id, $approved_date, $leave_id);
if ($stmt->execute()) {
    $badge_class = $status_id === '1' ? 'success' : 'danger';
    $status_name = $status_id === '1' ? 'อนุมัติ' : 'ไม่อนุมัติ';

    echo json_encode([
        'success' => true,
        'status_html' => "<span class='badge bg-$badge_class'>$status_name</span>"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปเดตได้']);
}
$stmt->close();
$conn->close();
?>

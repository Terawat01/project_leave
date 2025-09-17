<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] == 4) {
    header("Location: ../login.php");
    exit();
}
require_once '../includes/db.php';

$emp_id = $_SESSION['user_id'];

/* ========= Handle actions ========= */

// อ่านทั้งหมด
if (($_GET['action'] ?? $_POST['action'] ?? null) === 'read_all') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE emp_id = ?");
    $stmt->bind_param("s", $emp_id);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php");
    exit();
}

// คลิกอ่าน 1 รายการ แล้ววาร์ปไป history.php
if (($_GET['action'] ?? null) === 'open' && isset($_GET['id'])) {
    $nid = $_GET['id'];

    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND emp_id = ?");
    $stmt->bind_param("ss", $nid, $emp_id);
    $stmt->execute();
    $stmt->close();

    // (ออปชั่น) โฟกัส leave_id ถ้าคุณเก็บไว้ในข้อความ/ฟิลด์อื่น
    $focus = '';
    $stmt = $conn->prepare("SELECT message FROM notifications WHERE id = ? AND emp_id = ?");
    $stmt->bind_param("ss", $nid, $emp_id);
    $stmt->execute();
    $msgRes = $stmt->get_result();
    if ($row = $msgRes->fetch_assoc()) {
        if (preg_match('/Leave:\s*([A-Za-z0-9_-]+)/', $row['message'], $m)) {
            $focus = $m[1];
        }
    }
    $stmt->close();

    $to = 'history.php';
    if ($focus !== '') $to .= '?focus=' . urlencode($focus);
    header("Location: " . $to);
    exit();
}

/* ========= Query ========= */
$stmt = $conn->prepare("SELECT * FROM notifications WHERE emp_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$notifications = $stmt->get_result();

$unread_count = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE emp_id = '".$conn->real_escape_string($emp_id)."' AND is_read = 0")) {
    $unread_count = (int)($res->fetch_assoc()['c'] ?? 0);
}

function getNotificationIcon($type) {
    switch ($type) {
        case 'approved':   return ['icon' => 'bi-check-circle-fill',         'color' => 'text-success'];
        case 'rejected':   return ['icon' => 'bi-x-circle-fill',             'color' => 'text-danger'];
        case 'new_request':return ['icon' => 'bi-clock-fill',                'color' => 'text-warning'];
        case 'system':     return ['icon' => 'bi-gear-fill',                 'color' => 'text-primary'];
        case 'warning':    return ['icon' => 'bi-exclamation-triangle-fill', 'color' => 'text-danger'];
        default:           return ['icon' => 'bi-bell-fill',                 'color' => 'text-secondary'];
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';
?>
<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            การแจ้งเตือนล่าสุด
            <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger"><?= $unread_count ?></span>
            <?php endif; ?>
        </h4>
        <a href="notifications.php?action=read_all" class="btn btn-sm btn-outline-secondary">อ่านทั้งหมด</a>
    </div>

    <div class="list-group">
        <?php if ($notifications->num_rows > 0): ?>
            <?php while ($row = $notifications->fetch_assoc()):
                $icon = getNotificationIcon($row['type']);
            ?>
            <a href="notifications.php?action=open&id=<?= urlencode($row['id']) ?>"
               class="list-group-item list-group-item-action <?= ($row['is_read'] == 0) ? 'bg-light' : ''; ?>">
                <div class="d-flex w-100">
                    <div class="me-3 fs-3 <?= $icon['color']; ?>">
                        <i class="bi <?= $icon['icon']; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-1"><?= htmlspecialchars($row['title']); ?></h5>
                            <small class="text-muted"><?= date('d M H:i', strtotime($row['created_at'])); ?> น.</small>
                        </div>
                        <p class="mb-1"><?= htmlspecialchars($row['message']); ?></p>
                        <small class="<?= $row['is_read'] ? 'text-muted' : 'fw-bold text-danger' ?>">
                            <?= $row['is_read'] ? 'อ่านแล้ว' : 'ยังไม่ได้อ่าน' ?>
                        </small>
                    </div>
                </div>
            </a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center p-5 text-muted">
                <i class="bi bi-bell-slash fs-1"></i>
                <p class="mt-2">ไม่มีการแจ้งเตือน</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
require_once '../includes/footer.php';
$stmt->close();
$conn->close();

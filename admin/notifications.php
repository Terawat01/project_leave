<?php
// ---- Bootstrap session & auth (ทำก่อนมี output เสมอ) ----
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['position_id'] != 4) {
    header("Location: ../logout.php");
    exit();
}

require_once '../includes/db.php';

$admin_id = $_SESSION['user_id'];

// ---- Handle actions BEFORE any output ----
// ใช้ POST เป็นหลัก (ปลอดภัยกว่า) และรองรับ GET เป็น fallback
$action = $_POST['action'] ?? ($_GET['action'] ?? null);
if ($action === 'read_all') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE emp_id = ?");
    $stmt->bind_param("s", $admin_id);
    $stmt->execute();
    $stmt->close();

    header("Location: notifications.php");
    exit();
}

// ---- Query data BEFORE output (โอเค) ----
$stmt = $conn->prepare("SELECT * FROM notifications WHERE emp_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$notifications = $stmt->get_result();

$unread_count = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM notifications WHERE emp_id = '".$conn->real_escape_string($admin_id)."' AND is_read = 0")) {
    $row = $res->fetch_assoc();
    $unread_count = (int)($row['c'] ?? 0);
}

function getNotificationIcon($type) {
    switch ($type) {
        case 'approved':  return ['icon' => 'bi-check-circle-fill',        'color' => 'text-success'];
        case 'rejected':  return ['icon' => 'bi-x-circle-fill',            'color' => 'text-danger'];
        case 'new_request': return ['icon' => 'bi-clock-fill',            'color' => 'text-warning'];
        case 'system':    return ['icon' => 'bi-gear-fill',               'color' => 'text-primary'];
        case 'warning':   return ['icon' => 'bi-exclamation-triangle-fill','color' => 'text-danger'];
        default:          return ['icon' => 'bi-bell-fill',               'color' => 'text-secondary'];
    }
}

// ---- หลังจากนี้ค่อยเริ่ม output ----
require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';
?>
<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            การแจ้งเตือนล่าสุด
            <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </h4>

        <!-- ใช้ POST เพื่อความปลอดภัย และหลีกเลี่ยงการใช้ header() หลังมี output -->
        <form method="post" action="notifications.php" class="m-0">
            <input type="hidden" name="action" value="read_all">
            <button type="submit" class="btn btn-sm btn-outline-secondary">ทำเครื่องหมายว่าอ่านแล้วทั้งหมด</button>
        </form>
    </div>

    <div class="list-group">
        <?php if ($notifications->num_rows > 0): ?>
            <?php while ($row = $notifications->fetch_assoc()):
                $icon = getNotificationIcon($row['type']);
            ?>
            <div class="list-group-item list-group-item-action <?php echo ($row['is_read'] == 0) ? 'bg-light' : ''; ?>">
                <div class="d-flex w-100">
                    <div class="me-3 fs-3 <?php echo $icon['color']; ?>">
                        <i class="bi <?php echo $icon['icon']; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <small class="text-muted">
                                <?php echo date('d M H:i', strtotime($row['created_at'])); ?> น.
                            </small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($row['message']); ?></p>
                        <?php if ($row['is_read']): ?>
                            <small class="text-muted">อ่านแล้ว</small>
                        <?php else: ?>
                            <small class="fw-bold text-danger">ยังไม่ได้อ่าน</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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

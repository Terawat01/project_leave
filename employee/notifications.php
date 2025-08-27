<?php
require_once '../includes/db.php';

// Handle Mark all as read
if (isset($_GET['action']) && $_GET['action'] == 'read_all') {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE emp_id = ?");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php");
    exit();
}

require_once '../includes/header.php';
require_once '../includes/sidebar_employee.php';

$emp_id = $_SESSION['user_id'];

// Fetch notifications for the user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE emp_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $emp_id);
$stmt->execute();
$notifications = $stmt->get_result();

// Re-fetch unread count after potential update
$unread_count_result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE emp_id = '$emp_id' AND is_read = 0");
$unread_count = $unread_count_result->fetch_assoc()['count'];


function getNotificationIcon($type) {
    switch ($type) {
        case 'approved':
            return ['icon' => 'bi-check-circle-fill', 'color' => 'text-success'];
        case 'new_request':
            return ['icon' => 'bi-clock-fill', 'color' => 'text-warning'];
        case 'system':
            return ['icon' => 'bi-gear-fill', 'color' => 'text-primary'];
        case 'warning':
            return ['icon' => 'bi-exclamation-triangle-fill', 'color' => 'text-danger'];
        default:
            return ['icon' => 'bi-bell-fill', 'color' => 'text-secondary'];
    }
}
?>
<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>
            การแจ้งเตือนล่าสุด 
            <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </h4>
        <a href="?action=read_all" class="btn btn-sm btn-outline-secondary">อ่านทั้งหมด</a>
    </div>

    <div class="list-group">
        <?php if ($notifications->num_rows > 0): ?>
            <?php while($row = $notifications->fetch_assoc()): 
                $icon_details = getNotificationIcon($row['type']);
            ?>
            <div class="list-group-item list-group-item-action <?php echo ($row['is_read'] == 0) ? 'bg-light' : ''; ?>">
                <div class="d-flex w-100">
                    <div class="me-3 fs-3 <?php echo $icon_details['color']; ?>">
                        <i class="bi <?php echo $icon_details['icon']; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <small class="text-muted"><?php echo date('d M H:i', strtotime($row['created_at'])); ?> น.</small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($row['message']); ?></p>
                        <?php if ($row['is_read'] == 1): ?>
                            <small class="text-muted">ทำเครื่องหมายอ่านแล้ว</small>
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
?>
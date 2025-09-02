<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm px-3">
    <a class="navbar-brand fw-bold" href="#">ระบบจัดการการลา</a>
    <div class="ms-auto d-flex align-items-center">
        <!-- Notification Bell -->
        <div class="position-relative me-3">
            <a href="../employee/notifications.php" class="btn btn-light position-relative">
                <i class="bi bi-bell fs-4"></i>
                <span id="notification-badge"
                      class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="display:none;">0</span>
            </a>
        </div>
        <!-- Logout Button -->
        <a href="../logout.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
        </a>
    </div>
</nav>

<!-- Toast Container -->
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const userId = <?php echo json_encode($_SESSION['user_id']); ?>;
    let ws;

    function connectWebSocket() {
        ws = new WebSocket(`ws://localhost:8080/?user_id=${userId}`);

        ws.onopen = () => console.log("✅ WebSocket connected");

        ws.onmessage = (event) => {
            try {
                const notification = JSON.parse(event.data);

                // Update badge counter
                const badge = document.getElementById("notification-badge");
                if (badge) {
                    let count = parseInt(badge.innerText) || 0;
                    badge.innerText = count + 1;
                    badge.style.display = "inline";
                }

                // Show toast notification
                showToast(notification.title, notification.message);

            } catch (e) {
                console.error("Invalid WebSocket data:", event.data);
            }
        };

        ws.onclose = () => {
            console.warn("⚠️ WebSocket disconnected, retrying in 3s...");
            setTimeout(connectWebSocket, 3000);
        };

        ws.onerror = (err) => {
            console.error("❌ WebSocket error:", err);
            ws.close();
        };
    }

    // Show toast notification
    function showToast(title, message) {
        const container = document.getElementById("toast-container");

        const toast = document.createElement("div");
        toast.classList.add("toast", "align-items-center", "text-bg-primary", "border-0", "show", "mb-2");
        toast.role = "alert";
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    connectWebSocket();
});
</script>
<?php endif; ?>

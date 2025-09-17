<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // หาองค์ประกอบที่ต้องใช้จาก id
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            const toggleButton = document.getElementById('sidebarToggle');

            // ตรวจสอบว่าปุ่มมีอยู่จริงหรือไม่
            if (toggleButton) {
                // เพิ่ม Event Listener เมื่อมีการคลิกที่ปุ่ม
                toggleButton.addEventListener('click', function() {
                    // สั่งให้สลับ (toggle) class 'collapsed' บน sidebar และ main-content
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('collapsed');
                });
            }
        });
    </script>
    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
        const user_id = <?php echo json_encode($_SESSION['user_id']); ?>;
        let ws;

        function connectWebSocket() {
            ws = new WebSocket(`ws://localhost:8080/?user_id=${user_id}`);

            ws.onopen = () => {
                console.log("✅ WebSocket Connected");
            };

            ws.onmessage = (event) => {
                try {
                    const notification = JSON.parse(event.data);

                    // Show notification in console
                    console.log("📩 New Notification:", notification);

                    // Update notification badge
                    const notificationBadge = document.getElementById('notification-badge');
                    if (notificationBadge) {
                        let currentCount = parseInt(notificationBadge.innerText) || 0;
                        notificationBadge.innerText = currentCount + 1;
                        notificationBadge.style.display = 'inline';
                    }

                    // Show toast instead of alert
                    showToast(notification.title || "การแจ้งเตือน", notification.message);
                } catch (error) {
                    console.error("Invalid WebSocket Data:", event.data);
                }
            };

            ws.onclose = () => {
                console.warn("⚠️ WebSocket Disconnected. Reconnecting in 3s...");
                setTimeout(connectWebSocket, 3000);
            };

            ws.onerror = (err) => {
                console.error("❌ WebSocket Error: ", err);
                ws.close();
            };
        }

        function showToast(title, message) {
            // Create toast container if missing
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.style.position = 'fixed';
                container.style.bottom = '20px';
                container.style.right = '20px';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.style.background = '#333';
            toast.style.color = '#fff';
            toast.style.padding = '10px 15px';
            toast.style.marginTop = '10px';
            toast.style.borderRadius = '8px';
            toast.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.3)';
            toast.style.fontSize = '14px';
            toast.style.transition = 'opacity 0.5s ease';
            toast.innerHTML = `<strong>${title}</strong><br>${message}`;

            container.appendChild(toast);

            // Auto-remove toast after 5s
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 500);
            }, 5000);
        }

        // Start WebSocket connection
        connectWebSocket();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        const checkAll = document.getElementById('check-all');
        const rowChecks = document.querySelectorAll('.row-check');
        const btns = [
            document.getElementById('btn-bulk-delete'),
            document.getElementById('btn-bulk-delete-top')
        ].filter(Boolean);
        const bulkForm = document.getElementById('bulkForm');
        const bulkFormTop = document.getElementById('bulkFormTop');

        function updateButtons() {
            const anyChecked = Array.from(rowChecks).some(ch => ch.checked);
            btns.forEach(b => b && (b.disabled = !anyChecked));
        }

        // เลือกทั้งหมด
        if (checkAll) {
            checkAll.addEventListener('change', () => {
            rowChecks.forEach(ch => ch.checked = checkAll.checked);
            updateButtons();
            });
        }

        // เปลี่ยนสถานะช่องใด ๆ
        rowChecks.forEach(ch => ch.addEventListener('change', updateButtons));
        updateButtons();

        // ยืนยันก่อนส่ง
        [bulkForm, bulkFormTop].forEach(f => {
            if (!f) return;
            f.addEventListener('submit', (e) => {
            const anyChecked = Array.from(document.querySelectorAll('.row-check')).some(ch => ch.checked);
            if (!anyChecked) {
                e.preventDefault();
                alert('กรุณาเลือกรายการที่จะลบก่อน');
                return false;
            }
            if (!confirm('ยืนยันการลบรายการที่เลือกทั้งหมดหรือไม่?')) {
                e.preventDefault();
                return false;
            }
            });
        });
        });
        </script>

    <?php endif; ?>
    
</body>
</html>
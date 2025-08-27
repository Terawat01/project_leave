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
</body>
</html>
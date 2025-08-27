<?php
// --- การตั้งค่าการเชื่อมต่อ ---
$servername = "127.0.0.1";
$username = "root";
$password = ""; // รหัสผ่านของ MySQL ใน XAMPP ส่วนใหญ่จะว่าง
$dbname = "leave";
// ----------------------------

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    // ถ้าเชื่อมต่อไม่สำเร็จ
    die("<h3 style='color:red;'>การเชื่อมต่อล้มเหลว: " . $conn->connect_error . "</h3><p>กรุณาตรวจสอบการตั้งค่าในไฟล์ check_db.php หรือดูว่าคุณเปิดใช้งาน MySQL ใน XAMPP แล้วหรือยัง</p>");
}

// ถ้าเชื่อมต่อสำเร็จ
echo "<h3 style='color:green;'>เชื่อมต่อฐานข้อมูล `" . $dbname . "` สำเร็จ!</h3>";
echo "<p>การตั้งค่าการเชื่อมต่อในไฟล์ db.php ของคุณถูกต้องแล้ว</p>";

// ปิดการเชื่อมต่อ
$conn->close();
?>
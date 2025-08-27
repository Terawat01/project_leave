<?php
echo "<h1>เริ่มต้นการดีบักการล็อกอิน</h1>";

// --- Database Connection ---
$servername = "127.0.0.1";
$username = "root";
$password_db = "";
$dbname = "leave";

$conn = new mysqli($servername, $username, $password_db, $dbname);
if ($conn->connect_error) {
    die("<p style='color:red;'>การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error . "</p>");
}
$conn->set_charset("utf8");
echo "<p style='color:green;'>1. เชื่อมต่อฐานข้อมูลสำเร็จ</p>";
// -------------------------

// 2. กำหนดค่าที่จะทดสอบโดยตรง (ไม่ต้องกรอกฟอร์ม)
$test_emp_id = 'em005'; // ทดสอบกับ Admin
$test_password = '123456'; // รหัสผ่านที่ถูกต้อง
echo "<p>2. กำลังทดสอบกับผู้ใช้: <b>" . $test_emp_id . "</b> และรหัสผ่าน: <b>" . $test_password . "</b></p>";


// 3. ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$stmt = $conn->prepare("SELECT Password FROM employee WHERE Emp_id = ?");
$stmt->bind_param("s", $test_emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $hashed_password_from_db = $user['Password'];
    echo "<p>3. ดึงข้อมูลรหัสผ่านที่เข้ารหัสจากฐานข้อมูลสำเร็จ</p>";
    echo "<p style='font-family: monospace; color: blue; word-wrap: break-word;'>รหัสจากฐานข้อมูลคือ: " . $hashed_password_from_db . "</p>";

    // 4. ใช้ password_verify() เพื่อเปรียบเทียบ
    echo "<p>4. กำลังใช้ password_verify() เพื่อเปรียบเทียบ...</p>";
    $is_password_correct = password_verify($test_password, $hashed_password_from_db);

    if ($is_password_correct) {
        echo "<h2 style='color:green;'>ผลลัพธ์: รหัสผ่านถูกต้อง! (TRUE)</h2>";
        echo "<p>การทำงานของโค้ดและฐานข้อมูลถูกต้องทั้งหมด ปัญหาอาจจะอยู่ที่ Session หรือการ Redirect</p>";
    } else {
        echo "<h2 style='color:red;'>ผลลัพธ์: รหัสผ่านไม่ถูกต้อง! (FALSE)</h2>";
        echo "<p>มีปัญหาระหว่าง PHP กับข้อมูลในฐานข้อมูล แม้ว่าข้อมูลจะดูถูกต้องก็ตาม</p>";
    }

} else {
    echo "<h2 style='color:red;'>ไม่พบผู้ใช้งาน '" . $test_emp_id . "' ในฐานข้อมูล</h2>";
}

$stmt->close();
$conn->close();
?>
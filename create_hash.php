<?php
// กำหนดรหัสผ่านที่ต้องการเข้ารหัส
$plain_password = '123456';

// ใช้ฟังก์ชัน password_hash() เพื่อสร้างรหัสที่เข้ารหัสแล้ว
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// แสดงผลลัพธ์
echo "<h1>รหัสผ่านที่เข้ารหัสแล้วสำหรับ '123456' คือ:</h1>";
echo "<p style='font-family: monospace; font-size: 1.2rem; color: blue; background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc; word-wrap: break-word;'>";
echo $hashed_password;
echo "</p>";
echo "<p>กรุณาคัดลอกข้อความสีน้ำเงินทั้งหมดนี้ไปใช้ในขั้นตอนต่อไป</p>";
?>
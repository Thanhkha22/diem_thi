<?php
// config.php - Kết nối đến cơ sở dữ liệu
$host = 'localhost';
$dbName = 'diem_thi';
$dbUser = 'root';
$dbPass = ''; // Thay đổi nếu có mật khẩu

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Kết nối CSDL thất bại: " . $e->getMessage();
    exit;
}
?>

<?php
session_start();
require_once 'config.php';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Lấy thông tin người dùng theo username
    $sql = "SELECT * FROM tbl_nguoi_dung WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Kiểm tra mật khẩu (ở ví dụ này so sánh trực tiếp; nên dùng password_verify() khi dùng password_hash())
        if ($user['password'] == $password) {
            // Lưu thông tin phiên đăng nhập
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            
            // Kiểm tra phân quyền và chuyển hướng tương ứng
            if ($user['role'] == 'admin') {
                header('Location: admin.php');
            } else {
                header('Location: search.php');
            }
            exit;
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Đăng nhập</h1>
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="post" action="">
            <label>Tên đăng nhập:</label>
            <input type="text" name="username" required><br><br>
            <label>Mật khẩu:</label>
            <input type="password" name="password" required><br><br>
            <input type="submit" name="login" value="Đăng nhập">
        </form>
        <p><a href="index.php">Quay về trang chủ</a></p>
    </div>
</body>
</html>

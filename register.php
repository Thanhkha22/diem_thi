<?php
session_start();
require_once 'config.php';

$error = "";
$success = "";

if (isset($_POST['register'])) {
    // Lấy dữ liệu từ form đăng ký
    $username         = trim($_POST['username']);
    $password         = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $fullName         = trim($_POST['full_name']);
    
    // Kiểm tra mật khẩu xác nhận
    if ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        // Kiểm tra xem tên đăng nhập đã tồn tại chưa
        $sql = "SELECT * FROM tbl_nguoi_dung WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['username' => $username]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Tên đăng nhập đã tồn tại, vui lòng chọn tên khác!";
        } else {
            // Hash mật khẩu trước khi lưu
            // Nếu bạn chưa dùng password_hash, hãy cân nhắc để bảo mật
            $hashed_password = $password;
            
            // Lấy thời gian hiện tại
            $currentTime = date('Y-m-d H:i:s');
            
            // Thêm mới tài khoản
            $sql = "INSERT INTO tbl_nguoi_dung (username, password, full_name, role, created_at, updated_at) 
                    VALUES (:username, :password, :full_name, :role, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            
            try {
                $stmt->execute([
                    'username'   => $username,
                    'password'   => $hashed_password,
                    'full_name'  => $fullName,
                    'role'       => 'user',
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime
                ]);
                $success = "Đăng ký thành công! Vui lòng <a href='index.php'>đăng nhập</a>.";
            } catch (PDOException $e) {
                $error = "Có lỗi xảy ra: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản</title>
    <style>
        /* Reset cơ bản */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        /* Nền gradient và căn giữa toàn trang */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #74ABE2, #5563DE);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Container chính */
        .container {
            width: 400px;
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }
        /* Tiêu đề */
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        /* Label và input */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #444;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        /* Nút submit */
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #5563DE;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #4453c9;
        }
        /* Thông báo */
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .message.error {
            color: red;
        }
        .message.success {
            color: green;
        }
        /* Liên kết */
        p {
            text-align: center;
            font-size: 14px;
        }
        a {
            color: #5563DE;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Đăng ký tài khoản</h1>
        <?php if (!empty($error)): ?>
            <p class="message error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p class="message success"><?php echo $success; ?></p>
        <?php endif; ?>
        
        <form method="post" action="">
            <label for="username">Tên đăng nhập:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Mật khẩu:</label>
            <input type="password" id="password" name="password" required>
            
            <label for="confirm_password">Xác nhận mật khẩu:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
            
            <label for="full_name">Họ và tên:</label>
            <input type="text" id="full_name" name="full_name" required>
            
            <input type="submit" name="register" value="Đăng ký">
        </form>
        
        <p style="margin-top: 20px;">Nếu bạn đã có tài khoản, hãy <a href="index.php">đăng nhập</a>.</p>
    </div>
</body>
</html>

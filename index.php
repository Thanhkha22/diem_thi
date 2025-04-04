<?php
session_start();
require_once 'config.php';

if(isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Lấy thông tin người dùng
    $sql = "SELECT * FROM tbl_nguoi_dung WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($user) {
        if($user['password'] == $password) { // Nên dùng password_verify() nếu mật khẩu được hash
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            header('Location: admin.php');
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
    <title>Trang chủ - Đăng nhập</title>
    <style>
        /* Reset các thuộc tính mặc định */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #74ABE2, #5563DE);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #fff;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
        .container {
            background: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        h2 {
            text-align: center;
            color: #444;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #5563DE;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type="submit"]:hover {
            background: #4453c9;
        }
        .register-link {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        .register-link a {
            color: #5563DE;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .register-link a:hover {
            color: #3c43a4;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
        .info {
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            text-align: center;
            font-size: 14px;
        }
        .info a {
            color: #5563DE;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .info a:hover {
            color: #3c43a4;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h1>Hệ thống Tra cứu Điểm thi</h1>
        <div class="container">
            <h2>Đăng nhập (Quản trị)</h2>
            <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="post" action="">
                <label for="username">Tên đăng nhập:</label>
                <input value="adminadmin" type="text" id="username" name="username" required>
                
                <label for="password">Mật khẩu:</label>
                <input value="123" type="password" id="password" name="password" required>
                
                <input type="submit" name="login" value="Đăng nhập">
                <div class="register-link">
                    <a href="register.php">Đăng ký</a>
                </div>
            </form>
        </div>
        <div class="info">
            <h2>Tra cứu điểm thi</h2>
            <p>Nếu bạn chỉ muốn tra cứu điểm thi, hãy truy cập trang <a href="search.php">Tra cứu điểm thi</a>.</p>
        </div>
    </div>
</body>
</html>

<?php
session_start();
require_once 'config.php';

// Chỉ cho phép admin truy cập trang này
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

$errorMessage = "";
$successMessage = "";

// Xử lý các hành động: thêm, cập nhật, xóa tài khoản
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        // Thêm mới tài khoản
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $fullName = trim($_POST['full_name']);
        $role     = trim($_POST['role']);
        
        if (empty($username) || empty($password) || empty($fullName) || empty($role)) {
            $errorMessage = "Vui lòng điền đầy đủ thông tin.";
        } else {
            // Kiểm tra xem tên đăng nhập đã tồn tại chưa
            $sql = "SELECT COUNT(*) as count FROM tbl_nguoi_dung WHERE username = :username";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['username' => $username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] > 0) {
                $errorMessage = "Tên đăng nhập đã tồn tại.";
            } else {
                // Mã hoá mật khẩu
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $currentTime = date('Y-m-d H:i:s');
                $sql = "INSERT INTO tbl_nguoi_dung (username, password, full_name, role, created_at, updated_at)
                        VALUES (:username, :password, :full_name, :role, :created_at, :updated_at)";
                $stmt = $conn->prepare($sql);
                try {
                    $stmt->execute([
                        'username'   => $username,
                        'password'   => $hashed_password,
                        'full_name'  => $fullName,
                        'role'       => $role,
                        'created_at' => $currentTime,
                        'updated_at' => $currentTime
                    ]);
                    $successMessage = "Tài khoản mới đã được thêm thành công.";
                } catch (PDOException $e) {
                    $errorMessage = "Lỗi: " . $e->getMessage();
                }
            }
        }
    } elseif ($action == 'update') {
        // Cập nhật tài khoản
        $userId   = $_POST['user_id'];
        $username = trim($_POST['username']);
        $fullName = trim($_POST['full_name']);
        $role     = trim($_POST['role']);
        $password = trim($_POST['password']); // Nếu nhập mật khẩu mới thì cập nhật
        
        if (empty($username) || empty($fullName) || empty($role)) {
            $errorMessage = "Vui lòng điền đầy đủ thông tin (trừ mật khẩu nếu không thay đổi).";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE tbl_nguoi_dung
                        SET username = :username,
                            password = :password,
                            full_name = :full_name,
                            role = :role,
                            updated_at = :updated_at
                        WHERE user_id = :user_id";
                $params = [
                    'username'   => $username,
                    'password'   => $hashed_password,
                    'full_name'  => $fullName,
                    'role'       => $role,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'user_id'    => $userId
                ];
            } else {
                $sql = "UPDATE tbl_nguoi_dung
                        SET username = :username,
                            full_name = :full_name,
                            role = :role,
                            updated_at = :updated_at
                        WHERE user_id = :user_id";
                $params = [
                    'username'   => $username,
                    'full_name'  => $fullName,
                    'role'       => $role,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'user_id'    => $userId
                ];
            }
            $stmt = $conn->prepare($sql);
            try {
                $stmt->execute($params);
                $successMessage = "Tài khoản đã được cập nhật thành công.";
            } catch (PDOException $e) {
                $errorMessage = "Lỗi: " . $e->getMessage();
            }
        }
    } elseif ($action == 'delete') {
        // Xóa tài khoản
        $userId = $_POST['user_id'];
        $sql = "DELETE FROM tbl_nguoi_dung WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        try {
            $stmt->execute(['user_id' => $userId]);
            $successMessage = "Tài khoản đã được xóa thành công.";
        } catch (PDOException $e) {
            $errorMessage = "Lỗi: " . $e->getMessage();
        }
    }
}

// Xử lý tìm kiếm theo ID nếu có
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';
if ($search_id !== '') {
    $sql = "SELECT * FROM tbl_nguoi_dung WHERE user_id = :search_id ORDER BY user_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['search_id' => $search_id]);
} else {
    $sql = "SELECT * FROM tbl_nguoi_dung ORDER BY user_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tài khoản</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Cấu hình chung */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }
        h1, h2 {
            text-align: center;
            color: #444;
        }
        .message {
            text-align: center;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .message.error {
            color: red;
        }
        .message.success {
            color: green;
        }
        .header-links {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-links a {
            margin: 0 10px;
            text-decoration: none;
            color: #5563DE;
            font-weight: bold;
        }
        .header-links a:hover {
            text-decoration: underline;
        }
        /* Form thêm mới */
        form.add-form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        form.add-form > div {
            flex: 1 1 200px;
            margin: 10px;
            padding-right: 8px;
        }
        form.add-form label {
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
        }
        form.add-form input[type="text"],
        form.add-form input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form.add-form select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form.add-form input[type="submit"] {
            background: #5563DE;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        form.add-form input[type="submit"]:hover {
            background: #4453c9;
        }
        /* Form tìm kiếm */
        .search-form {
            text-align: center;
            margin-bottom: 30px;
        }
        .search-form input[type="text"] {
            padding: 8px;
            width: 200px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-form input[type="submit"] {
            padding: 8px 12px;
            border: none;
            background: #5563DE;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .search-form input[type="submit"]:hover {
            background: #4453c9;
        }
        /* Bảng danh sách */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background: #f0f0f0;
        }
        table th, table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        table tbody tr:nth-child(odd) {
            background: #fafafa;
        }
        table tbody tr:hover {
            background: #f1f1f1;
        }
        /* Inline form cho nút cập nhật, xóa */
        form.inline-form {
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        form.inline-form button, 
        form.inline-form input[type="submit"] {
            padding: 6px 12px;
            margin: 4px 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        form.inline-form button {
            background: #FFA500;
            color: #fff;
        }
        form.inline-form input[type="submit"] {
            background: #e74c3c;
            color: #fff;
        }
        form.inline-form button:hover {
            background: #e69500;
        }
        form.inline-form input[type="submit"]:hover {
            background: #c0392b;
        }
        /* Form cập nhật ẩn */
        .update-form {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-top: 10px;
            border-radius: 4px;
            text-align: left;
        }
        .update-form .form-group {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .update-form .form-group label {
            width: 120px;
            margin-right: 10px;
            font-weight: bold;
        }
        .update-form .form-group input[type="text"],
        .update-form .form-group input[type="password"] {
            flex: 1;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .update-form .form-group select {
            flex: 1;
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .update-form .form-actions {
            text-align: left;
            margin-top: 10px;
        }
        .update-form .form-actions input[type="submit"],
        .update-form .form-actions button {
            padding: 6px 12px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .update-form .form-actions input[type="submit"] {
            background: #5563DE;
            color: #fff;
        }
        .update-form .form-actions button {
            background: #ccc;
            color: #333;
        }
    </style>
    <script>
        function toggleUpdateForm(id) {
            var formRow = document.getElementById("updateForm_" + id);
            if (formRow.style.display === "none" || formRow.style.display === "") {
                formRow.style.display = "table-row";
            } else {
                formRow.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Quản lý tài khoản</h1>
        <div class="header-links">
            <a href="admin.php">Quay lại trang quản trị</a> | 
            <a href="logout.php">Đăng xuất</a>
        </div>
        
        <?php if (!empty($errorMessage)): ?>
            <p class="message error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <p class="message success"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <h2>Thêm mới tài khoản</h2>
        <form method="post" action="" class="add-form">
            <input type="hidden" name="action" value="add">
            <div>
                <label>Tên đăng nhập:</label>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Mật khẩu:</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label>Họ và tên:</label>
                <input type="text" name="full_name" required>
            </div>
            <div>
                <label>Vai trò:</label>
                <select name="role" required>
                    <option value="user" selected>User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div style="align-self: flex-end;">
                <input type="submit" value="Thêm mới">
            </div>
        </form>
        
        <!-- Form tìm kiếm theo ID đặt dưới form thêm mới -->
        <form method="get" action="" class="search-form">
            <label>Tìm kiếm theo ID:</label>
            <input type="text" name="search_id" placeholder="Nhập ID tài khoản" value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>">
            <input type="submit" value="Tìm kiếm">
            <?php if(isset($_GET['search_id']) && trim($_GET['search_id']) !== ''): ?>
                <a href="tk.php" style="margin-left: 10px;">Hiển thị tất cả</a>
            <?php endif; ?>
        </form>

        <h2>Danh sách tài khoản</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ và tên</th>
                    <th>Vai trò</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($accounts)): ?>
                    <?php foreach($accounts as $account): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($account['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($account['username']); ?></td>
                            <td><?php echo htmlspecialchars($account['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($account['role']); ?></td>
                            <td><?php echo htmlspecialchars($account['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($account['updated_at']); ?></td>
                            <td>
                                <form method="post" action="" class="inline-form">
                                    <button type="button" onclick="toggleUpdateForm(<?php echo $account['user_id']; ?>)">Cập nhật</button>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $account['user_id']; ?>">
                                    <input type="submit" value="Xóa" onclick="return confirm('Bạn có chắc muốn xóa?');">
                                </form>
                            </td>
                        </tr>
                        <!-- Form cập nhật ẩn -->
                        <tr id="updateForm_<?php echo $account['user_id']; ?>" style="display: none;">
                            <td colspan="7">
                                <form method="post" action="" class="update-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="user_id" value="<?php echo $account['user_id']; ?>">
                                    <div class="form-group">
                                        <label>Tên đăng nhập:</label>
                                        <input type="text" name="username" value="<?php echo htmlspecialchars($account['username']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Mật khẩu:</label>
                                        <input type="password" name="password" placeholder="Để trống nếu không thay đổi">
                                    </div>
                                    <div class="form-group">
                                        <label>Họ và tên:</label>
                                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($account['full_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Vai trò:</label>
                                        <select name="role" required>
                                            <option value="user" <?php echo ($account['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                            <option value="admin" <?php echo ($account['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="form-actions">
                                        <input type="submit" value="Cập nhật">
                                        <button type="button" onclick="toggleUpdateForm(<?php echo $account['user_id']; ?>)">Hủy</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Không có tài khoản nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
session_start();
require_once 'config.php';

$errorMessage = ""; // Biến lưu thông báo lỗi

// Chỉ cho phép admin truy cập trang này
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit;
}

// Xử lý các thao tác: Thêm, Cập nhật, Xóa
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        // Lấy dữ liệu từ form thêm mới
        $sbd      = trim($_POST['so_bao_danh']);
        $fullName = trim($_POST['full_name']);
        $diaChi   = trim($_POST['dia_chi']);
        
        $toan     = floatval($_POST['diem_toan']);
        $van      = floatval($_POST['diem_van']);
        $anh      = floatval($_POST['diem_anh']);
        $ly       = floatval($_POST['diem_ly']);
        $hoa      = floatval($_POST['diem_hoa']);
        $sinh     = floatval($_POST['diem_sinh']);
        $tong     = $toan + $van + $anh + $ly + $hoa + $sinh;
        
        // Kiểm tra nếu số báo danh đã tồn tại
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_thi_sinh WHERE so_bao_danh = :sbd");
        $checkStmt->execute(['sbd' => $sbd]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $errorMessage = "Số báo danh $sbd đã tồn tại. Vui lòng kiểm tra lại!";
        } else {
            // Nếu không tồn tại, thêm mới bản ghi
            $sql = "INSERT INTO tbl_thi_sinh (so_bao_danh, full_name, dia_chi, diem_toan, diem_van, diem_anh, diem_ly, diem_hoa, diem_sinh, tong_diem)
                    VALUES (:sbd, :fname, :dia_chi, :toan, :van, :anh, :ly, :hoa, :sinh, :tong)";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    'sbd'    => $sbd,
                    'fname'  => $fullName,
                    'dia_chi'=> $diaChi,
                    'toan'   => $toan,
                    'van'    => $van,
                    'anh'    => $anh,
                    'ly'     => $ly,
                    'hoa'    => $hoa,
                    'sinh'   => $sinh,
                    'tong'   => $tong
                ]);
            } catch (PDOException $e) {
                $errorMessage = "Lỗi: " . $e->getMessage();
            }
        }
    } elseif ($action == 'update') {
        // Cập nhật thông tin thí sinh
        $id       = $_POST['thi_sinh_id'];
        $sbd      = trim($_POST['so_bao_danh']);
        $fullName = trim($_POST['full_name']);
        $diaChi   = trim($_POST['dia_chi']);
        
        $toan     = floatval($_POST['diem_toan']);
        $van      = floatval($_POST['diem_van']);
        $anh      = floatval($_POST['diem_anh']);
        $ly       = floatval($_POST['diem_ly']);
        $hoa      = floatval($_POST['diem_hoa']);
        $sinh     = floatval($_POST['diem_sinh']);
        $tong     = $toan + $van + $anh + $ly + $hoa + $sinh;
        
        $sql = "UPDATE tbl_thi_sinh
                SET so_bao_danh = :sbd,
                    full_name   = :fname,
                    dia_chi     = :dia_chi,
                    diem_toan   = :toan,
                    diem_van    = :van,
                    diem_anh    = :anh,
                    diem_ly     = :ly,
                    diem_hoa    = :hoa,
                    diem_sinh   = :sinh,
                    tong_diem   = :tong
                WHERE thi_sinh_id = :id";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'sbd'     => $sbd,
                'fname'   => $fullName,
                'dia_chi' => $diaChi,
                'toan'    => $toan,
                'van'     => $van,
                'anh'     => $anh,
                'ly'      => $ly,
                'hoa'     => $hoa,
                'sinh'    => $sinh,
                'tong'    => $tong,
                'id'      => $id
            ]);
        } catch (PDOException $e) {
            $errorMessage = "Lỗi: " . $e->getMessage();
        }
    } elseif ($action == 'delete') {
        //----------------------------------------- Xóa thí sinh
        $id = $_POST['thi_sinh_id'];
        $sql = "DELETE FROM tbl_thi_sinh WHERE thi_sinh_id = :id";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            $errorMessage = "Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách thí sinh từ CSDL, sắp xếp theo ID giảm dần
// Kiểm tra nếu có tìm kiếm theo ID
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';

if ($search_id !== '') {
    $sql = "SELECT * FROM tbl_thi_sinh WHERE thi_sinh_id = :search_id ORDER BY thi_sinh_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['search_id' => $search_id]);
} else {
    $sql = "SELECT * FROM tbl_thi_sinh ORDER BY thi_sinh_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$thiSinhs = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang quản trị</title>
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
            max-width: 1100px;
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
        p {
            text-align: center;
        }
        a {
            color: #5563DE;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        /* Header liên kết */
        .header-links {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-links a {
            margin: 0 10px;
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
        /* Form cập nhật ẩn - Hiển thị theo chiều ngang */
        .update-form {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-top: 10px;
            border-radius: 4px;
        }
        .update-form .update-form-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .update-form .form-group {
            margin-right: 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .update-form .form-group label {
            margin-right: 5px;
            font-weight: bold;
        }
        .update-form .form-group input {
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
        <h1>Quản trị hệ thống</h1>
        <p>Xin chào, <?php echo $_SESSION['full_name']; ?> (<?php echo $_SESSION['username']; ?>)</p>
        <div class="header-links">
            <a href="logout.php">Đăng xuất</a> | 
            <a href="index.php">Trang chủ</a> | 
            <a href="tk.php">Tài khoản</a>
        </div>
        
        <?php if (!empty($errorMessage)): ?>
            <p class="message error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <?php if (!empty($successMessage)): ?>
            <p class="message success"><?php echo $successMessage; ?></p>
        <?php endif; ?>
<!-- Thêm thí sinhsinh -->
        <h2>Thêm mới thí sinh</h2>
        <form method="post" action="" class="add-form">
            <input type="hidden" name="action" value="add">
            <div>
                <label>Số báo danh:</label>
                <input type="text" name="so_bao_danh" required>
            </div>
            <div>
                <label>Họ và tên:</label>
                <input type="text" name="full_name" required>
            </div>
            <div>
                <label>Địa chỉ:</label>
                <input type="text" name="dia_chi">
            </div>
            <div>
                <label>Điểm Toán:</label>
                <input type="text" name="diem_toan">
            </div>
            <div>
                <label>Điểm Văn:</label>
                <input type="text" name="diem_van">
            </div>
            <div>
                <label>Điểm Anh:</label>
                <input type="text" name="diem_anh">
            </div>
            <div>
                <label>Điểm Lý:</label>
                <input type="text" name="diem_ly">
            </div>
            <div>
                <label>Điểm Hóa:</label>
                <input type="text" name="diem_hoa">
            </div>
            <div>
                <label>Điểm Sinh:</label>
                <input style="width:50%" type="text" name="diem_sinh">
            </div>
            <div style="align-self: flex-end;">
                <input type="submit" value="Thêm mới">
            </div>
        </form>

        <!-- Form tìm kiếm theo ID đặt dưới phần thêm mới -->
        <form method="get" action="" class="search-form">
            <label>Tìm kiếm theo ID:</label>
            <input type="text" name="search_id" placeholder="Nhập ID tài khoản" value="<?php echo isset($_GET['search_id']) ? htmlspecialchars($_GET['search_id']) : ''; ?>">
            <input type="submit" value="Tìm kiếm">
            <?php if(isset($_GET['search_id']) && trim($_GET['search_id']) !== ''): ?>
                <a href="tk.php" style="margin-left: 10px;">Hiển thị tất cả</a>
            <?php endif; ?>
        </form>

        <h2>Danh sách thí sinh</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Số báo danh</th>
                    <th>Họ và tên</th>
                    <th>Địa chỉ</th>
                    <th>Điểm Toán</th>
                    <th>Điểm Văn</th>
                    <th>Điểm Anh</th>
                    <th>Điểm Lý</th>
                    <th>Điểm Hóa</th>
                    <th>Điểm Sinh</th>
                    <th>Tổng Điểm</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <!-- hiện dữ liệu thí sinh... -->
                <?php if(!empty($thiSinhs)): ?>
                    <?php foreach($thiSinhs as $ts): ?>
                        <tr>
                            <td><?php echo $ts['thi_sinh_id']; ?></td>
                            <td><?php echo $ts['so_bao_danh']; ?></td>
                            <td><?php echo $ts['full_name']; ?></td>
                            <td><?php echo $ts['dia_chi']; ?></td>
                            <td><?php echo $ts['diem_toan']; ?></td>
                            <td><?php echo $ts['diem_van']; ?></td>
                            <td><?php echo $ts['diem_anh']; ?></td>
                            <td><?php echo $ts['diem_ly']; ?></td>
                            <td><?php echo $ts['diem_hoa']; ?></td>
                            <td><?php echo $ts['diem_sinh']; ?></td>
                            <td><?php echo $ts['tong_diem']; ?></td>
                            <td>
                                <!-- cập nhật và xóa Danh sách thí sinh -->

                                <form method="post" action="" class="inline-form">
                                    <button type="button" onclick="toggleUpdateForm(<?php echo $ts['thi_sinh_id']; ?>)">Cập nhật</button>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="thi_sinh_id" value="<?php echo $ts['thi_sinh_id']; ?>">
                                    <input type="submit" value="Xóa" onclick="return confirm('Bạn có chắc muốn xóa?');">
                                </form>
                            </td>
                        </tr>
                        <!-- Form cập nhật ẩn: hiển thị theo chiều ngang -->
                        <tr id="updateForm_<?php echo $ts['thi_sinh_id']; ?>" style="display: none;">
                            <td colspan="12">
                                <form method="post" action="" class="update-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="thi_sinh_id" value="<?php echo $ts['thi_sinh_id']; ?>">
                                    <div class="update-form-content">
                                        <div class="form-group">
                                            <label>SBD:</label>
                                            <input type="text" name="so_bao_danh" value="<?php echo $ts['so_bao_danh']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Họ tên:</label>
                                            <input type="text" name="full_name" value="<?php echo $ts['full_name']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Địa chỉ:</label>
                                            <input type="text" name="dia_chi" value="<?php echo $ts['dia_chi']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Toán:</label>
                                            <input type="text" name="diem_toan" value="<?php echo $ts['diem_toan']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Văn:</label>
                                            <input type="text" name="diem_van" value="<?php echo $ts['diem_van']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Anh:</label>
                                            <input type="text" name="diem_anh" value="<?php echo $ts['diem_anh']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Lý:</label>
                                            <input type="text" name="diem_ly" value="<?php echo $ts['diem_ly']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Hóa:</label>
                                            <input type="text" name="diem_hoa" value="<?php echo $ts['diem_hoa']; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Sinh:</label>
                                            <input type="text" name="diem_sinh" value="<?php echo $ts['diem_sinh']; ?>">
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <input type="submit" value="Cập nhật">
                                        <button type="button" onclick="toggleUpdateForm(<?php echo $ts['thi_sinh_id']; ?>)">Hủy</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12">Không có tài khoản nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <style>
        /* CSS cho update form hiển thị theo chiều ngang */
        .update-form {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-top: 10px;
            border-radius: 4px;
        }
        .update-form .update-form-content {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .update-form .form-group {
            margin-right: 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .update-form .form-group label {
            margin-right: 5px;
            font-weight: bold;
        }
        .update-form .form-group input {
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
</body>
</html>

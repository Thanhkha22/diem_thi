<?php
session_start();
require_once 'config.php';

$result = "";

if (isset($_POST['search'])) {
    // Lấy số báo danh và mã captcha người dùng nhập
    $soBaoDanh = trim($_POST['so_bao_danh']);
    $captcha   = trim($_POST['captcha']);

    // Kiểm tra captcha: so sánh giá trị người dùng nhập với giá trị lưu trong session
    if (isset($_SESSION['captcha']) && $captcha === $_SESSION['captcha']) {
        // Nếu captcha khớp, tiến hành tra cứu trong CSDL
        $sql = "SELECT * FROM tbl_thi_sinh WHERE so_bao_danh = :sbd";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['sbd' => $soBaoDanh]);
        $thiSinh = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($thiSinh) {
            $result .= "<p><strong>Kết quả tra cứu</strong></p>";
            $result .= "● Họ và tên: " . $thiSinh['full_name'] . "<br>";
            $result .= "● Địa Chỉ: " . $thiSinh['dia_chi'] . "<br>";
            $result .= "● Điểm Toán: " . $thiSinh['diem_toan'] . "<br>";
            $result .= "● Điểm Văn: " . $thiSinh['diem_van'] . "<br>";
            $result .= "● Điểm Anh: " . $thiSinh['diem_anh'] . "<br>";
            $result .= "● Điểm Lý: " . $thiSinh['diem_ly'] . "<br>";
            $result .= "● Điểm Hóa: " . $thiSinh['diem_hoa'] . "<br>";
            $result .= "● Điểm Sinh: " . $thiSinh['diem_sinh'] . "<br>";
            $result .= "Tổng Điểm: " . $thiSinh['tong_diem'] . "<br>";
        } else {
            $result = "<p style='color:red;'>Không tìm thấy thí sinh với SBD: $soBaoDanh</p>";
        }
    } else {
        $result = "<p style='color:red;'>Mã xác nhận không đúng!</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tra cứu điểm thi</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .search-container h1 {
            text-align: center;
            color: #007BFF;
            margin-bottom: 20px;
        }
        .search-container form label {
            font-weight: bold;
        }
        .search-container form input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-container form input[type="submit"] {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-container form input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .captcha-container {
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        .refresh-button {
            cursor: pointer;
            font-size: 24px; /* Tăng kích thước font */
            margin-left: 5px;
        }
    </style>


    <script>
        // Hàm reload captcha: tải lại ảnh captcha để tránh cache
        function reloadCaptcha() {
            var captchaImage = document.getElementById('captchaImage');
            captchaImage.src = 'http://localhost:90/diemthi/captch.php?rand=' + Math.random();
        }
    </script>
</head>
<body>
    <div class="search-container">
        <h1>Tra cứu điểm thi THPT Quốc gia</h1>
        <form method="post" action="">
            <label for="so_bao_danh">Số báo danh:</label>
            <input type="text" name="so_bao_danh" id="so_bao_danh" required>
            <br><br>
            <label for="captcha">Mã xác nhận:</label>
            <input type="text" name="captcha" id="captcha" required>
            <!-- Hiển thị ảnh captcha cùng nút refresh -->
            <span class="captcha-container">
            <img id="captchaImage" src="http://localhost:90/diemthi/captch.php?rand=<?php echo rand(); ?>" alt="Captcha Image" style="width:150px; height:60px;">
            <span class="refresh-button" onclick="reloadCaptcha()">↻</span>
           </span>

            <br><br>
            <input type="submit" name="search" value="Tra cứu">
        </form>
        <div style="margin-top:20px;">
            <?php echo $result; ?>
        </div>
        <h2><a href="index.php">Quay về trang chủ</a><h2>
    </div>
</body>
</html>

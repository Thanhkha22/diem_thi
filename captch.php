<?php
session_start();

// Tạo chuỗi captcha ngẫu nhiên (5 ký tự, gồm chữ và số)
$captcha_text = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ123456789"), 0, 5);
$_SESSION['captcha'] = $captcha_text;

// Thiết lập header trả về ảnh PNG
header('Content-Type: image/png');

// Kích thước ảnh captcha
$width = 90;
$height = 30;
$image = imagecreate($width, $height);

// Màu nền và màu chữ
$background_color = imagecolorallocate($image, 255, 255, 255); // Trắng
$text_color       = imagecolorallocate($image, 0, 0, 0);       // Đen

// Vẽ chuỗi captcha lên ảnh
imagestring($image, 5, 10, 10, $captcha_text, $text_color);

/* 
 * 1) Thêm các đường nhiễu
 *    Vẽ vài đường ngang/ngẫu nhiên với màu ngẫu nhiên
 */
for ($i = 0; $i < 5; $i++) {
    $line_color = imagecolorallocate($image, rand(50,150), rand(50,150), rand(50,150));
    imageline(
        $image,
        0,
        rand(0, $height),    // y1 ngẫu nhiên
        $width,
        rand(0, $height),    // y2 ngẫu nhiên
        $line_color
    );
}

/*
 * 2) Thêm các chấm nhiễu (dot)
 *    Rải nhiều chấm (pixel) ngẫu nhiên
 */
for ($i = 0; $i < 200; $i++) {
    $dot_color = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(100, 255));
    imagesetpixel($image, rand(0, $width), rand(0, $height), $dot_color);
}

// Xuất ảnh và hủy ảnh
imagepng($image);
imagedestroy($image);
?>

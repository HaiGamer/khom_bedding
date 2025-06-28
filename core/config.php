<?php
// Bật báo cáo lỗi để dễ dàng gỡ lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CẤU HÌNH CƠ SỞ DỮ LIỆU ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Thay bằng username của bạn
define('DB_PASS', '');     // Thay bằng password của bạn
define('DB_NAME', 'khom_bedding_db');

// --- CẤU HÌNH ĐƯỜNG DẪN ---
define('ROOT_PATH', dirname(__DIR__) . '/');
define('BASE_URL', 'http://localhost/'); // Thay bằng URL của bạn

// --- KẾT NỐI CSDL BẰNG PDO (an toàn hơn mysqli) ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    // Thiết lập chế độ báo lỗi PDO thành Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Thiết lập chế độ trả về dữ liệu mặc định là mảng kết hợp (associative array)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}

// THÊM CÁC DÒNG HCAPTCHA MỚI VÀ DÁN KHÓA CỦA BẠN VÀO
define('HCAPTCHA_SITE_KEY', '32f4ae2a-3f62-4624-8b84-d4ca3cc6072e');
define('HCAPTCHA_SECRET_KEY', 'ES_54b52b18b0dd4c86becd86357ad8b706');


// (Chúng ta sẽ thêm các hàm chung vào file functions.php sau)
// require_once ROOT_PATH . 'core/functions.php';

// Nạp các hàm chung vào hệ thống
require_once ROOT_PATH . 'core/functions.php';
?>
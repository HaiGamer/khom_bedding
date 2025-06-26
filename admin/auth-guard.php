<?php
// Bắt đầu session để truy cập biến $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nếu không tồn tại session của admin, đá về trang đăng nhập
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
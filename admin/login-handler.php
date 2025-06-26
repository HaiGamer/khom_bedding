<?php
require_once __DIR__ . '/../core/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Invalid request'); }

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // KIỂM TRA MẬT KHẨU VÀ VAI TRÒ (ROLE)
    if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
        // Đăng nhập thành công, lưu session cho admin
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['full_name'];
        header('Location: index.php'); // Chuyển đến trang dashboard
        exit();
    } else {
        // Sai thông tin hoặc không phải admin
        header('Location: login.php?error=1');
        exit();
    }
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
<?php
require_once __DIR__ . '/core/config.php';

$response = ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Vui lòng nhập một địa chỉ email hợp lệ.';
    } else {
        try {
            // Kiểm tra xem email đã tồn tại chưa
            $stmt_check = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
            $stmt_check->execute([$email]);

            if ($stmt_check->fetch()) {
                $response['success'] = true; // Coi như thành công nếu đã đăng ký rồi
                $response['message'] = 'Cảm ơn bạn, email này đã được đăng ký trước đó!';
            } else {
                // Nếu chưa, thêm vào CSDL
                $stmt_insert = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
                $stmt_insert->execute([$email]);
                $response['success'] = true;
                $response['message'] = 'Đăng ký nhận tin thành công. Cảm ơn bạn!';
            }
        } catch (PDOException $e) {
            // Ghi log lỗi thay vì die() để không làm lộ thông tin CSDL
            error_log('Subscribe error: ' . $e->getMessage());
            $response['message'] = 'Lỗi hệ thống, không thể đăng ký tại thời điểm này.';
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
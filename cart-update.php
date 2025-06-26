<?php
require_once __DIR__ . '/core/config.php';

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $variant_id = isset($data['variant_id']) ? (int)$data['variant_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;

    if ($variant_id > 0 && isset($_SESSION['cart'][$variant_id])) {
        if ($quantity > 0) {
            // Cập nhật số lượng
            $_SESSION['cart'][$variant_id] = $quantity;
            $response['message'] = 'Cập nhật giỏ hàng thành công.';
        } else {
            // Nếu số lượng là 0 hoặc nhỏ hơn, xóa sản phẩm
            unset($_SESSION['cart'][$variant_id]);
            $response['message'] = 'Xóa sản phẩm thành công.';
        }
        $response['success'] = true;
    } else {
        $response['message'] = 'Sản phẩm không có trong giỏ hàng.';
    }
}

// Trả về số lượng sản phẩm mới trong giỏ hàng để cập nhật icon header
$response['cart_count'] = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

header('Content-Type: application/json');
echo json_encode($response);
<?php
// Luôn bắt đầu session ở đầu file để làm việc với giỏ hàng
require_once __DIR__ . '/core/config.php';

// Mặc định phản hồi là lỗi
$response = [
    'success' => false,
    'message' => 'Có lỗi xảy ra, vui lòng thử lại.',
    'cart_count' => 0
];

try {
    // Chỉ chấp nhận phương thức POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không hợp lệ.');
    }

    // Lấy dữ liệu từ body của request
    $data = json_decode(file_get_contents('php://input'), true);

    $variant_id = isset($data['variant_id']) ? (int)$data['variant_id'] : 0;
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;

    // Kiểm tra dữ liệu đầu vào
    if ($variant_id <= 0 || $quantity <= 0) {
        throw new Exception('Dữ liệu không hợp lệ.');
    }

    // Khởi tạo giỏ hàng nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    if (isset($_SESSION['cart'][$variant_id])) {
        // Nếu có rồi thì cập nhật số lượng
        $_SESSION['cart'][$variant_id] += $quantity;
    } else {
        // Nếu chưa có thì thêm mới
        $_SESSION['cart'][$variant_id] = $quantity;
    }

    // Chuẩn bị phản hồi thành công
    $response['success'] = true;
    $response['message'] = 'Sản phẩm đã được thêm vào giỏ hàng!';
    // Tính tổng số loại sản phẩm trong giỏ hàng
    $response['cart_count'] = count($_SESSION['cart']);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Trả về phản hồi dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($response);
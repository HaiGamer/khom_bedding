<?php
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';

$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $variant_id = isset($data['variant_id']) ? (int)$data['variant_id'] : 0;
    $stock_quantity = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : -1;

    if ($variant_id > 0 && $stock_quantity >= 0) {
        try {
            $stmt = $pdo->prepare("UPDATE product_variants SET stock_quantity = ? WHERE id = ?");
            $stmt->execute([$stock_quantity, $variant_id]);
            $response = ['success' => true, 'message' => 'Cập nhật tồn kho thành công.'];
        } catch (PDOException $e) {
            $response['message'] = 'Lỗi CSDL: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Dữ liệu không hợp lệ.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
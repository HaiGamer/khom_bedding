<?php
require_once __DIR__ . '/core/config.php';

$results = [];
$term = $_GET['term'] ?? '';

if (strlen($term) >= 2) { // Chỉ tìm kiếm khi người dùng gõ ít nhất 2 ký tự
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.name, 
                p.slug,
                (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_featured = TRUE LIMIT 1) as image_url,
                (SELECT pv.price FROM product_variants pv WHERE pv.product_id = p.id AND pv.is_default = TRUE) as price
            FROM products p
            WHERE p.name LIKE ?
            LIMIT 5 -- Giới hạn 5 kết quả để không làm chậm
        ");
        $stmt->execute(["%$term%"]);
        $results = $stmt->fetchAll();

    } catch (PDOException $e) {
        // Có thể ghi log lỗi ở đây nhưng không trả lỗi về cho người dùng
        $results = [];
    }
}

// Trả kết quả về dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($results);
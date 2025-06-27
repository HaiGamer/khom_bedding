<?php
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';

$results = [];
$term = $_GET['term'] ?? '';

if (strlen($term) >= 2) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                pv.id, pv.sku, pv.price, pv.stock_quantity,
                p.name AS product_name,
                GROUP_CONCAT(av.value ORDER BY a.id SEPARATOR ' - ') AS variant_attributes
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            LEFT JOIN variant_values vv ON pv.id = vv.variant_id
            LEFT JOIN attribute_values av ON vv.attribute_value_id = av.id
            LEFT JOIN attributes a ON av.attribute_id = a.id
            WHERE p.name LIKE ? OR pv.sku LIKE ?
            GROUP BY pv.id
            LIMIT 10
        ");
        $stmt->execute(["%$term%", "%$term%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $results = []; }
}

header('Content-Type: application/json');
echo json_encode($results);
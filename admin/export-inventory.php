<?php
// Nạp các file cần thiết nhưng không include header/footer
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';

try {
    // 1. Chạy lại câu lệnh SQL để lấy toàn bộ dữ liệu tồn kho
    $stmt = $pdo->prepare("
        SELECT 
            p.name AS product_name,
            GROUP_CONCAT(av.value ORDER BY a.id SEPARATOR ' - ') AS variant_attributes,
            pv.sku, 
            pv.stock_quantity,
            COALESCE(SUM(oi.quantity), 0) AS total_sold
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        LEFT JOIN variant_values vv ON pv.id = vv.variant_id
        LEFT JOIN attribute_values av ON vv.attribute_value_id = av.id
        LEFT JOIN attributes a ON av.attribute_id = a.id
        LEFT JOIN order_items oi ON pv.id = oi.variant_id
        GROUP BY pv.id, p.name
        ORDER BY p.name, pv.id
    ");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Thiết lập HTTP Headers để trình duyệt hiểu đây là một file Excel
    $filename = "bao-cao-ton-kho_" . date('Y-m-d') . ".xls";
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    // 3. Tạo tiêu đề cho file Excel
    $is_first_row = true;
    if (!empty($data)) {
        // Lấy tên các cột từ dòng dữ liệu đầu tiên
        $column_headers = array_keys($data[0]);
        // Xuất dòng tiêu đề (có thể tùy chỉnh lại tên cột cho đẹp hơn)
        echo implode("\t", ['Tên Sản Phẩm', 'Phiên Bản', 'SKU', 'Tồn Kho', 'Đã Bán']) . "\n";

        // 4. Lặp qua dữ liệu và xuất ra từng dòng
        foreach ($data as $row) {
            // Làm sạch dữ liệu để tránh lỗi
            array_walk($row, function(&$value) {
                $value = preg_replace("/\t/", "\\t", $value);
                $value = preg_replace("/\r?\n/", "\\n", $value);
                if(strstr($value, '"')) $value = '"' . str_replace('"', '""', $value) . '"';
            });
            echo implode("\t", array_values($row)) . "\n";
        }
    } else {
        echo "Không có dữ liệu để xuất.";
    }

    exit(); // Dừng kịch bản sau khi đã xuất file

} catch (PDOException $e) {
    die("Lỗi khi xuất dữ liệu: " . $e->getMessage());
}
?>
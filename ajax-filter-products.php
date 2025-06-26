<?php
require_once __DIR__ . '/core/config.php';

// --- KHỞI TẠO ---
$base_sql = "
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = TRUE
";
$where_clauses = ["pv.is_default = TRUE"];
$params = [];

// --- XỬ LÝ SẮP XẾP ---
$sort_option = $_GET['sort'] ?? 'newest';
$order_by_clause = 'ORDER BY p.created_at DESC';
switch ($sort_option) {
    case 'price_asc': $order_by_clause = 'ORDER BY pv.price ASC'; break;
    case 'price_desc': $order_by_clause = 'ORDER BY pv.price DESC'; break;
    case 'name_asc': $order_by_clause = 'ORDER BY p.name ASC'; break;
}

// --- XỬ LÝ LỌC GIÁ ---
$price_range = $_GET['price_range'] ?? 'all';
if ($price_range !== 'all') {
    switch ($price_range) {
        case 'range1':
            $where_clauses[] = "pv.price < ?";
            $params[] = 500000;
            break;
        case 'range2':
            $where_clauses[] = "pv.price BETWEEN ? AND ?";
            $params[] = 500000;
            $params[] = 1000000;
            break;
        case 'range3':
            $where_clauses[] = "pv.price > ?";
            $params[] = 1000000;
            break;
    }
}


// === LOGIC MỚI: XỬ LÝ LỌC DANH MỤC ===
$category_slug = $_GET['category'] ?? '';
if (!empty($category_slug)) {
    $stmt_cat = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt_cat->execute([$category_slug]);
    $category = $stmt_cat->fetch();
    if ($category) {
        $where_clauses[] = "p.category_id = ?";
        $params[] = $category['id'];
    }
}


// --- HÀM TIỆN ÍCH ĐỂ XỬ LÝ LỌC THUỘC TÍNH ---
// Hàm này giúp chúng ta không phải lặp lại code cho Kích thước và Chất liệu
function addAttributeFilter(string $attribute_name, string $param_key, array &$where_clauses, array &$params) {
    if (!empty($_GET[$param_key]) && is_array($_GET[$param_key])) {
        $values = $_GET[$param_key];
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        
        // Tìm tất cả các sản phẩm có ít nhất một phiên bản khớp với một trong các giá trị được chọn
        $where_clauses[] = "p.id IN (
            SELECT DISTINCT p_inner.id 
            FROM products p_inner
            JOIN product_variants pv_filter ON p_inner.id = pv_filter.product_id
            JOIN variant_values vv_filter ON pv_filter.id = vv_filter.variant_id
            JOIN attribute_values av_filter ON vv_filter.attribute_value_id = av_filter.id
            JOIN attributes a_filter ON av_filter.attribute_id = a_filter.id
            WHERE a_filter.name = ? AND av_filter.value IN ($placeholders)
        )";
        $params[] = $attribute_name; // Thêm tên thuộc tính vào params
        $params = array_merge($params, $values); // Thêm các giá trị thuộc tính vào params
    }
}

// --- ÁP DỤNG LỌC THUỘC TÍNH ---
addAttributeFilter('Kích thước', 'size', $where_clauses, $params);
addAttributeFilter('Chất liệu', 'material', $where_clauses, $params);


// --- DỰNG CÂU LỆNH SQL CUỐI CÙNG ---
$where_sql = implode(' AND ', $where_clauses);
$sql = "
    SELECT DISTINCT p.name, p.slug, pv.price, pi.image_url
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = TRUE
    WHERE $where_sql
    $order_by_clause
";

// --- THỰC THI VÀ TRẢ KẾT QUẢ ---
$stmt_products = $pdo->prepare($sql);
$stmt_products->execute($params);
$products = $stmt_products->fetchAll();

if (!empty($products)) {
    foreach ($products as $product) {
        include __DIR__ . '/templates/product-card.php';
    }
} else {
    echo '<p class="text-center col-12 my-5">Không tìm thấy sản phẩm nào phù hợp với lựa chọn của bạn.</p>';
}

?>
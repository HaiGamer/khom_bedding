<?php
require_once __DIR__ . '/core/config.php';

// --- PHẦN LOGIC XỬ LÝ LỌC, SẮP XẾP, PHÂN TRANG ---
try {
    // 1. Cấu hình phân trang
    $results_per_page = 2;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) { $page = 1; }
    $offset = ($page - 1) * $results_per_page;

    // 2. Xây dựng các điều kiện WHERE
    $where_clauses = ["pv.is_default = TRUE"];
    $params = [];

    // Lọc theo danh mục
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

    // Lọc theo giá
    $price_range = $_GET['price_range'] ?? 'all';
    if ($price_range !== 'all') {
        switch ($price_range) {
            case 'range1': $where_clauses[] = "pv.price < 500000"; break;
            case 'range2': $where_clauses[] = "pv.price BETWEEN 500000 AND 1000000"; break;
            case 'range3': $where_clauses[] = "pv.price > 1000000"; break;
        }
    }

    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

    // 3. Xử lý sắp xếp
    $sort_option = $_GET['sort'] ?? 'newest';
    $order_by_clause = 'ORDER BY p.created_at DESC';
    switch ($sort_option) {
        case 'price_asc': $order_by_clause = 'ORDER BY pv.price ASC'; break;
        case 'price_desc': $order_by_clause = 'ORDER BY pv.price DESC'; break;
        case 'name_asc': $order_by_clause = 'ORDER BY p.name ASC'; break;
    }

    // 4. Đếm tổng số sản phẩm với bộ lọc hiện tại
    $count_sql = "SELECT COUNT(DISTINCT p.id) FROM products p JOIN product_variants pv ON p.id = pv.product_id " . $where_sql;
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute($params);
    $total_results = $stmt_count->fetchColumn();
    $total_pages = ceil($total_results / $results_per_page);

    // 5. Lấy sản phẩm cho trang hiện tại
    $products_sql = "
        SELECT p.name, p.slug, pv.price, pv.original_price, 
               (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_featured = TRUE LIMIT 1) as image_url
        FROM products p
        JOIN product_variants pv ON p.id = pv.product_id
        $where_sql
        GROUP BY p.id
        $order_by_clause
        LIMIT $results_per_page OFFSET $offset
    ";
    $stmt_products = $pdo->prepare($products_sql);
    $stmt_products->execute($params);
    $products = $stmt_products->fetchAll();

} catch (PDOException $e) {
    // Trả về lỗi nếu có sự cố
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['error' => 'Lỗi CSDL: ' . $e->getMessage()]);
    exit();
}


// --- TẠO HTML CHO SẢN PHẨM VÀ PHÂN TRANG ---
ob_start();
if (!empty($products)) {
    foreach ($products as $product) { include __DIR__ . '/templates/product-card.php'; }
} else {
    echo '<p class="text-center col-12 my-5">Không tìm thấy sản phẩm nào phù hợp.</p>';
}
$products_html = ob_get_clean();

ob_start();
?>
<nav aria-label="Page navigation">
   <ul class="pagination justify-content-center">
      <?php if($total_pages > 1): ?>
      <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>"><a class="page-link" href="#"
            data-page="<?php echo $page - 1; ?>">Previous</a></li>
      <?php for($i = 1; $i <= $total_pages; $i++): ?>
      <li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link" href="#"
            data-page="<?php echo $i; ?>"><?php echo $i; ?></a></li>
      <?php endfor; ?>
      <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>"><a class="page-link" href="#"
            data-page="<?php echo $page + 1; ?>">Next</a></li>
      <?php endif; ?>
   </ul>
</nav>
<?php
$pagination_html = ob_get_clean();

// Trả về kết quả dạng JSON
header('Content-Type: application/json');
echo json_encode([
    'products_html' => $products_html,
    'pagination_html' => $pagination_html
]);
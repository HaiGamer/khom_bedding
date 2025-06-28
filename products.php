<?php 
// Nạp header
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LOGIC PHP ĐÃ ĐƯỢC SỬA LỖI ---

// 1. Lấy các tham số từ URL
$category_slug = $_GET['category'] ?? null;
$collection_slug = $_GET['collection'] ?? null;
$page_title = 'Tất cả sản phẩm';

// 2. Cấu hình phân trang
$results_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $results_per_page;

// 3. Xây dựng các điều kiện truy vấn
$where_clauses = ["pv.is_default = TRUE"];
$params = [];
$join_clause = '';

if ($category_slug) {
    $stmt_cat = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ?");
    $stmt_cat->execute([$category_slug]);
    $category = $stmt_cat->fetch();
    if ($category) {
        $page_title = $category['name'];
        $where_clauses[] = "p.category_id = ?";
        $params[] = $category['id'];
    }
} 
if ($collection_slug) {
    $stmt_coll = $pdo->prepare("SELECT id, name FROM collections WHERE slug = ?");
    $stmt_coll->execute([$collection_slug]);
    $collection = $stmt_coll->fetch();
    if ($collection) {
        $page_title = 'Bộ sưu tập: ' . $collection['name'];
        $join_clause = "JOIN product_collections pc ON p.id = pc.product_id";
        $where_clauses[] = "pc.collection_id = ?";
        $params[] = $collection['id'];
    }
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

// 4. Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(DISTINCT p.id) FROM products p JOIN product_variants pv ON p.id = pv.product_id $join_clause $where_sql";
$stmt_count = $pdo->prepare($count_sql);
$stmt_count->execute($params);
$total_results = $stmt_count->fetchColumn();
$total_pages = ceil($total_results / $results_per_page);

// 5. Lấy sản phẩm cho trang đầu tiên
$products = [];
try {
    // === SỬA LỖI: Đưa LIMIT và OFFSET trực tiếp vào chuỗi SQL sau khi đã ép kiểu (int) ===
    $products_sql = "
        SELECT p.name, p.slug, pv.price, pv.original_price, 
               (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_featured = TRUE LIMIT 1) as image_url
        FROM products p
        JOIN product_variants pv ON p.id = pv.product_id
        $join_clause
        $where_sql
        GROUP BY p.id
        ORDER BY p.created_at DESC 
        LIMIT $results_per_page OFFSET $offset
    ";
    $stmt_products = $pdo->prepare($products_sql);
    // Chỉ thực thi với các tham số của WHERE clause
    $stmt_products->execute($params);
    $products = $stmt_products->fetchAll();
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<div class="container my-5">
   <div class="d-lg-none mb-4">
      <button class="btn btn-primary w-100" type="button" data-bs-toggle="offcanvas"
         data-bs-target="#filterOffcanvasMobile" aria-controls="filterOffcanvasMobile"><i class="bi bi-funnel"></i> Mở
         Bộ lọc</button>
   </div>
   <div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvasMobile"
      aria-labelledby="filterOffcanvasLabel">
      <div class="offcanvas-header">
         <h5 class="offcanvas-title" id="filterOffcanvasLabel" style="font-family: var(--font-heading);">Bộ lọc</h5>
         <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body"><?php $prefix = 'mobile-'; include __DIR__ . '/templates/filter-sidebar.php'; ?></div>
   </div>
   <div class="row">
      <div class="col-lg-3 d-none d-lg-block">
         <h4 class="mb-4" style="font-family: var(--font-heading);">Bộ lọc</h4>
         <?php $prefix = 'desktop-'; include __DIR__ . '/templates/filter-sidebar.php'; ?>
      </div>
      <div class="col-lg-9">
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0" style="font-family: var(--font-heading);"><?php echo htmlspecialchars($page_title); ?></h2>
            <div class="d-flex align-items-center">
               <label for="sort-by" class="form-label me-2 mb-0">Sắp xếp:</label>
               <select class="form-select form-select-sm" id="sort-by" style="width: auto;">
                  <option value="newest" selected>Mới nhất</option>
                  <option value="price_asc">Giá: Thấp đến cao</option>
                  <option value="price_desc">Giá: Cao đến thấp</option>
                  <option value="name_asc">Tên: A-Z</option>
               </select>
            </div>
         </div>
         <input type="hidden" id="current-category-slug" value="<?php echo htmlspecialchars($category_slug ?? ''); ?>">
         <input type="hidden" id="current-collection-slug"
            value="<?php echo htmlspecialchars($collection_slug ?? ''); ?>">
         <div id="product-grid" class="row row-cols-2 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): include __DIR__ . '/templates/product-card.php'; endforeach; ?>
            <?php else: ?>
            <p class="text-center col-12">Không có sản phẩm nào trong danh mục này.</p>
            <?php endif; ?>
         </div>
         <div id="pagination-container" class="mt-5">
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
               <ul class="pagination justify-content-center">
                  <li class="page-item disabled"><a class="page-link" href="#" data-page="0">Previous</a></li>
                  <?php for($i = 1; $i <= $total_pages; $i++): ?>
                  <li class="page-item <?php if($i == 1) echo 'active'; ?>"><a class="page-link" href="#"
                        data-page="<?php echo $i; ?>"><?php echo $i; ?></a></li>
                  <?php endfor; ?>
                  <li class="page-item <?php if($total_pages <= 1) echo 'disabled'; ?>"><a class="page-link" href="#"
                        data-page="2">Next</a></li>
               </ul>
            </nav>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
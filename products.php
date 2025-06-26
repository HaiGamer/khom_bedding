<?php 
// Nạp header
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LOGIC PHP ---

// 1. Lấy slug của danh mục từ URL
$category_slug = $_GET['category'] ?? null;
$category_name = 'Tất cả sản phẩm'; // Tên mặc định

// 2. Xây dựng câu lệnh SQL dựa trên việc có danh mục được chọn hay không
$base_sql = "
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = TRUE
";
$where_clause = "WHERE pv.is_default = TRUE";
$params = [];

if ($category_slug) {
    // Nếu có slug, tìm category_id và thêm điều kiện vào câu lệnh
    $stmt_cat = $pdo->prepare("SELECT id, name FROM categories WHERE slug = ?");
    $stmt_cat->execute([$category_slug]);
    $category = $stmt_cat->fetch();
    
    if ($category) {
        $category_id = $category['id'];
        $category_name = $category['name']; // Cập nhật tên để hiển thị
        $where_clause .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
}

// 3. Lấy danh sách sản phẩm ban đầu để hiển thị
try {
    $sql = "SELECT p.name, p.slug, pv.price, pv.original_price, pi.image_url " . $base_sql . $where_clause . " ORDER BY p.created_at DESC";
    $stmt_products = $pdo->prepare($sql);
    $stmt_products->execute($params);
    $products = $stmt_products->fetchAll();
} catch(PDOException $e) {
    $products = [];
    // Có thể ghi log lỗi ở đây
}
?>

<div class="container my-5">

   <div class="d-lg-none mb-4">
      <button class="btn btn-primary w-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas"
         aria-controls="filterOffcanvas">
         <i class="bi bi-funnel"></i> Mở Bộ lọc
      </button>
   </div>

   <div class="offcanvas offcanvas-start" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
      <div class="offcanvas-header">
         <h5 class="offcanvas-title" id="filterOffcanvasLabel" style="font-family: var(--font-heading);">Bộ lọc</h5>
         <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
         <?php 
            // Thêm biến $prefix cho bộ lọc di động
            $prefix = 'mobile-';
            include __DIR__ . '/templates/filter-sidebar.php'; 
        ?>
      </div>
   </div>


   <div class="row">
      <div class="col-lg-3 d-none d-lg-block">
         <h4 class="mb-4" style="font-family: var(--font-heading);">Bộ lọc</h4>
         <?php 
            // Thêm biến $prefix cho bộ lọc máy tính
            $prefix = 'desktop-';
            include __DIR__ . '/templates/filter-sidebar.php'; 
        ?>
      </div>

      <div class="col-lg-9">
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0" style="font-family: var(--font-heading);"><?php echo htmlspecialchars($category_name); ?>
            </h2>
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


         <div id="product-grid" class="row row-cols-2 row-cols-md-2 row-cols-lg-3 g-4">
            <?php if (!empty($products)): ?>
            <?php foreach ($products as $product): ?>
            <?php include __DIR__ . '/templates/product-card.php'; ?>
            <?php endforeach; ?>
            <?php else: ?>
            <p class="text-center col-12">Không có sản phẩm nào trong danh mục này.</p>
            <?php endif; ?>
         </div>

         <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
               <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
               <li class="page-item active"><a class="page-link" href="#">1</a></li>
               <li class="page-item"><a class="page-link" href="#">2</a></li>
               <li class="page-item"><a class="page-link" href="#">3</a></li>
               <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
         </nav>
      </div>
   </div>
</div>

<?php 
// Nạp footer
include_once __DIR__ . '/includes/footer.php'; 
?>
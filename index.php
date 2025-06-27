<?php 
// Nạp header
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LẤY DỮ LIỆU TỪ DATABASE ---

// 1. Lấy 8 sản phẩm mới nhất
$stmt_new_arrivals = $pdo->prepare("
    SELECT p.name, p.slug, pv.price, pv.original_price, pi.image_url
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = TRUE
    WHERE pv.is_default = TRUE ORDER BY p.created_at DESC LIMIT 8
");
$stmt_new_arrivals->execute();
$new_arrivals = $stmt_new_arrivals->fetchAll();

// 2. Lấy 8 sản phẩm bán chạy nhất
$stmt_best_sellers = $pdo->prepare("
    SELECT p.name, p.slug, pv.price, pv.original_price, pi.image_url
    FROM products p
    JOIN product_variants pv ON p.id = pv.product_id
    LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = TRUE
    WHERE pv.is_default = TRUE ORDER BY RAND() LIMIT 8
");
$stmt_best_sellers->execute();
$best_sellers = $stmt_best_sellers->fetchAll();

// 3. Lấy 3 bài viết Blog mới nhất
$stmt_posts = $pdo->prepare("
    SELECT p.title, p.slug, p.excerpt, p.featured_image, p.created_at, u.full_name AS author_name
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT 3
");
$stmt_posts->execute();
$latest_posts = $stmt_posts->fetchAll();

// === LOGIC MỚI: LẤY SẢN PHẨM THEO TỪNG DANH MỤC ĐỂ HIỂN THỊ TRONG TAB ===
// Lấy ra 4 danh mục đầu tiên để làm tab
$featured_categories = $pdo->query("SELECT * FROM categories LIMIT 4")->fetchAll();
$products_by_category = [];
if (!empty($featured_categories)) {
    $category_ids = array_column($featured_categories, 'id');
    $placeholders = implode(',', array_fill(0, count($category_ids), '?'));

    // Câu lệnh SQL phức tạp này dùng ROW_NUMBER() để đánh số thứ tự sản phẩm trong mỗi danh mục
    // và chỉ lấy 4 sản phẩm đầu tiên (mới nhất) của mỗi danh mục đó trong một lần truy vấn duy nhất.
    $sql_products_by_cat = "
        WITH RankedProducts AS (
            SELECT
                p.id, p.name, p.slug, p.category_id,
                pv.price, pv.original_price,
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_featured = TRUE LIMIT 1) as image_url,
                ROW_NUMBER() OVER(PARTITION BY p.category_id ORDER BY p.created_at DESC) as rn
            FROM products p
            JOIN product_variants pv ON p.id = pv.product_id AND pv.is_default = TRUE
            WHERE p.category_id IN ($placeholders)
        )
        SELECT * FROM RankedProducts WHERE rn <= 4;
    ";
    $stmt_products_by_cat = $pdo->prepare($sql_products_by_cat);
    $stmt_products_by_cat->execute($category_ids);
    $products_list = $stmt_products_by_cat->fetchAll();

    // Gom nhóm sản phẩm lại theo category_id
    foreach ($products_list as $product) {
        $products_by_category[$product['category_id']][] = $product;
    }
}


?>

<div class="container-fluid p-0 mt-3 mb-5">
   <div id="hero-carousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
      <div class="carousel-indicators"><button type="button" data-bs-target="#hero-carousel" data-bs-slide-to="0"
            class="active" aria-current="true" aria-label="Slide 1"></button><button type="button"
            data-bs-target="#hero-carousel" data-bs-slide-to="1" aria-label="Slide 2"></button><button type="button"
            data-bs-target="#hero-carousel" data-bs-slide-to="2" aria-label="Slide 3"></button></div>
      <div class="carousel-inner">
         <div class="carousel-item active" data-bs-interval="5000"><img
               src="https://nemthuanviet.com/wp-content/uploads/2025/06/Website-1920x650-2.png" class="d-block w-100"
               alt="Banner 1"></div>
         <div class="carousel-item" data-bs-interval="5000"><img
               src="https://nemthuanviet.com/wp-content/uploads/2025/05/WEBSITE-CHILL-TAI-GIA-1536x520.jpg"
               class="d-block w-100" alt="Banner 2"></div>
         <div class="carousel-item" data-bs-interval="5000"><img
               src="https://nemthuanviet.com/wp-content/uploads/2025/02/1920x650-2-1536x520.png" class="d-block w-100"
               alt="Banner 3"></div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#hero-carousel" data-bs-slide="prev"><span
            class="carousel-control-prev-icon" aria-hidden="true"></span><span
            class="visually-hidden">Previous</span></button><button class="carousel-control-next" type="button"
         data-bs-target="#hero-carousel" data-bs-slide="next"><span class="carousel-control-next-icon"
            aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
   </div>
</div>



<div class="container">
   <section class="py-5">
      <div class="container">
         <section class="category-tabs-section">
            <ul class="nav nav-tabs justify-content-center mb-4 category-tab-nav" id="pills-tab" role="tablist">
               <?php foreach ($featured_categories as $index => $category): ?>
               <li class="nav-item nav-item-sp" role="presentation">
                  <button class="nav-link <?php if ($index === 0) echo 'active'; ?>"
                     id="pills-<?php echo $category['slug']; ?>-tab" data-bs-toggle="pill"
                     data-bs-target="#pills-<?php echo $category['slug']; ?>" type="button"
                     role="tab"><?php echo htmlspecialchars($category['name']); ?></button>
               </li>
               <?php endforeach; ?>
            </ul>

            <div class="tab-content category-tabs-section-wrapper" id="pills-tabContent">
               <?php foreach ($featured_categories as $index => $category): ?>
               <div class="tab-pane fade <?php if ($index === 0) echo 'show active'; ?>"
                  id="pills-<?php echo $category['slug']; ?>" role="tabpanel">
                  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
                     <?php if (!empty($products_by_category[$category['id']])): ?>
                     <?php foreach ($products_by_category[$category['id']] as $product): ?>
                     <?php include __DIR__ . '/templates/product-card.php'; ?>
                     <?php endforeach; ?>
                     <?php else: ?>
                     <p class="text-center col-12">Chưa có sản phẩm trong danh mục này.</p>
                     <?php endif; ?>
                  </div>
                  <div class="text-center mt-4">
                     <a href="<?php echo BASE_URL; ?>products.php?category=<?php echo $category['slug']; ?>"
                        class="btn btn-warning">Xem thêm >></a>
                  </div>
               </div>
               <?php endforeach; ?>
            </div>
         </section>
      </div>
   </section>



   <section class="new-arrivals mb-5">
      <h2 class="text-center mb-4" style="font-family: var(--font-heading);">Bộ Sưu Tập Mới</h2>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
         <?php foreach ($new_arrivals as $product): include __DIR__ . '/templates/product-card.php'; endforeach; ?>
      </div>
   </section>

   <section class="best-sellers mb-5">
      <h2 class="text-center mb-4" style="font-family: var(--font-heading);">Sản Phẩm Bán Chạy</h2>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
         <?php foreach ($best_sellers as $product): include __DIR__ . '/templates/product-card.php'; endforeach; ?>
      </div>
   </section>

   <section class="call-to-action text-center p-5 my-5 rounded" style="background-color: #EAE6DD;">
      <h2 style="font-family: var(--font-heading);">Khám phá Bộ sưu tập Lụa Tencel</h2>
      <p class="lead text-muted">Trải nghiệm sự mềm mại và thoáng mát vượt trội, mang đến giấc ngủ hoàng gia cho chính
         ngôi nhà của bạn.</p>
      <a href="#" class="btn btn-primary btn-lg mt-3">Xem ngay</a>
   </section>



   <section class="why-us text-white p-5 rounded mb-5" style="background-color: var(--secondary-color);">
      <h2 class="text-center mb-4" style="font-family: var(--font-heading); color: var(--white-color);">Vì Sao Chọn
         Khóm?</h2>
      <div class="row text-center">
         <div class="col-md-4">
            <i class="bi bi-feather" style="font-size: 3rem; color: var(--accent-color);"></i>
            <h5 class="mt-3">Chất Liệu Tự Nhiên</h5>
            <p>An toàn cho làn da, thân thiện với môi trường.</p>
         </div>
         <div class="col-md-4">
            <i class="bi bi-truck" style="font-size: 3rem; color: var(--accent-color);"></i>
            <h5 class="mt-3">Giao Hàng Miễn Phí</h5>
            <p>Áp dụng cho mọi đơn hàng trên toàn quốc.</p>
         </div>
         <div class="col-md-4">
            <i class="bi bi-patch-check" style="font-size: 3rem; color: var(--accent-color);"></i>
            <h5 class="mt-3">Bảo Hành</h5>
            <p>Cam kết chất lượng, hỗ trợ đổi trả dễ dàng.</p>
         </div>
      </div>
   </section>

   <section class="latest-blog mb-5">
      <h2 class="text-center mb-4" style="font-family: var(--font-heading);">Góc chia sẻ</h2>
      <div class="row">
         <?php if (!empty($latest_posts)): ?>
         <?php foreach ($latest_posts as $post): ?>
         <?php 
                        // Tái sử dụng template post-card.php một cách hoàn hảo!
                        include __DIR__ . '/templates/post-card.php'; 
                    ?>
         <?php endforeach; ?>
         <?php endif; ?>
      </div>
      <div class="text-center mt-4">
         <a href="<?php echo BASE_URL; ?>blog/" class="btn btn-outline-primary">Xem tất cả bài viết</a>
      </div>
   </section>


</div>

<?php 
// Nạp footer
include_once __DIR__ . '/includes/footer.php'; 
?>
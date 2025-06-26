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
   <section class="why-us bg-light p-5 rounded mb-5">
      <h2 class="text-center mb-4" style="font-family: var(--font-heading);">Vì Sao Chọn Khóm?</h2>
      <div class="row text-center">
         <div class="col-md-4">
            <i class="bi bi-feather" style="font-size: 3rem; color: var(--accent-color);"></i>
            <h5 class="mt-3">Chất Liệu Tự Nhiên</h5>
            <p class="text-muted">An toàn cho làn da, thân thiện với môi trường.</p>
         </div>
         <div class="col-md-4">
            <i class="bi bi-truck" style="font-size: 3rem; color: var(--accent-color);"></i>
            <h5 class="mt-3">Giao Hàng Miễn Phí</h5>
            <p class="text-muted">Áp dụng cho mọi đơn hàng trên toàn quốc.</p>
         </div>
         <div class="col-md-4">
            <i class="bi bi-patch-check" style="font-size: 3rem; color: var(--accent-color);"></i>
            <h5 class="mt-3">Bảo Hành 1 Năm</h5>
            <p class="text-muted">Cam kết chất lượng, hỗ trợ đổi trả dễ dàng.</p>
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
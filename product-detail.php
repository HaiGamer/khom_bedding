<?php
include_once __DIR__ . '/includes/header.php';

// --- PHẦN 1: LẤY DỮ LIỆU TỪ DATABASE ---
$product_slug = isset($_GET['slug']) ? htmlspecialchars($_GET['slug']) : '';
if (empty($product_slug)) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Sản phẩm không hợp lệ.</div></div>";
    include_once __DIR__ . '/includes/footer.php';
    exit();
}

// Chuẩn bị các biến
$product = null; $variants_data = []; $attributes_map = []; $product_images = []; 
$best_sellers = []; $reviews = []; $average_rating = 0; $sales_count = 0; $user_can_review = false;

try {
    // 1. Lấy thông tin sản phẩm gốc
    $stmt_product = $pdo->prepare("SELECT * FROM products WHERE slug = ?");
    $stmt_product->execute([$product_slug]);
    $product = $stmt_product->fetch();

    if (!$product) { throw new Exception("Sản phẩm không tồn tại."); }
    
    $product_id = $product['id'];

    // 2. Lấy gallery ảnh của sản phẩm
    $stmt_images = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_featured DESC, id ASC");
    $stmt_images->execute([$product_id]);
    $product_images = $stmt_images->fetchAll();

    // 3. Lấy tất cả các phiên bản (variants) và thuộc tính của chúng
    $sql_variants = "
        SELECT pv.id AS variant_id, pv.sku, pv.price, pv.original_price, pv.stock_quantity, pv.is_default,
               a.name AS attribute_name, av.value AS attribute_value
        FROM product_variants pv 
        JOIN variant_values vv ON pv.id = vv.variant_id
        JOIN attribute_values av ON vv.attribute_value_id = av.id 
        JOIN attributes a ON av.attribute_id = a.id
        WHERE pv.product_id = ? ORDER BY pv.id, a.id";
    $stmt_variants = $pdo->prepare($sql_variants);
    $stmt_variants->execute([$product_id]);
    $results = $stmt_variants->fetchAll();
    
    $temp_variants = [];
    foreach ($results as $row) {
        $variant_id = $row['variant_id'];
        if (!isset($temp_variants[$variant_id])) {
            $temp_variants[$variant_id] = [ 'id' => $variant_id, 'sku' => $row['sku'], 'price' => (float)$row['price'], 'original_price' => (float)($row['original_price'] ?? 0), 'stock' => (int)$row['stock_quantity'], 'is_default' => (bool)$row['is_default'], 'attributes' => [] ];
        }
        $temp_variants[$variant_id]['attributes'][$row['attribute_name']] = $row['attribute_value'];
    }
    $variants_data = array_values($temp_variants);

    // 4. Lấy tất cả các tùy chọn thuộc tính để hiển thị
    $sql_attributes = "SELECT DISTINCT a.name AS attribute_name, av.value AS attribute_value FROM product_variants pv JOIN variant_values vv ON pv.id = vv.variant_id JOIN attribute_values av ON vv.attribute_value_id = av.id JOIN attributes a ON av.attribute_id = a.id WHERE pv.product_id = ? ORDER BY a.id, av.id";
    $stmt_attributes = $pdo->prepare($sql_attributes);
    $stmt_attributes->execute([$product_id]);
    $all_attributes = $stmt_attributes->fetchAll();
    foreach ($all_attributes as $attr) { $attributes_map[$attr['attribute_name']][] = $attr['attribute_value']; }

    // 5. Lấy 8 sản phẩm bán chạy
    $stmt_best_sellers = $pdo->prepare("SELECT p.name, p.slug, pv.price, pv.original_price, pi.image_url FROM products p JOIN product_variants pv ON p.id = pv.product_id LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_featured = TRUE WHERE pv.is_default = TRUE AND p.id != ? ORDER BY RAND() LIMIT 8");
    $stmt_best_sellers->execute([$product_id]);
    $best_sellers = $stmt_best_sellers->fetchAll();

    // 6. Lấy đánh giá của sản phẩm và tính toán điểm trung bình
    $stmt_reviews = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
    $stmt_reviews->execute([$product_id]);
    $reviews = $stmt_reviews->fetchAll();
    if (count($reviews) > 0) {
        $total_rating = array_sum(array_column($reviews, 'rating'));
        $average_rating = round($total_rating / count($reviews), 1);
    }
    
    // 7. Lấy số lượng đã bán
    $stmt_sales = $pdo->prepare("SELECT SUM(oi.quantity) FROM order_items oi JOIN product_variants pv ON oi.variant_id = pv.id WHERE pv.product_id = ?");
    $stmt_sales->execute([$product_id]);
    $sales_count = $stmt_sales->fetchColumn() ?: 0;

    // 8. Kiểm tra xem user có thể đánh giá sản phẩm này không
    if (isset($_SESSION['user_id'])) {
        $stmt_check_purchase = $pdo->prepare("SELECT COUNT(*) FROM orders o JOIN order_items oi ON o.id = oi.order_id JOIN product_variants pv ON oi.variant_id = pv.id WHERE o.user_id = ? AND pv.product_id = ? AND o.status = 'completed'");
        $stmt_check_purchase->execute([$_SESSION['user_id'], $product_id]);
        if ($stmt_check_purchase->fetchColumn() > 0) { $user_can_review = true; }
    }
} catch (Exception $e) {
    echo "<div class='container my-5'><div class='alert alert-danger'>Lỗi: " . $e->getMessage() . "</div></div>";
    include_once __DIR__ . '/includes/footer.php';
    exit();
}
?>

<div class="container my-5" id="product-detail-page-container">
   <div class="row">
      <div class="col-lg-6">
         <?php include __DIR__ . '/templates/partials/product-gallery.php'; ?>
      </div>
      <div class="col-lg-6">
         <?php include __DIR__ . '/templates/partials/product-purchase-box.php'; ?>
      </div>
   </div>
   <div class="row mt-5">
      <div class="col-12">
         <?php include __DIR__ . '/templates/partials/product-info-tabs.php'; ?>
      </div>
   </div>
   <hr class="my-5">
   <section class="related-products">
      <h2 class="text-center mb-4" style="font-family: var(--font-heading);">Có thể bạn cũng thích</h2>
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4">
         <?php foreach ($best_sellers as $product): ?>
         <?php include __DIR__ . '/templates/product-card.php'; ?>
         <?php endforeach; ?>
      </div>
   </section>
</div>

<script>
const allVariantsData = <?php echo json_encode($variants_data, JSON_PRETTY_PRINT); ?>;
</script>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
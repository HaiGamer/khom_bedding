<?php
// Nạp header và file cấu hình
include_once __DIR__ . '/includes/header.php';

// Khởi tạo các biến cần thiết
$cart_items = [];
$total_price = 0;

// Kiểm tra xem giỏ hàng có dữ liệu không
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Lấy danh sách tất cả các variant_id từ giỏ hàng
    $variant_ids = array_keys($_SESSION['cart']);

    // Tạo chuỗi placeholder cho câu lệnh IN, ví dụ: (?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));

    // Chuẩn bị câu lệnh SQL
    $sql = "
        SELECT
            p.name AS product_name,
            p.slug AS product_slug,
            pv.id AS variant_id,
            pv.price,
            pv.image_url,
            GROUP_CONCAT(CONCAT(a.name, ': ', av.value) ORDER BY a.id SEPARATOR ' | ') AS attributes_string
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id
        JOIN variant_values vv ON pv.id = vv.variant_id
        JOIN attribute_values av ON vv.attribute_value_id = av.id
        JOIN attributes a ON av.attribute_id = a.id
        WHERE pv.id IN ($placeholders)
        GROUP BY pv.id, p.name, p.slug, pv.price, pv.image_url
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($variant_ids);
    $products_in_cart = $stmt->fetchAll();

    // Tổ chức lại dữ liệu để dễ hiển thị
    foreach ($products_in_cart as $product) {
        $variant_id = $product['variant_id'];
        $quantity = $_SESSION['cart'][$variant_id];
        $sub_total = $product['price'] * $quantity;

        $cart_items[] = [
            'product_name' => $product['product_name'],
            'product_slug' => $product['product_slug'],
            'variant_id' => $variant_id,
            'price' => $product['price'],
            'image_url' => $product['image_url'],
            'attributes_string' => $product['attributes_string'],
            'quantity' => $quantity,
            'sub_total' => $sub_total
        ];
        $total_price += $sub_total;
    }
}
?>

<div class="container my-5 cart-page-container">
   <h1 class="text-center mb-4" style="font-family: var(--font-heading);">Giỏ hàng của bạn</h1>

   <?php if (!empty($cart_items)): ?>
   <div class="row">
      <div class="col-lg-8">
         <table class="table align-middle">
            <thead>
               <tr>
                  <th colspan="2">Sản phẩm</th>
                  <th class="text-center">Giá</th>
                  <th class="text-center">Số lượng</th>
                  <th class="text-end">Tạm tính</th>
                  <th></th>
               </tr>
            </thead>
            <tbody>
               <?php foreach ($cart_items as $item): ?>
               <tr class="cart-item-row" data-variant-id="<?php echo $item['variant_id']; ?>"
                  data-price="<?php echo $item['price']; ?>">
                  <td style="width: 100px;">
                     <a href="product-detail.php?slug=<?php echo $item['product_slug']; ?>">
                        <img
                           src="<?php echo $item['image_url'] ?? 'https://via.placeholder.com/100x100.png?text=Khóm'; ?>"
                           alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-fluid rounded">
                     </a>
                  </td>
                  <td>
                     <a href="product-detail.php?slug=<?php echo $item['product_slug']; ?>"
                        class="text-decoration-none text-dark fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></a>
                     <p class="text-muted small"><?php echo htmlspecialchars($item['attributes_string']); ?></p>
                  </td>
                  <td class="text-center"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ</td>
                  <td class="text-center" style="width: 120px;">
                     <input type="number" class="form-control text-center quantity-input"
                        value="<?php echo $item['quantity']; ?>" min="0">
                  </td>
                  <td class="text-end fw-bold">
                     <span class="item-subtotal"><?php echo number_format($item['sub_total'], 0, ',', '.'); ?></span>đ
                  </td>
                  <td class="text-center">
                     <button class="btn btn-sm btn-outline-danger remove-item-btn"
                        data-variant-id="<?php echo $item['variant_id']; ?>">
                        <i class="bi bi-trash"></i>
                     </button>
                  </td>
               </tr>
               <?php endforeach; ?>
            </tbody>
         </table>
      </div>

      <div class="col-lg-4">
         <div class="card">
            <div class="card-body">
               <h5 class="card-title mb-3" style="font-family: var(--font-heading);">Tóm tắt đơn hàng</h5>
               <div class="d-flex justify-content-between mb-2">
                  <span>Tạm tính</span>
                  <span id="cart-subtotal"><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
               </div>
               <div class="d-flex justify-content-between mb-3">
                  <span>Phí vận chuyển</span>
                  <span>Sẽ được tính ở bước sau</span>
               </div>
               <hr>
               <div class="d-flex justify-content-between fw-bold fs-5">
                  <span>Tổng cộng</span>
                  <span id="cart-grand-total"><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
               </div>
               <div class="d-grid mt-4">
                  <a href="checkout.php" class="btn btn-primary btn-lg">Tiến hành thanh toán</a>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php else: ?>
   <div class="text-center py-5">
      <i class="bi bi-cart-x" style="font-size: 5rem; color: var(--secondary-color);"></i>
      <h3 class="mt-3">Giỏ hàng của bạn đang trống</h3>
      <p class="text-muted">Hãy quay lại và chọn cho mình những sản phẩm ưng ý nhé.</p>
      <a href="index.php" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
   </div>
   <?php endif; ?>

</div>

<?php
// Nạp footer
include_once __DIR__ . '/includes/footer.php';
?>
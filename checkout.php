<?php
// Nạp header và file cấu hình
include_once __DIR__ . '/includes/header.php';

// --- PHẦN 1: KIỂM TRA VÀ LẤY DỮ LIỆU ---

// Quan trọng: Nếu giỏ hàng trống, chuyển hướng người dùng về trang giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Lấy thông tin giỏ hàng (giữ nguyên như cũ)
$cart_items = [];
$total_price = 0;
// ... (toàn bộ code lấy $cart_items và $total_price giữ nguyên) ...
$variant_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($variant_ids), '?'));
$sql = "
    SELECT p.name AS product_name, pv.id AS variant_id, pv.price,
           GROUP_CONCAT(CONCAT(a.name, ': ', av.value) ORDER BY a.id SEPARATOR ' | ') AS attributes_string
    FROM product_variants pv JOIN products p ON pv.product_id = p.id JOIN variant_values vv ON pv.id = vv.variant_id
    JOIN attribute_values av ON vv.attribute_value_id = av.id JOIN attributes a ON av.attribute_id = a.id
    WHERE pv.id IN ($placeholders) GROUP BY pv.id, p.name, pv.price
";
$stmt = $pdo->prepare($sql);
$stmt->execute($variant_ids);
$products_in_cart = $stmt->fetchAll();
foreach ($products_in_cart as $product) {
    $quantity = $_SESSION['cart'][$product['variant_id']];
    $cart_items[] = [
        'product_name' => $product['product_name'], 'attributes_string' => $product['attributes_string'],
        'quantity' => $quantity, 'price' => $product['price'],
        'sub_total' => $product['price'] * $quantity
    ];
    $total_price += $product['price'] * $quantity;
}


// --- PHẦN LOGIC MỚI: LẤY ĐỊA CHỈ ĐÃ LƯU CỦA NGƯỜI DÙNG ---
$saved_addresses = [];
$default_address_data = null; // Lưu thông tin của địa chỉ mặc định

if (isset($_SESSION['user_id'])) {
    try {
        $stmt_addr = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt_addr->execute([$_SESSION['user_id']]);
        $saved_addresses = $stmt_addr->fetchAll();

        // Nếu có địa chỉ, lấy địa chỉ đầu tiên (đã được sắp xếp is_default=1 lên đầu) làm mặc định
        if (!empty($saved_addresses)) {
            $default_address_data = $saved_addresses[0];
        }
    } catch (PDOException $e) {
        // Bỏ qua lỗi nếu không lấy được địa chỉ
    }
}
?>

<div class="container my-5">
   <h1 class="text-center mb-5" style="font-family: var(--font-heading);">Thanh toán</h1>

   <form action="place-order.php" method="POST">
      <div class="row g-5">
         <div class="col-lg-7">
            <h4 style="font-family: var(--font-heading);">Thông tin giao hàng</h4>
            <hr class="mt-2 mb-4">

            <?php if (!empty($saved_addresses)): ?>
            <div class="mb-3">
               <label for="saved_address_select" class="form-label">Chọn địa chỉ đã lưu</label>
               <select class="form-select" id="saved_address_select">
                  <option value="">-- Nhập địa chỉ mới bằng tay --</option>
                  <?php foreach($saved_addresses as $address): ?>
                  <option value="<?php echo $address['id']; ?>"
                     data-name="<?php echo htmlspecialchars($address['full_name']); ?>"
                     data-phone="<?php echo htmlspecialchars($address['phone_number']); ?>"
                     data-address="<?php echo htmlspecialchars($address['address_line']); ?>"
                     <?php if($address['is_default']) echo 'selected'; ?>>
                     <?php echo htmlspecialchars($address['address_line']); ?>
                  </option>
                  <?php endforeach; ?>
               </select>
            </div>
            <?php endif; ?>

            <div class="mb-3">
               <label for="full_name" class="form-label">Họ và tên</label>
               <input type="text" class="form-control" id="full_name" name="full_name"
                  value="<?php echo htmlspecialchars($default_address_data['full_name'] ?? $_SESSION['user_name'] ?? ''); ?>"
                  required>
            </div>

            <div class="row">
               <div class="col-md-7 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email"
                     value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
                     <?php if(isset($_SESSION['user_id'])) echo 'readonly'; ?> required>
               </div>
               <div class="col-md-5 mb-3">
                  <label for="phone_number" class="form-label">Số điện thoại</label>
                  <input type="tel" class="form-control" id="phone_number" name="phone_number"
                     value="<?php echo htmlspecialchars($default_address_data['phone_number'] ?? ''); ?>" required>
               </div>
            </div>

            <div class="mb-3">
               <label for="address" class="form-label">Địa chỉ</label>
               <textarea class="form-control" id="address" name="address" rows="3"
                  placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"
                  required><?php echo htmlspecialchars($default_address_data['address_line'] ?? ''); ?></textarea>
            </div>

            <div class="mb-4">
               <label for="note" class="form-label">Ghi chú (tùy chọn)</label>
               <textarea class="form-control" id="note" name="note" rows="2"></textarea>
            </div>
            <h4 style="font-family: var(--font-heading);">Phương thức thanh toán</h4>
            <hr class="mt-2 mb-4">
            <div class="form-check border p-3 rounded mb-3">
               <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
               <label class="form-check-label fw-bold" for="payment_cod">Thanh toán khi nhận hàng (COD)</label>
               <p class="text-muted small mb-0 mt-2">Bạn sẽ thanh toán bằng tiền mặt cho nhân viên giao hàng khi nhận
                  được sản phẩm.</p>
            </div>
            <div class="form-check border p-3 rounded">
               <input class="form-check-input" type="radio" name="payment_method" id="payment_bank"
                  value="bank_transfer">
               <label class="form-check-label fw-bold" for="payment_bank">Chuyển khoản ngân hàng</label>
            </div>

         </div>

         <div class="col-lg-5">
            <div class="card" style="background-color: #f8f9fa;">
               <div class="card-body">
                  <h4 class="card-title mb-4" style="font-family: var(--font-heading);">Đơn hàng của bạn</h4>

                  <?php foreach ($cart_items as $item): ?>
                  <div class="d-flex justify-content-between mb-3">
                     <div>
                        <p class="mb-0 fw-bold"><?php echo htmlspecialchars($item['product_name']); ?> x
                           <?php echo $item['quantity']; ?></p>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['attributes_string']); ?></p>
                     </div>
                     <span class="fw-bold"><?php echo number_format($item['sub_total'], 0, ',', '.'); ?>đ</span>
                  </div>
                  <?php endforeach; ?>

                  <hr>

                  <div class="d-flex justify-content-between mb-2">
                     <span>Tạm tính</span>
                     <span><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
                  </div>
                  <div class="d-flex justify-content-between mb-3">
                     <span>Phí vận chuyển</span>
                     <span class="text-success">Miễn phí</span>
                  </div>
                  <hr>
                  <div class="d-flex justify-content-between fw-bold fs-5">
                     <span>Tổng cộng</span>
                     <span
                        style="color: var(--accent-color);"><?php echo number_format($total_price, 0, ',', '.'); ?>đ</span>
                  </div>

                  <div class="d-grid mt-4">
                     <button type="submit" class="btn btn-primary btn-lg">Hoàn tất đơn hàng</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>

<?php
// Nạp footer
include_once __DIR__ . '/includes/footer.php';
?>
<?php
// Nạp header và file cấu hình
include_once __DIR__ . '/includes/header.php';

// --- BƯỚC BẢO VỆ: KIỂM TRA ĐĂNG NHẬP VÀ QUYỀN SỞ HỮU ---

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$order = null;
$order_items = [];

if ($order_id > 0) {
    try {
        // Lấy thông tin đơn hàng chính
        $stmt_order = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt_order->execute([$order_id, $user_id]);
        $order = $stmt_order->fetch();

        if ($order) {
            // === SỬA LỖI TRONG CÂU LỆNH SQL DƯỚI ĐÂY ===
            $stmt_items = $pdo->prepare("
                SELECT 
                    oi.quantity, 
                    oi.price, 
                    p.name AS product_name, 
                    p.slug AS product_slug,
                    pv.image_url, -- SỬA LỖI: Lấy image_url từ bảng product_variants (pv)
                    GROUP_CONCAT(CONCAT(a.name, ': ', av.value) ORDER BY a.id SEPARATOR ' | ') AS attributes_string
                FROM order_items oi
                JOIN product_variants pv ON oi.variant_id = pv.id
                JOIN products p ON pv.product_id = p.id
                -- Đã xóa LEFT JOIN đến product_images không cần thiết ở đây
                JOIN variant_values vv ON pv.id = vv.variant_id
                JOIN attribute_values av ON vv.attribute_value_id = av.id
                JOIN attributes a ON av.attribute_id = a.id
                WHERE oi.order_id = ?
                GROUP BY oi.id, p.name, p.slug, pv.image_url
            ");
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();
        }

    } catch (PDOException $e) {
        die("Lỗi truy vấn: " . $e->getMessage());
    }
}

// Tái sử dụng hàm format trạng thái từ trang account.php
function format_order_status_detail($status) {
    switch ($status) {
        case 'pending': return '<span class="badge bg-warning text-dark fs-6">Chờ xử lý</span>';
        case 'processing': return '<span class="badge bg-info text-dark fs-6">Đang xử lý</span>';
        case 'shipped': return '<span class="badge bg-primary fs-6">Đang giao</span>';
        case 'completed': return '<span class="badge bg-success fs-6">Hoàn thành</span>';
        case 'cancelled': return '<span class="badge bg-danger fs-6">Đã hủy</span>';
        default: return '<span class="badge bg-secondary fs-6">Không xác định</span>';
    }
}
?>

<div class="container my-5">

   <?php if ($order): ?>
   <div class="row">
      <div class="col-12">
         <a href="account.php" class="text-decoration-none mb-3 d-inline-block"><i class="bi bi-arrow-left"></i> Quay
            lại Lịch sử đơn hàng</a>
         <h1 class="mb-2" style="font-family: var(--font-heading);">Chi tiết Đơn hàng #<?php echo $order['id']; ?></h1>
         <p class="text-muted">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?> | Trạng thái:
            <?php echo format_order_status_detail($order['status']); ?></p>
      </div>
   </div>

   <div class="row g-5 mt-2">
      <div class="col-lg-8">
         <div class="card">
            <div class="card-header bg-light">
               <h5 class="mb-0">Các sản phẩm trong đơn</h5>
            </div>
            <div class="card-body">
               <table class="table align-middle">
                  <tbody>
                     <?php foreach ($order_items as $item): ?>
                     <tr>
                        <td style="width: 80px;">
                           <a href="product-detail.php?slug=<?php echo $item['product_slug']; ?>">
                              <img
                                 src="<?php echo BASE_URL . htmlspecialchars($item['image_url'] ?? 'assets/images/placeholder.png'); ?>"
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="img-fluid rounded">
                           </a>
                        </td>
                        <td>
                           <a href="product-detail.php?slug=<?php echo $item['product_slug']; ?>"
                              class="text-decoration-none text-dark fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></a>
                           <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['attributes_string']); ?>
                           </p>
                        </td>
                        <td class="text-nowrap"><?php echo number_format($item['price'], 0, ',', '.'); ?>đ x
                           <?php echo $item['quantity']; ?></td>
                        <td class="text-end fw-bold">
                           <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>đ</td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>

      <div class="col-lg-4">
         <div class="card">
            <div class="card-header bg-light">
               <h5 class="mb-0">Thông tin giao hàng</h5>
            </div>
            <div class="card-body">
               <p class="mb-1"><strong>Họ và tên:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
               <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
               <p class="mb-1"><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?>
               </p>
               <p class="mb-1"><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
               <p class="mb-1"><strong>Phương thức TT:</strong>
                  <?php echo $order['payment_method'] == 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản ngân hàng'; ?>
               </p>
               <?php if(!empty($order['note'])): ?>
               <p class="mb-1"><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['note']); ?></p>
               <?php endif; ?>
            </div>
            <div class="card-footer">
               <div class="d-flex justify-content-between fw-bold fs-5">
                  <span>Tổng cộng</span>
                  <span
                     style="color: var(--accent-color);"><?php echo number_format($order['order_total'], 0, ',', '.'); ?>đ</span>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php else: // Nếu không tìm thấy đơn hàng ?>
   <div class="text-center py-5">
      <i class="bi bi-x-circle" style="font-size: 5rem; color: var(--danger);"></i>
      <h3 class="mt-3">Không tìm thấy đơn hàng</h3>
      <p class="text-muted">Đơn hàng bạn yêu cầu không tồn tại hoặc không thuộc về tài khoản của bạn.</p>
      <a href="account.php" class="btn btn-primary mt-3">Quay lại Lịch sử đơn hàng</a>
   </div>
   <?php endif; ?>
</div>


<?php 
// Nạp footer
include_once __DIR__ . '/includes/footer.php'; 
?>
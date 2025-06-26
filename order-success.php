<?php
// Nạp header
include_once __DIR__ . '/includes/header.php';

// Lấy mã đơn hàng từ URL để hiển thị
$order_id = isset($_GET['order_id']) ? htmlspecialchars($_GET['order_id']) : 'của bạn';
?>

<div class="container my-5 text-center">
   <div class="py-5">
      <i class="bi bi-check-circle-fill text-success" style="font-size: 6rem;"></i>
      <h1 class="mt-4" style="font-family: var(--font-heading);">Đặt hàng thành công!</h1>
      <p class="lead text-muted">Cảm ơn bạn đã tin tưởng và mua sắm tại Khóm Bedding.</p>
      <p>Mã đơn hàng của bạn là: <strong style="color: var(--accent-color);">#<?php echo $order_id; ?></strong></p>
      <p>Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng trong thời gian sớm nhất. Bạn cũng có thể kiểm tra email để
         xem lại chi tiết đơn hàng.</p>
      <div class="mt-5">
         <a href="index.php" class="btn btn-primary btn-lg">Tiếp tục mua sắm</a>
         <a href="account.php?view=orders" class="btn btn-outline-secondary btn-lg ms-2">Xem lịch sử đơn hàng</a>
      </div>
   </div>
</div>

<?php
// Nạp footer
include_once __DIR__ . '/includes/footer.php';
?>
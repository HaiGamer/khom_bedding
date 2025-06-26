<?php
// LẤY DỮ LIỆU ĐƠN HÀNG CỦA NGƯỜI DÙNG
$user_id = $_SESSION['user_id'];
$orders = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>
<h1 class="mb-4" style="font-family: var(--font-heading);">Lịch sử đơn hàng</h1>

<?php if (empty($orders)): ?>
<div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
<?php else: ?>
<div class="table-responsive">
   <table class="table table-hover align-middle">
      <thead>
         <tr>
            <th>Mã ĐH</th>
            <th>Ngày đặt</th>
            <th class="text-end">Tổng tiền</th>
            <th class="text-center">Trạng thái</th>
            <th></th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($orders as $order): ?>
         <tr>
            <td class="fw-bold">#<?php echo $order['id']; ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
            <td class="text-end"><?php echo number_format($order['order_total'], 0, ',', '.'); ?>đ</td>
            <td class="text-center"><?php echo format_order_status($order['status']); // Sử dụng lại hàm format cũ ?>
            </td>
            <td class="text-end">
               <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">Xem chi
                  tiết</a>
            </td>
         </tr>
         <?php endforeach; ?>
      </tbody>
   </table>
</div>
<?php endif; ?>
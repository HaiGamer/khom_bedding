<?php 
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LOGIC MỚI: LẤY DỮ LIỆU CHO DASHBOARD ---

try {
    // 1. Lấy tổng doanh thu từ các đơn hàng đã hoàn thành
    $stmt_revenue = $pdo->prepare("SELECT SUM(order_total) AS total_revenue FROM orders WHERE status = 'completed'");
    $stmt_revenue->execute();
    $total_revenue = $stmt_revenue->fetchColumn();

    // 2. Đếm số đơn hàng đang chờ xử lý
    $stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stmt_pending->execute();
    $pending_orders_count = $stmt_pending->fetchColumn();
    
    // 3. Đếm tổng số khách hàng (không tính admin)
    $stmt_users = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stmt_users->execute();
    $total_customers = $stmt_users->fetchColumn();

    // 4. Lấy 5 đơn hàng gần đây nhất
    $stmt_recent_orders = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    $stmt_recent_orders->execute();
    $recent_orders = $stmt_recent_orders->fetchAll();

} catch (PDOException $e) {
    die("Lỗi truy vấn CSDL: " . $e->getMessage());
}

// Hàm format trạng thái (mượn từ orders.php)
function format_order_status_dashboard($status) {
    switch ($status) {
        case 'pending': return '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
        case 'processing': return '<span class="badge bg-info text-dark">Đang xử lý</span>';
        case 'shipped': return '<span class="badge bg-primary">Đang giao</span>';
        case 'completed': return '<span class="badge bg-success">Hoàn thành</span>';
        case 'cancelled': return '<span class="badge bg-danger">Đã hủy</span>';
        default: return '<span class="badge bg-secondary">Không xác định</span>';
    }
}
?>

<h1 class="mb-4">Dashboard</h1>
<p>Chào mừng bạn đến với trang quản trị của Khóm Bedding!</p>

<div class="row">
   <div class="col-lg-4 col-md-6 mb-4">
      <div class="card text-white bg-success h-100">
         <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
               <div>
                  <h5 class="card-title">Tổng Doanh thu</h5>
                  <p class="card-text fs-3 fw-bold"><?php echo number_format($total_revenue ?? 0, 0, ',', '.'); ?>đ</p>
               </div>
               <i class="bi bi-cash-coin" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
         </div>
      </div>
   </div>
   <div class="col-lg-4 col-md-6 mb-4">
      <div class="card text-white bg-warning h-100">
         <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
               <div>
                  <h5 class="card-title">Đơn hàng chờ xử lý</h5>
                  <p class="card-text fs-3 fw-bold"><?php echo $pending_orders_count ?? 0; ?></p>
               </div>
               <i class="bi bi-hourglass-split" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
         </div>
      </div>
   </div>
   <div class="col-lg-4 col-md-6 mb-4">
      <div class="card text-white bg-info h-100">
         <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
               <div>
                  <h5 class="card-title">Tổng số khách hàng</h5>
                  <p class="card-text fs-3 fw-bold"><?php echo $total_customers ?? 0; ?></p>
               </div>
               <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="card mt-4">
   <div class="card-header">
      <h5 class="mb-0">Đơn hàng gần đây</h5>
   </div>
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-hover align-middle">
            <thead>
               <tr>
                  <th>Mã ĐH</th>
                  <th>Khách hàng</th>
                  <th class="text-end">Tổng tiền</th>
                  <th class="text-center">Trạng thái</th>
                  <th class="text-end">Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php if (empty($recent_orders)): ?>
               <tr>
                  <td colspan="5" class="text-center">Chưa có đơn hàng nào.</td>
               </tr>
               <?php else: ?>
               <?php foreach ($recent_orders as $order): ?>
               <tr>
                  <td class="fw-bold">#<?php echo $order['id']; ?></td>
                  <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                  <td class="text-end"><?php echo number_format($order['order_total'], 0, ',', '.'); ?>đ</td>
                  <td class="text-center"><?php echo format_order_status_dashboard($order['status']); ?></td>
                  <td class="text-end">
                     <a href="orders.php?action=view&id=<?php echo $order['id']; ?>"
                        class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye-fill"></i> Xem
                     </a>
                  </td>
               </tr>
               <?php endforeach; ?>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>
</div>


<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
<?php 
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LOGIC MỚI: LẤY DỮ LIỆU CHO DASHBOARD ---
try {
    // 1. Các thống kê cơ bản (giữ nguyên)
    $stmt_revenue = $pdo->prepare("SELECT SUM(order_total) FROM orders WHERE status = 'completed'");
    $stmt_revenue->execute();
    $total_revenue = $stmt_revenue->fetchColumn();

    $stmt_pending = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stmt_pending->execute();
    $pending_orders_count = $stmt_pending->fetchColumn();
    
    $stmt_users = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stmt_users->execute();
    $total_customers = $stmt_users->fetchColumn();

    // 2. Lấy dữ liệu doanh thu 7 ngày gần nhất cho biểu đồ
    $sales_data = $pdo->query("
        SELECT 
            DATE(created_at) as sale_date, 
            SUM(order_total) as daily_revenue
        FROM orders
        WHERE status = 'completed' AND created_at >= CURDATE() - INTERVAL 7 DAY
        GROUP BY DATE(created_at)
        ORDER BY sale_date ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Chuẩn bị dữ liệu cho Chart.js
    $chart_labels = [];
    $chart_values = [];
    // Tạo một mảng ngày 7 ngày qua để đảm bảo ngày nào không có doanh thu thì giá trị là 0
    $period = new DatePeriod(
         new DateTime('-7 days'),
         new DateInterval('P1D'),
         new DateTime('+1 day')
    );
    $dates = [];
    foreach ($period as $key => $value) {
        $dates[$value->format('Y-m-d')] = 0;
    }
    foreach ($sales_data as $data) {
        $dates[$data['sale_date']] = $data['daily_revenue'];
    }
    foreach ($dates as $date => $revenue) {
        $chart_labels[] = date('d/m', strtotime($date));
        $chart_values[] = $revenue;
    }


    // 3. Lấy 5 sản phẩm bán chạy nhất
    $stmt_top_products = $pdo->query("
        SELECT p.id, p.name, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.id
        JOIN products p ON pv.product_id = p.id
        GROUP BY p.id, p.name
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    // 4. Lấy 5 đơn hàng gần đây nhất
    $stmt_recent_orders = $pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
    $stmt_recent_orders->execute();
    $recent_orders = $stmt_recent_orders->fetchAll();

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

    $top_products = $stmt_top_products->fetchAll();

} catch (PDOException $e) {
    die("Lỗi truy vấn CSDL: " . $e->getMessage());
}
?>

<h1 class="mb-4">Dashboard</h1>

<div class="row">
</div>

<div class="row mt-4">
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Doanh thu 7 ngày qua</h5>
         </div>
         <div class="card-body">
            <canvas id="revenueChart"></canvas>
         </div>
      </div>
   </div>
   <div class="col-lg-4">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Top 5 sản phẩm bán chạy</h5>
         </div>
         <div class="card-body">
            <ul class="list-group list-group-flush">
               <?php if(empty($top_products)): ?>
               <li class="list-group-item">Chưa có dữ liệu.</li>
               <?php else: ?>
               <?php foreach($top_products as $product): ?>
               <li class="list-group-item d-flex justify-content-between align-items-center">
                  <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="text-dark text-decoration-none">
                     <?php echo htmlspecialchars($product['name']); ?>
                  </a>
                  <span class="badge bg-primary rounded-pill"><?php echo $product['total_sold']; ?></span>
               </li>
               <?php endforeach; ?>
               <?php endif; ?>
            </ul>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
   const ctx = document.getElementById('revenueChart');
   if (ctx) {
      new Chart(ctx, {
         type: 'line', // Loại biểu đồ đường
         data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
               label: 'Doanh thu (VNĐ)',
               data: <?php echo json_encode($chart_values); ?>,
               fill: true,
               borderColor: 'rgb(75, 192, 192)',
               tension: 0.1,
               backgroundColor: 'rgba(75, 192, 192, 0.2)',
            }]
         },
         options: {
            scales: {
               y: {
                  beginAtZero: true
               }
            }
         }
      });
   }
});
</script>


<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
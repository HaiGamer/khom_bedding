<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Chi tiết Khách hàng</h1>
   <a href="customers.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
</div>

<div class="row">
   <div class="col-lg-4">
      <div class="card">
         <div class="card-body text-center">
            <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
            <h4 class="card-title mt-3"><?php echo htmlspecialchars($customer['full_name']); ?></h4>
            <p class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></p>
            <p class="text-muted"><?php echo htmlspecialchars($customer['email']); ?></p>
            <p>
               <?php if($customer['role'] == 'admin'): ?>
               <span class="badge bg-danger">Admin</span>
               <?php else: ?>
               <span class="badge bg-secondary">User</span>
               <?php endif; ?>
            </p>
            <p class="small text-muted">Tham gia ngày: <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?>
            </p>
         </div>
      </div>
   </div>
   <div class="col-lg-8">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Lịch sử đơn hàng (<?php echo count($orders); ?> đơn)</h5>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-sm table-hover align-middle">
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
                     <?php if (empty($orders)): ?>
                     <tr>
                        <td colspan="5" class="text-center text-muted">Khách hàng này chưa có đơn hàng nào.</td>
                     </tr>
                     <?php else: ?>
                     <?php foreach ($orders as $order): ?>
                     <tr>
                        <td class="fw-bold">#<?php echo $order['id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td class="text-end"><?php echo number_format($order['order_total'], 0, ',', '.'); ?>đ</td>
                        <td class="text-center"><?php echo format_order_status_admin($order['status']); ?></td>
                        <td class="text-end"><a href="orders.php?action=view&id=<?php echo $order['id']; ?>"
                              class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                     </tr>
                     <?php endforeach; ?>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
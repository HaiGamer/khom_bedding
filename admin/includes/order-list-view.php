<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Quản lý Đơn hàng</h1>
</div>

<div class="card">
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-striped table-hover align-middle">
            <thead>
               <tr>
                  <th>Mã ĐH</th>
                  <th>Tên khách hàng</th>
                  <th>Ngày đặt</th>
                  <th class="text-end">Tổng tiền</th>
                  <th class="text-center">Trạng thái</th>
                  <th class="text-center">Thanh toán</th>
                  <th class="text-end">Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php if (empty($orders)): ?>
               <tr>
                  <td colspan="7" class="text-center">Chưa có đơn hàng nào.</td>
               </tr>
               <?php else: ?>
               <?php foreach ($orders as $order): ?>
               <tr>
                  <td class="fw-bold"><a href="orders.php?action=view&id=<?php echo $order['id']; ?>"
                        class="fw-bold btn btn-sm btn-outline-primary">#<?php echo $order['id']; ?></a></td>
                  <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                  <td class="text-end"><?php echo number_format($order['order_total'], 0, ',', '.'); ?>đ</td>
                  <td class="text-center"><?php echo format_order_status_admin($order['status']); ?></td>
                  <td class="text-center"><?php echo strtoupper($order['payment_method']); ?></td>
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
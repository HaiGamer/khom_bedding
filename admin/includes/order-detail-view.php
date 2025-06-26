<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Chi tiết Đơn hàng #<?php echo $order['id']; ?></h1>
   <a href="orders.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
</div>

<div class="row g-4">
   <div class="col-lg-4">
      <div class="card mb-4">
         <div class="card-header">
            <h5 class="mb-0">Thông tin chung</h5>
         </div>
         <div class="card-body">
            <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
            <p><strong>SDT:</strong>
               <a href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $order['customer_phone'])); ?>"
                  class="fw-bold text-danger text-decoration-none">
                  <?php echo htmlspecialchars($order['customer_phone']); ?>
               </a>
            </p>
            <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
            <p><strong>Tổng tiền:</strong> <span
                  class="fw-bold text-danger"><?php echo number_format($order['order_total'], 0, ',', '.'); ?>đ</span>
            </p>
            <p><strong>Thanh toán:</strong> <?php echo strtoupper($order['payment_method']); ?></p>
            <p><strong>Trạng thái hiện tại:</strong> <?php echo format_order_status_admin($order['status']); ?></p>
         </div>
      </div>
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Cập nhật trạng thái</h5>
         </div>
         <div class="card-body">
            <form action="orders.php" method="POST">
               <input type="hidden" name="action" value="update_status">
               <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
               <div class="mb-3">
                  <label for="status" class="form-label">Trạng thái mới</label>
                  <select name="status" id="status" class="form-select">
                     <option value="pending" <?php if($order['status'] == 'pending') echo 'selected'; ?>>Chờ xử lý
                     </option>
                     <option value="processing" <?php if($order['status'] == 'processing') echo 'selected'; ?>>Đang xử
                        lý</option>
                     <option value="shipped" <?php if($order['status'] == 'shipped') echo 'selected'; ?>>Đang giao
                     </option>
                     <option value="completed" <?php if($order['status'] == 'completed') echo 'selected'; ?>>Hoàn thành
                     </option>
                     <option value="cancelled" <?php if($order['status'] == 'cancelled') echo 'selected'; ?>>Đã hủy
                     </option>
                  </select>
               </div>
               <button type="submit" class="btn btn-primary">Cập nhật Trạng thái</button>
            </form>
         </div>
      </div>
   </div>

   <div class="col-lg-8">
      <div class="card mb-4">
         <div class="card-header">
            <h5 class="mb-0">Các sản phẩm trong đơn</h5>
         </div>
         <div class="card-body">
            <table class="table align-middle">
               <tbody>
                  <?php foreach ($order_items as $item): ?>
                  <tr>
                     <td style="width: 80px;"><img
                           src="<?php echo BASE_URL . htmlspecialchars($item['image_url'] ?? 'assets/images/placeholder.png'); ?>"
                           class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['product_name']); ?>"></td>
                     <td>
                        <?php echo htmlspecialchars($item['product_name']); ?>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['attributes_string']); ?></p>
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
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0">Thông tin giao hàng</h5>
         </div>
         <div class="card-body">
            <form action="orders.php" method="POST">
               <input type="hidden" name="action" value="update_shipping">
               <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
               <div class="mb-3">
                  <label for="customer_name" class="form-label d-flex justify-content-between">
                     <span>Họ và tên</span>
                     <button type="button" class="btn btn-sm btn-outline-secondary copy-btn"
                        data-target="#customer_name" title="Copy"><i class="bi bi-clipboard"></i></button>
                  </label>
                  <input type="text" class="form-control" id="customer_name" name="customer_name"
                     value="<?php echo htmlspecialchars($order['customer_name']); ?>" required>
               </div>
               <div class="row">
                  <div class="col-md-7 mb-3">
                     <label for="customer_email" class="form-label">Email</label>
                     <input type="email" class="form-control" id="customer_email" name="customer_email"
                        value="<?php echo htmlspecialchars($order['customer_email']); ?>" required>
                  </div>
                  <div class="col-md-5 mb-3">
                     <label for="customer_phone" class="form-label d-flex justify-content-between">
                        <span>Số điện thoại</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary copy-btn"
                           data-target="#customer_phone" title="Copy"><i class="bi bi-clipboard"></i></button>
                     </label>
                     <input type="tel" class="form-control" id="customer_phone" name="customer_phone"
                        value="<?php echo htmlspecialchars($order['customer_phone']); ?>" required>
                  </div>
               </div>
               <div class="mb-3">
                  <label for="customer_address" class="form-label d-flex justify-content-between">
                     <span>Địa chỉ</span>
                     <button type="button" class="btn btn-sm btn-outline-secondary copy-btn"
                        data-target="#customer_address" title="Copy"><i class="bi bi-clipboard"></i></button>
                  </label>
                  <textarea class="form-control" id="customer_address" name="customer_address" rows="3"
                     required><?php echo htmlspecialchars($order['customer_address']); ?></textarea>
               </div>
               <div class="mb-3">
                  <label for="note" class="form-label d-flex justify-content-between">
                     <span>Ghi chú</span>
                     <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" data-target="#note"
                        title="Copy"><i class="bi bi-clipboard"></i></button>
                  </label>
                  <textarea class="form-control" id="note" name="note"
                     rows="2"><?php echo htmlspecialchars($order['note']); ?></textarea>
               </div>
               <div class="mb-3">
                  <label for="order_total" class="form-label d-flex justify-content-between">
                     <span>Tổng tiền đơn hàng (VNĐ)</span>
                     <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" data-target="#order_total"
                        title="Copy"><i class="bi bi-clipboard"></i></button>
                  </label>
                  <input type="number" class="form-control" id="order_total" name="order_total"
                     value="<?php echo (int)$order['order_total']; ?>" required>
               </div>
               <button type="submit" class="btn btn-success">Lưu Thông tin Giao hàng</button>
            </form>
         </div>
      </div>
   </div>
</div>
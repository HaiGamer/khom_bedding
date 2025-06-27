<div class="d-flex justify-content-between align-items-center mb-4 print-hide">
   <a href="export.php?action=history" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại lịch sử</a>
   <button class="btn btn-primary" onclick="window.print();"><i class="bi bi-printer-fill"></i> In Phiếu</button>
</div>

<div class="card">
   <div class="card-body">
      <div class="row mb-4">
         <div class="col-6">
            <h2 class="mb-0" style="font-family: var(--font-heading);">Khóm Bedding</h2>
            <p class="text-muted">Nâng tầm phòng ngủ</p>
         </div>
         <div class="col-6 text-end">
            <h4>PHIẾU XUẤT KHO</h4>
            <div><strong>Mã phiếu:</strong> <?php echo htmlspecialchars($export['export_code']); ?></div>
            <div><strong>Ngày xuất:</strong> <?php echo date('d/m/Y H:i', strtotime($export['created_at'])); ?></div>
            <div><strong>Người xuất:</strong> <?php echo htmlspecialchars($export['user_name']); ?></div>
         </div>
      </div>

      <div class="table-responsive">
         <table class="table table-bordered">
            <thead class="table-light">
               <tr>
                  <th class="text-center">STT</th>
                  <th>Tên sản phẩm / Phiên bản</th>
                  <th>SKU</th>
                  <th class="text-center">Số lượng</th>
                  <th class="text-end">Đơn giá</th>
                  <th class="text-end">Thành tiền</th>
               </tr>
            </thead>
            <tbody>
               <?php 
                    $total_value = 0;
                    foreach ($export_items as $index => $item): 
                        $sub_total = $item['quantity'] * $item['price_at_export'];
                        $total_value += $sub_total;
                    ?>
               <tr>
                  <td class="text-center"><?php echo $index + 1; ?></td>
                  <td>
                     <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                     <small class="text-muted"><?php echo htmlspecialchars($item['variant_attributes']); ?></small>
                  </td>
                  <td><?php echo htmlspecialchars($item['sku']); ?></td>
                  <td class="text-center"><?php echo $item['quantity']; ?></td>
                  <td class="text-end"><?php echo number_format($item['price_at_export'], 0, ',', '.'); ?>đ</td>
                  <td class="text-end"><?php echo number_format($sub_total, 0, ',', '.'); ?>đ</td>
               </tr>
               <?php endforeach; ?>
            </tbody>
            <tfoot>
               <tr class="fw-bold">
                  <td colspan="5" class="text-end">Tổng giá trị xuất kho:</td>
                  <td class="text-end text-danger fs-5"><?php echo number_format($total_value, 0, ',', '.'); ?>đ</td>
               </tr>
            </tfoot>
         </table>
      </div>

      <?php if(!empty($export['note'])): ?>
      <div class="mt-4">
         <strong>Ghi chú:</strong>
         <p class="text-muted border p-2 rounded"><?php echo nl2br(htmlspecialchars($export['note'])); ?></p>
      </div>
      <?php endif; ?>

      <div class="row text-center mt-5">
         <div class="col-6">
            <strong>Người xuất kho</strong><br>
            <small>(Ký, ghi rõ họ tên)</small>
         </div>
         <div class="col-6">
            <strong>Người nhận hàng</strong><br>
            <small>(Ký, ghi rõ họ tên)</small>
         </div>
      </div>
   </div>
</div>
<h1 class="mb-4">Tạo Phiếu Xuất Kho</h1>

<form action="export.php" method="POST">
   <input type="hidden" name="action" value="create_export">
   <div class="row">
      <div class="col-lg-5">
         <div class="card">
            <div class="card-header">
               <h5 class="mb-0">Thêm sản phẩm vào phiếu</h5>
            </div>
            <div class="card-body">
               <label for="product-search-input" class="form-label">Tìm kiếm sản phẩm theo Tên hoặc SKU</label>
               <div class="position-relative">
                  <input type="text" id="product-search-input" class="form-control" placeholder="Bắt đầu gõ để tìm..."
                     autocomplete="off">
                  <div id="product-search-results"
                     class="search-results-dropdown position-absolute bg-white border shadow-sm mt-1 w-100"
                     style="display: none; z-index: 100;">
                  </div>
               </div>

               <hr class="my-4">
               <h6 class="text-muted">Hoặc chọn từ danh sách:</h6>
               <div class="product-list-container border rounded" style="max-height: 400px; overflow-y: auto;">
                  <table class="table table-hover table-sm">
                     <tbody>
                        <?php foreach($all_variants as $item): ?>
                        <tr class="add-item-btn cursor-pointer" data-id="<?php echo $item['id']; ?>"
                           data-name="<?php echo htmlspecialchars($item['product_name']); ?>"
                           data-attributes="<?php echo htmlspecialchars($item['variant_attributes'] ?? 'Phiên bản gốc'); ?>"
                           data-sku="<?php echo htmlspecialchars($item['sku']); ?>"
                           data-price="<?php echo $item['price']; ?>"
                           data-stock="<?php echo $item['stock_quantity']; ?>">
                           <td>
                              <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                              <small
                                 class="text-muted"><?php echo htmlspecialchars($item['variant_attributes'] ?? 'Phiên bản gốc'); ?></small>
                           </td>
                           <td class="text-end">
                              <span
                                 class="badge <?php echo $item['stock_quantity'] > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'; ?>">
                                 Tồn: <?php echo $item['stock_quantity']; ?>
                              </span>
                           </td>
                        </tr>
                        <?php endforeach; ?>
                     </tbody>
                  </table>
               </div>

            </div>
         </div>
      </div>
      <div class="col-lg-7">
         <div class="card">
            <div class="card-header">
               <h5 class="mb-0">Chi tiết phiếu xuất</h5>
            </div>
            <div class="card-body">
               <div class="mb-3">
                  <label for="note" class="form-label">Ghi chú (tùy chọn)</label>
                  <textarea name="note" id="note" class="form-control" rows="2"></textarea>
               </div>
               <div class="table-responsive">
                  <table class="table align-middle">
                     <thead>
                        <tr>
                           <th>Sản phẩm</th>
                           <th style="width: 120px;">Số lượng</th>
                           <th class="text-end">Đơn giá</th>
                           <th class="text-end">Thành tiền</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody id="export-items-table">
                        <tr id="no-items-row">
                           <td colspan="5" class="text-center text-muted">Chưa có sản phẩm nào.</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div class="d-flex justify-content-end mt-3">
                  <h5 class="mb-0">Tổng cộng: <span id="export-grand-total" class="text-danger fw-bold">0đ</span></h5>
               </div>
            </div>
            <div class="card-footer text-end">
               <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Hoàn tất & Xuất
                  kho</button>
            </div>
         </div>
      </div>
   </div>
</form>
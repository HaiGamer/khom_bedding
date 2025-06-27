<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Lịch sử Xuất kho</h1>
   <a href="export.php?action=create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tạo Phiếu Xuất mới</a>
</div>

<div class="card mb-4">
   <div class="card-body">
      <form action="export.php" method="GET" class="row g-3 align-items-end">
         <input type="hidden" name="action" value="history">
         <div class="col-md-5">
            <label for="search" class="form-label">Tìm theo mã phiếu:</label>
            <input type="text" class="form-control" id="search" name="search" placeholder="Nhập mã phiếu..."
               value="<?php echo htmlspecialchars($search_term); ?>">
         </div>
         <div class="col-md-3">
            <label for="from_date" class="form-label">Từ ngày:</label>
            <input type="date" class="form-control" id="from_date" name="from_date"
               value="<?php echo htmlspecialchars($from_date); ?>">
         </div>
         <div class="col-md-3">
            <label for="to_date" class="form-label">Đến ngày:</label>
            <input type="date" class="form-control" id="to_date" name="to_date"
               value="<?php echo htmlspecialchars($to_date); ?>">
         </div>
         <div class="col-md-1">
            <button type="submit" class="btn btn-success w-100">Lọc</button>
         </div>
      </form>
   </div>
</div>

<div class="d-flex justify-content-end mb-3">
   <a href="export.php?action=export_history&search=<?php echo urlencode($search_term); ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>"
      class="btn btn-success">
      <i class="bi bi-file-earmark-excel-fill"></i> Xuất Excel
   </a>
</div>

<div class="card">
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-hover align-middle">
            <thead>
               <tr>
                  <th>Mã Phiếu</th>
                  <th>Người xuất</th>
                  <th>Ngày xuất</th>
                  <th class="text-center">Số loại SP</th>
                  <th class="text-end">Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php if (empty($exports)): ?>
               <tr>
                  <td colspan="5" class="text-center text-muted">Không tìm thấy phiếu xuất nào.</td>
               </tr>
               <?php else: ?>
               <?php foreach ($exports as $export): ?>
               <tr>
                  <td class="fw-bold"><?php echo htmlspecialchars($export['export_code']); ?></td>
                  <td><?php echo htmlspecialchars($export['user_name']); ?></td>
                  <td><?php echo date('d/m/Y H:i', strtotime($export['created_at'])); ?></td>
                  <td class="text-center"><?php echo $export['item_count']; ?></td>
                  <td class="text-end">
                     <a href="export.php?action=view&id=<?php echo $export['id']; ?>"
                        class="btn btn-sm btn-outline-primary"><i class="bi bi-eye-fill"></i> Xem</a>
                  </td>
               </tr>
               <?php endforeach; ?>
               <?php endif; ?>
            </tbody>
         </table>
      </div>
   </div>

   <?php if($total_pages > 1): ?>
   <div class="card-footer">
      <nav aria-label="Page navigation">
         <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>"><a class="page-link"
                  href="?action=history&search=<?php echo urlencode($search_term); ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&page=<?php echo $page - 1; ?>">Previous</a>
            </li>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link"
                  href="?action=history&search=<?php echo urlencode($search_term); ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>"><a class="page-link"
                  href="?action=history&search=<?php echo urlencode($search_term); ?>&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&page=<?php echo $page + 1; ?>">Next</a>
            </li>
         </ul>
      </nav>
   </div>
   <?php endif; ?>
</div>
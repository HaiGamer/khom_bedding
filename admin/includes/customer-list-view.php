<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Quản lý Khách hàng</h1>
</div>

<div class="card mb-4">
   <div class="card-body">
      <form action="customers.php" method="GET" class="d-flex">
         <input type="text" class="form-control me-2" name="search" placeholder="Tìm theo tên hoặc email..."
            value="<?php echo htmlspecialchars($search_term); ?>">
         <button type="submit" class="btn btn-success">Tìm</button>
      </form>
   </div>
</div>

<div class="card">
   <div class="card-body">
      <div class="table-responsive">
         <table class="table table-striped table-hover align-middle">
            <thead>
               <tr>
                  <th>ID</th>
                  <th>Họ và tên</th>
                  <th>Email</th>
                  <th class="text-center">Vai trò</th>
                  <th>Ngày đăng ký</th>
                  <th class="text-end">Hành động</th>
               </tr>
            </thead>
            <tbody>
               <?php if (empty($customers)): ?>
               <tr>
                  <td colspan="6" class="text-center">Không tìm thấy khách hàng nào.</td>
               </tr>
               <?php else: ?>
               <?php foreach ($customers as $customer): ?>
               <tr>
                  <td class="fw-bold"><?php echo $customer['id']; ?></td>
                  <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($customer['email']); ?></td>
                  <td class="text-center">
                     <?php if($customer['role'] == 'admin'): ?>
                     <span class="badge bg-danger">Admin</span>
                     <?php else: ?>
                     <span class="badge bg-secondary">User</span>
                     <?php endif; ?>
                  </td>
                  <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                  <td class="text-end">
                     <a href="customers.php?action=view&id=<?php echo $customer['id']; ?>"
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

   <?php if($total_pages > 1): ?>
   <div class="card-footer">
      <nav aria-label="Page navigation">
         <ul class="pagination justify-content-center mb-0">
            <li class="page-item <?php if($page <= 1) echo 'disabled'; ?>"><a class="page-link"
                  href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $page - 1; ?>">Previous</a></li>
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link"
                  href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
            <li class="page-item <?php if($page >= $total_pages) echo 'disabled'; ?>"><a class="page-link"
                  href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $page + 1; ?>">Next</a></li>
         </ul>
      </nav>
   </div>
   <?php endif; ?>
</div>
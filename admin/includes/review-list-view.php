<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Quản lý Đánh giá</h1>
</div>

<?php if(isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>

<div class="card">
   <div class="card-body">
      <table class="table table-hover align-middle">
         <thead>
            <tr>
               <th>ID</th>
               <th>Sản phẩm</th>
               <th>Người đánh giá</th>
               <th class="text-center">Xếp hạng</th>
               <th>Bình luận</th>
               <th>Ngày tạo</th>
               <th class="text-end">Hành động</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($reviews as $review): ?>
            <tr>
               <td class="fw-bold"><?php echo $review['id']; ?></td>
               <td><a href="<?php echo BASE_URL . 'product-detail.php?slug=' . $review['product_slug']; ?>"
                     target="_blank"><?php echo htmlspecialchars($review['product_name']); ?></a></td>
               <td><?php echo htmlspecialchars($review['user_name']); ?></td>
               <td class="text-center text-nowrap">
                  <?php for($i = 1; $i <= 5; $i++): ?>
                  <i class="bi <?php echo ($i <= $review['rating']) ? 'bi-star-fill text-warning' : 'bi-star'; ?>"></i>
                  <?php endfor; ?>
               </td>
               <td><?php echo htmlspecialchars(mb_strimwidth($review['comment'], 0, 50, "...")); ?></td>
               <td><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></td>
               <td class="text-end">
                  <a href="reviews.php?action=edit&id=<?php echo $review['id']; ?>"
                     class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i> Sửa</a>
                  <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');"><i
                        class="bi bi-trash-fill"></i> Xóa</a>
               </td>
            </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>
</div>
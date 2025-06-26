<h1 class="mb-4">Chỉnh sửa Đánh giá</h1>
<div class="card">
   <div class="card-body">
      <form action="reviews.php" method="POST">
         <input type="hidden" name="action" value="edit_review">
         <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">

         <div class="mb-3">
            <strong>Sản phẩm:</strong> <a
               href="<?php echo BASE_URL . 'product-detail.php?slug=' . $review['product_slug']; ?>"
               target="_blank"><?php echo htmlspecialchars($review['product_name']); ?></a><br>
            <strong>Người đánh giá:</strong> <?php echo htmlspecialchars($review['user_name']); ?>
         </div>

         <div class="mb-3">
            <label for="rating" class="form-label">Xếp hạng</label>
            <select name="rating" id="rating" class="form-select" style="width: auto;">
               <?php for($i = 5; $i >= 1; $i--): ?>
               <option value="<?php echo $i; ?>" <?php if($i == $review['rating']) echo 'selected'; ?>><?php echo $i; ?>
                  Sao</option>
               <?php endfor; ?>
            </select>
         </div>

         <div class="mb-3">
            <label for="comment" class="form-label">Bình luận</label>
            <textarea class="form-control" id="comment" name="comment" rows="5"
               required><?php echo htmlspecialchars($review['comment']); ?></textarea>
         </div>

         <button type="submit" class="btn btn-success">Lưu thay đổi</button>
         <a href="reviews.php" class="btn btn-secondary">Quay lại</a>
      </form>
   </div>
</div>
<h1 class="mb-4"><?php echo $is_editing ? 'Chỉnh sửa Danh mục' : 'Thêm Danh mục mới'; ?></h1>
<div class="card">
   <div class="card-body">
      <form action="categories.php" method="POST">
         <?php if ($is_editing): ?>
         <input type="hidden" name="action" value="edit_category">
         <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
         <?php else: ?>
         <input type="hidden" name="action" value="add_category">
         <?php endif; ?>

         <div class="mb-3">
            <label for="name" class="form-label">Tên Danh mục</label>
            <input type="text" class="form-control" id="name" name="name"
               value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required>
         </div>
         <div class="mb-3">
            <label for="description" class="form-label">Mô tả (tùy chọn)</label>
            <textarea class="form-control" id="description" name="description"
               rows="4"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
         </div>
         <button type="submit" class="btn btn-success">Lưu Danh mục</button>
         <a href="categories.php" class="btn btn-secondary">Quay lại</a>
      </form>
   </div>
</div>
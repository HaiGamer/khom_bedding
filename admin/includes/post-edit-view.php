<h1 class="mb-4">Chỉnh sửa bài viết</h1>
<div class="card">
   <div class="card-body">
      <form action="posts.php" method="POST" enctype="multipart/form-data">
         <input type="hidden" name="action" value="edit_post">
         <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

         <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="title"
               value="<?php echo htmlspecialchars($post['title']); ?>" required>
         </div>
         <div class="mb-3">
            <label for="excerpt" class="form-label">Mô tả ngắn (Excerpt)</label>
            <textarea class="form-control" id="excerpt" name="excerpt"
               rows="3"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
         </div>
         <div class="mb-3">
            <label for="content-editor" class="form-label">Nội dung</label>
            <textarea class="form-control" id="content-editor" name="content"
               rows="10"><?php echo htmlspecialchars($post['content']); ?></textarea>
         </div>
         <div class="mb-3">
            <label for="featured_image" class="form-label">Thay đổi ảnh đại diện</label>
            <input class="form-control" type="file" id="featured_image" name="featured_image">
            <?php if(!empty($post['featured_image'])): ?>
            <div class="mt-2">
               <small>Ảnh hiện tại:</small><br>
               <div class="admin-thumbnail-preview">
                  <img src="<?php echo BASE_URL . htmlspecialchars($post['featured_image']); ?>" alt="Ảnh hiện tại">
               </div>
            </div>
            <?php endif; ?>
         </div>
         <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-select" id="status" name="status">
               <option value="published" <?php if($post['status'] == 'published') echo 'selected'; ?>>Xuất bản</option>
               <option value="draft" <?php if($post['status'] == 'draft') echo 'selected'; ?>>Bản nháp</option>
            </select>
         </div>
         <button type="submit" class="btn btn-success">Lưu thay đổi</button>
         <a href="posts.php" class="btn btn-secondary">Quay lại</a>
      </form>
   </div>
</div>
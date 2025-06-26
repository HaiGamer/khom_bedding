<h1 class="mb-4">Thêm bài viết mới</h1>
<div class="card">
   <div class="card-body">
      <form action="posts.php" method="POST" enctype="multipart/form-data">
         <input type="hidden" name="action" value="add_post">
         <div class="mb-3">
            <label for="title" class="form-label">Tiêu đề</label>
            <input type="text" class="form-control" id="title" name="title" required>
         </div>
         <div class="mb-3">
            <label for="excerpt" class="form-label">Mô tả ngắn (Excerpt)</label>
            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"></textarea>
         </div>
         <div class="mb-3">
            <label for="content-editor" class="form-label">Nội dung</label>
            <textarea class="form-control" id="content-editor" name="content" rows="10"></textarea>
         </div>
         <div class="mb-3">
            <label for="featured_image" class="form-label">Ảnh đại diện</label>
            <input class="form-control" type="file" id="featured_image" name="featured_image">
         </div>
         <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-select" id="status" name="status">
               <option value="published" selected>Xuất bản</option>
               <option value="draft">Bản nháp</option>
            </select>
         </div>
         <button type="submit" class="btn btn-success">Lưu bài viết</button>
         <a href="posts.php" class="btn btn-secondary">Quay lại</a>
      </form>
   </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Quản lý Bài viết</h1>
   <a href="posts.php?action=add" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm bài viết mới</a>
</div>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Thao tác thành công!</div>
<?php endif; ?>

<div class="card">
   <div class="card-body">
      <table class="table table-hover align-middle">
         <thead>
            <tr>
               <th>ID</th>
               <th style="width: 100px;">Ảnh</th>
               <th>Tiêu đề</th>
               <th>Tác giả</th>
               <th class="text-center">Trạng thái</th>
               <th>Ngày tạo</th>
               <th class="text-end">Hành động</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
               <td class="fw-bold"><?php echo $post['id']; ?></td>
               <td><img
                     src="<?php echo BASE_URL . htmlspecialchars($post['featured_image'] ?? 'assets/images/placeholder.png'); ?>"
                     class="img-thumbnail" width="80"></td>
               <td><?php echo htmlspecialchars($post['title']); ?></td>
               <td><?php echo htmlspecialchars($post['author_name']); ?></td>
               <td class="text-center">
                  <?php if($post['status'] == 'published'): ?>
                  <span class="badge bg-success">Đã xuất bản</span>
                  <?php else: ?>
                  <span class="badge bg-secondary">Bản nháp</span>
                  <?php endif; ?>
               </td>
               <td><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></td>
               <td class="text-end">
                  <a href="posts.php?action=edit&id=<?php echo $post['id']; ?>"
                     class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i> Sửa</a>
                  <a href="posts.php?action=delete&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này?');"><i
                        class="bi bi-trash-fill"></i> Xóa</a>
               </td>
            </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>
</div>
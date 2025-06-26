<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Quản lý Danh mục</h1>
   <a href="categories.php?action=add" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm Danh mục mới</a>
</div>

<?php if(isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>
<?php if(isset($_GET['error'])): ?><div class="alert alert-danger">
   <?php if($_GET['error'] == 'in_use') echo 'Không thể xóa danh mục đang có sản phẩm.'; else echo 'Có lỗi xảy ra.'; ?>
</div><?php endif; ?>

<div class="card">
   <div class="card-body">
      <table class="table table-hover align-middle">
         <thead>
            <tr>
               <th>ID</th>
               <th>Tên Danh mục</th>
               <th>Slug</th>
               <th class="text-center">Số sản phẩm</th>
               <th class="text-end">Hành động</th>
            </tr>
         </thead>
         <tbody>
            <?php foreach ($categories as $category): ?>
            <tr>
               <td class="fw-bold"><?php echo $category['id']; ?></td>
               <td><?php echo htmlspecialchars($category['name']); ?></td>
               <td><?php echo htmlspecialchars($category['slug']); ?></td>
               <td class="text-center"><?php echo $category['product_count']; ?></td>
               <td class="text-end">
                  <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>"
                     class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i> Sửa</a>
                  <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');"><i
                        class="bi bi-trash-fill"></i> Xóa</a>
               </td>
            </tr>
            <?php endforeach; ?>
         </tbody>
      </table>
   </div>
</div>
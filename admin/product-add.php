<?php 
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php'; 

// Lấy dữ liệu cho các dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$attributes = $pdo->query("SELECT id, name FROM attributes ORDER BY name ASC")->fetchAll();
$attribute_values = $pdo->query("SELECT id, attribute_id, value FROM attribute_values ORDER BY value ASC")->fetchAll();
?>

<h1 class="mb-4">Thêm Sản phẩm mới</h1>

<div class="card">
   <div class="card-body">
      <form action="product-handler.php" method="POST" enctype="multipart/form-data">
         <input type="hidden" name="action" value="add_product">

         <h5 class="mb-3">Thông tin cơ bản</h5>
         <div class="row">
            <div class="col-md-8 mb-3"><label for="name" class="form-label">Tên sản phẩm</label><input type="text"
                  class="form-control" id="name" name="name" required></div>
            <div class="col-md-4 mb-3"><label for="category_id" class="form-label">Danh mục</label><select
                  class="form-select" id="category_id" name="category_id">
                  <option value="">Chọn danh mục</option><?php foreach($categories as $category): ?><option
                     value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                  <?php endforeach; ?>
               </select></div>
         </div>
         <div class="mb-3">
            <label for="short_description" class="form-label">Mô tả ngắn</label>
            <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
         </div>
         <div class="mb-3">
            <label for="description" class="form-label">Mô tả chi tiết</label>
            <textarea class="form-control" id="description-editor" name="description" rows="10"></textarea>
         </div>
         <div class="mb-3">
            <label for="gallery_images" class="form-label">Ảnh Gallery (có thể chọn nhiều ảnh)</label>
            <input class="form-control" type="file" id="gallery_images" name="gallery_images[]" multiple>
         </div>

         <hr class="my-4">

         <h5 class="mb-3">Các phiên bản sản phẩm</h5>
         <div id="variants-container"></div>
         <button type="button" id="add-variant-btn" class="btn btn-outline-success mt-2"><i
               class="bi bi-plus-circle"></i> Thêm phiên bản</button>

         <div class="mt-4">
            <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-floppy"></i> Lưu Sản phẩm</button>
            <a href="products.php" class="btn btn-secondary btn-lg">Quay lại</a>
         </div>
      </form>
   </div>
</div>

<template id="variant-template">
   <div class="variant-row border rounded p-3 mb-3 position-relative">
      <button type="button" class="btn-close remove-variant-btn position-absolute top-0 end-0 p-2"
         aria-label="Close"></button>
      <h6>Phiên bản Mới #<span class="variant-index"></span></h6>
      <div class="row">
         <div class="col-md-3 mb-3"><label class="form-label">SKU</label><input type="text" class="form-control"
               name="variants[__INDEX__][sku]" required></div>
         <div class="col-md-3 mb-3"><label class="form-label">Giá vốn (VNĐ)</label><input type="number"
               class="form-control" name="variants[__INDEX__][cost_price]" value="0" required></div>
         <div class="col-md-3 mb-3"><label class="form-label">Giá gốc (VNĐ)</label><input type="number"
               placeholder="Bỏ trống nếu không giảm giá" class="form-control"
               name="variants[__INDEX__][original_price]"></div>
         <div class="col-md-3 mb-3"><label class="form-label">Giá bán (VNĐ)</label><input type="number"
               class="form-control" name="variants[__INDEX__][price]" required></div>
         <div class="col-md-3 mb-3"><label class="form-label">Tồn kho</label><input type="number" class="form-control"
               name="variants[__INDEX__][stock]" required></div>
      </div>
      <div class="row">
         <div class="col-md-9">
            <div class="row">
               <?php foreach($attributes as $attribute): ?>
               <div class="col-md-4 mb-3">
                  <label class="form-label"><?php echo htmlspecialchars($attribute['name']); ?></label>
                  <select class="form-select" name="variants[__INDEX__][attributes][<?php echo $attribute['id']; ?>]">
                     <option value="">Chọn <?php echo htmlspecialchars(strtolower($attribute['name'])); ?></option>
                     <?php foreach($attribute_values as $value): if($value['attribute_id'] == $attribute['id']): ?>
                     <option value="<?php echo $value['id']; ?>"><?php echo htmlspecialchars($value['value']); ?>
                     </option>
                     <?php endif; endforeach; ?>
                  </select>
               </div>
               <?php endforeach; ?>
            </div>
         </div>
         <div class="col-md-3 mb-3 d-flex align-items-center justify-content-center">
            <div class="form-check"><input class="form-check-input" type="radio" name="default_variant_index"
                  value="__INDEX__"><label class="form-check-label">Làm mặc định</label></div>
         </div>
      </div>
   </div>
</template>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
echo '<script src="'.BASE_URL.'admin/assets/js/admin.js"></script>';
?>
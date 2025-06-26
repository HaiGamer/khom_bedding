<?php 
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php'; 

// --- PHẦN LẤY DỮ LIỆU CŨ ĐỂ ĐIỀN VÀO FORM ---
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    header('Location: products.php?error=not_found');
    exit();
}
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$attributes = $pdo->query("SELECT id, name FROM attributes ORDER BY name ASC")->fetchAll();
$attribute_values = $pdo->query("SELECT id, attribute_id, value FROM attribute_values ORDER BY value ASC")->fetchAll();

$stmt_product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt_product->execute([$product_id]);
$product = $stmt_product->fetch();

if (!$product) {
    header('Location: products.php?error=not_found');
    exit();
}

$stmt_variants = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC");
$stmt_variants->execute([$product_id]);
$variants = $stmt_variants->fetchAll();

$variant_attributes = [];
if (!empty($variants)) {
    $variant_ids = array_column($variants, 'id');
    $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));
    $stmt_variant_attrs = $pdo->prepare("SELECT * FROM variant_values WHERE variant_id IN ($placeholders)");
    $stmt_variant_attrs->execute($variant_ids);
    $variant_attributes_raw = $stmt_variant_attrs->fetchAll();
    foreach ($variant_attributes_raw as $val) {
        $variant_attributes[$val['variant_id']][$val['attribute_value_id']] = true;
    }
}

$gallery_images = [];
$stmt_gallery = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_featured DESC, id ASC");
$stmt_gallery->execute([$product_id]);
$gallery_images = $stmt_gallery->fetchAll();
?>

<h1 class="mb-4">Chỉnh sửa Sản phẩm: <span class="text-primary"><?php echo htmlspecialchars($product['name']); ?></span>
</h1>

<?php if(isset($_GET['success'])): ?>
<div class="alert alert-success">Thao tác thành công!</div>
<?php elseif(isset($_GET['error'])): ?>
<div class="alert alert-danger">
   <?php 
            if ($_GET['error'] == 'last_variant') echo 'Không thể xóa phiên bản cuối cùng của sản phẩm.';
            elseif ($_GET['error'] == 'variant_in_order') echo 'Không thể xóa phiên bản đã có trong đơn hàng của khách.';
            else echo 'Có lỗi xảy ra, vui lòng thử lại.';
        ?>
</div>
<?php endif; ?>

<form action="product-handler.php" method="POST" enctype="multipart/form-data">
   <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
   <div class="row g-4">
      <div class="col-lg-8">
         <div class="card">
            <div class="card-body">
               <h5 class="mb-3">Thông tin cơ bản</h5>
               <div class="row">
                  <div class="col-md-8 mb-3"><label for="name" class="form-label">Tên sản phẩm</label><input type="text"
                        class="form-control" id="name" name="name"
                        value="<?php echo htmlspecialchars($product['name']); ?>" required></div>
                  <div class="col-md-4 mb-3"><label for="category_id" class="form-label">Danh mục</label><select
                        class="form-select" id="category_id" name="category_id">
                        <option value="">Chọn danh mục</option><?php foreach($categories as $category): ?><option
                           value="<?php echo $category['id']; ?>"
                           <?php if($category['id'] == $product['category_id']) echo 'selected'; ?>>
                           <?php echo htmlspecialchars($category['name']); ?></option><?php endforeach; ?>
                     </select></div>
               </div>
               <div class="mb-3"><label for="short_description" class="form-label">Mô tả ngắn</label><textarea
                     class="form-control" id="short_description" name="short_description"
                     rows="2"><?php echo htmlspecialchars($product['short_description']); ?></textarea></div>
               <div class="mb-3"><label for="description" class="form-label">Mô tả chi tiết</label>

                  <textarea class="form-control" id="description-editor" name="description"
                     rows="10"><?php echo htmlspecialchars($product['description']); ?></textarea>
               </div>
               <hr class="my-4">
               <h5 class="mb-3">Các phiên bản sản phẩm</h5>
               <div id="variants-container">
                  <?php foreach($variants as $index => $variant): ?>
                  <div class="variant-row border rounded p-3 mb-3 position-relative">
                     <button type="submit" name="delete_variant" value="<?php echo $variant['id']; ?>"
                        class="btn-close position-absolute top-0 end-0 p-2" aria-label="Close"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa phiên bản này?');"></button>
                     <input type="hidden" name="variants[<?php echo $index; ?>][id]"
                        value="<?php echo $variant['id']; ?>">
                     <h6>Phiên bản #<span class="variant-index"><?php echo $index + 1; ?></span></h6>
                     <div class="row">
                        <div class="col-md-3 mb-3"><label class="form-label">SKU</label><input type="text"
                              class="form-control" name="variants[<?php echo $index; ?>][sku]"
                              value="<?php echo htmlspecialchars($variant['sku']); ?>" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Giá gốc (VNĐ)</label><input type="number"
                              placeholder="Bỏ trống nếu không giảm giá" class="form-control"
                              name="variants[<?php echo $index; ?>][original_price]"
                              value="<?php echo htmlspecialchars($variant['original_price']); ?>"></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Giá bán (VNĐ)</label><input type="number"
                              class="form-control" name="variants[<?php echo $index; ?>][price]"
                              value="<?php echo htmlspecialchars($variant['price']); ?>" required></div>
                        <div class="col-md-3 mb-3"><label class="form-label">Tồn kho</label><input type="number"
                              class="form-control" name="variants[<?php echo $index; ?>][stock]"
                              value="<?php echo htmlspecialchars($variant['stock_quantity']); ?>" required></div>
                     </div>
                     <div class="row">
                        <div class="col-md-9">
                           <div class="row">
                              <?php foreach($attributes as $attribute): ?>
                              <div class="col-md-4 mb-3">
                                 <label class="form-label"><?php echo htmlspecialchars($attribute['name']); ?></label>
                                 <select class="form-select"
                                    name="variants[<?php echo $index; ?>][attributes][<?php echo $attribute['id']; ?>]">
                                    <option value="">Chọn
                                       <?php echo htmlspecialchars(strtolower($attribute['name'])); ?></option>
                                    <?php foreach($attribute_values as $value): if($value['attribute_id'] == $attribute['id']): ?>
                                    <?php $isSelected = isset($variant_attributes[$variant['id']][$value['id']]); ?>
                                    <option value="<?php echo $value['id']; ?>"
                                       <?php if($isSelected) echo 'selected'; ?>>
                                       <?php echo htmlspecialchars($value['value']); ?></option>
                                    <?php endif; endforeach; ?>
                                 </select>
                              </div>
                              <?php endforeach; ?>
                           </div>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-center justify-content-center">
                           <div class="form-check"><input class="form-check-input" type="radio"
                                 name="default_variant_index" value="<?php echo $index; ?>"
                                 <?php if($variant['is_default']) echo 'checked'; ?>><label class="form-check-label">Làm
                                 mặc định</label></div>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
               </div>
               <button type="button" id="add-variant-btn" class="btn btn-outline-success mt-2"><i
                     class="bi bi-plus-circle"></i> Thêm phiên bản</button>
               <div class="mt-4">
                  <button type="submit" name="action" value="edit_product" class="btn btn-success btn-lg"><i
                        class="bi bi-floppy"></i> Lưu Tất Cả Thay Đổi</button>
                  <a href="products.php" class="btn btn-secondary btn-lg">Quay lại</a>
               </div>
            </div>
         </div>
      </div>
      <div class="col-lg-4">
         <div class="card">
            <div class="card-header">
               <h5 class="mb-0">Quản lý Ảnh Gallery</h5>
            </div>
            <div class="card-body">
               <div class="row g-2">
                  <?php foreach($gallery_images as $image): ?>
                  <div class="col-6">
                     <div class="position-relative border p-1 rounded">
                        <img src="<?php echo BASE_URL . htmlspecialchars($image['image_url']); ?>"
                           class="img-fluid rounded">
                        <div class="position-absolute top-0 end-0 m-1"><button type="submit" name="delete_image"
                              value="<?php echo $image['id']; ?>" class="btn btn-sm btn-danger rounded-circle"
                              onclick="return confirm('Bạn có chắc chắn muốn xóa ảnh này?');"
                              style="line-height: 1; padding: 0.2rem 0.45rem;"><i class="bi bi-x-lg"></i></button></div>
                        <?php if(!$image['is_featured']): ?>
                        <div class="position-absolute bottom-0 w-100 p-1 text-center"
                           style="background: rgba(0,0,0,0.5);"><button type="submit" name="set_featured_image"
                              value="<?php echo $image['id']; ?>" class="btn btn-sm btn-light w-100"><i
                                 class="bi bi-star-fill"></i> Đặt làm đại diện</button></div>
                        <?php else: ?>
                        <div class="position-absolute bottom-0 w-100 p-1 text-center bg-success text-white"><small><i
                                 class="bi bi-star-fill"></i> Ảnh đại diện</small></div>
                        <?php endif; ?>
                     </div>
                  </div>
                  <?php endforeach; ?>
               </div>
               <hr>
               <div class="mb-3">
                  <label for="new_gallery_images" class="form-label">Thêm ảnh mới vào Gallery</label>
                  <input class="form-control" type="file" id="new_gallery_images" name="new_gallery_images[]" multiple>
               </div>
            </div>
         </div>
      </div>
   </div>
</form>
<template id="variant-template">
   <div class="variant-row border rounded p-3 mb-3 position-relative">
      <button type="button" class="btn-close remove-variant-btn position-absolute top-0 end-0 p-2"
         aria-label="Close"></button>
      <h6>Phiên bản Mới #<span class="variant-index"></span></h6>
      <div class="row">
         <div class="col-md-3 mb-3"><label class="form-label">SKU</label><input type="text" class="form-control"
               name="variants[__INDEX__][sku]" required></div>
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
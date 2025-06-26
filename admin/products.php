<?php 
require_once __DIR__ . '/auth-guard.php'; // Gọi "lính canh"
include_once __DIR__ . '/includes/header.php'; 

// --- LẤY DỮ LIỆU SẢN PHẨM ---
$products = [];
try {
    // Câu lệnh SQL này hơi phức tạp một chút, nó làm các việc sau:
    // 1. Lấy thông tin cơ bản từ bảng `products` (p)
    // 2. JOIN với `categories` (c) để lấy tên danh mục
    // 3. JOIN với `product_variants` (pv_stock) để tính tổng tồn kho (total_stock)
    // 4. Dùng subquery (câu lệnh con) để lấy giá và ảnh của phiên bản mặc định
    $sql = "
        SELECT 
            p.id, 
            p.name,
            c.name AS category_name,
            (SELECT pv.price FROM product_variants pv WHERE pv.product_id = p.id AND pv.is_default = TRUE) AS default_price,
            (SELECT pv.image_url FROM product_variants pv WHERE pv.product_id = p.id AND pv.is_default = TRUE) AS default_image,
            SUM(pv_stock.stock_quantity) AS total_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_variants pv_stock ON p.id = pv_stock.product_id
        GROUP BY p.id, p.name, c.name
        ORDER BY p.id DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
   <h1 class="mb-0">Quản lý Sản phẩm</h1>
   <a href="product-add.php" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> Thêm Sản phẩm mới
   </a>
</div>

<?php if(isset($_GET['success']) && $_GET['success'] == 'deleted'): ?>
<div class="alert alert-success">Đã xóa sản phẩm thành công.</div>
<?php elseif(isset($_GET['error']) && $_GET['error'] == 'in_order'): ?>
<div class="alert alert-danger">Không thể xóa sản phẩm đã có trong đơn hàng của khách.</div>
<?php elseif(isset($_GET['error'])): ?>
<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại.</div>
<?php endif; ?>

<div class="card">
   <div class="card-body">
      <table class="table table-striped table-hover align-middle">
         <thead>
            <tr>
               <th>ID</th>
               <th style="width: 80px;">Ảnh</th>
               <th>Tên sản phẩm</th>
               <th>Danh mục</th>
               <th class="text-end">Giá</th>
               <th class="text-center">Tồn kho</th>
               <th class="text-end">Hành động</th>
            </tr>
         </thead>
         <tbody>
            <?php if (empty($products)): ?>
            <tr>
               <td colspan="7" class="text-center">Chưa có sản phẩm nào.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($products as $product): ?>
            <tr>
               <td class="fw-bold"><?php echo $product['id']; ?></td>
               <td>
                  <img
                     src="<?php echo BASE_URL . htmlspecialchars($product['default_image'] ?? 'assets/images/placeholder.png'); ?>"
                     alt="" class="img-thumbnail" width="60">
               </td>
               <td><?php echo htmlspecialchars($product['name']); ?></td>
               <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
               <td class="text-end"><?php echo number_format($product['default_price'] ?? 0, 0, ',', '.'); ?>đ</td>
               <td class="text-center"><?php echo $product['total_stock']; ?></td>
               <td class="text-end">
                  <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-primary">
                     <i class="bi bi-pencil-fill"></i> Sửa
                  </a>
                  <a href="product-delete.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                     <i class="bi bi-trash-fill"></i> Xóa
                  </a>
               </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
         </tbody>
      </table>
   </div>
</div>


<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
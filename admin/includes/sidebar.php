<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Mảng các trang thuộc về module sản phẩm
$product_pages = ['products.php', 'categories.php', 'attributes.php'];
?>
<div class="sidebar">
   <nav class="nav flex-column p-3">
      <a class="nav-link <?php if ($current_page == 'index.php') echo 'active'; ?>"
         href="<?php echo BASE_URL; ?>admin/">
         <i class="bi bi-speedometer2 me-2"></i>Dashboard
      </a>

      <a class="nav-link dropdown-toggle <?php if (in_array($current_page, $product_pages)) echo 'active'; ?>"
         href="#productSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false"
         aria-controls="productSubmenu">
         <i class="bi bi-box-seam me-2"></i>Sản phẩm
      </a>
      <div class="collapse <?php if (in_array($current_page, $product_pages)) echo 'show'; ?>" id="productSubmenu">
         <ul class="nav flex-column ps-3">
            <li><a class="nav-link" href="<?php echo BASE_URL; ?>admin/products.php">- Tất cả sản phẩm</a></li>
            <li><a class="nav-link" href="<?php echo BASE_URL; ?>admin/categories.php">- Danh mục</a></li>
            <li><a class="nav-link" href="<?php echo BASE_URL; ?>admin/attributes.php">- Thuộc tính</a></li>
            <li><a class="nav-link" href="<?php echo BASE_URL; ?>admin/reviews.php">- Đánh giá</a></li>
         </ul>
      </div>

      <a class="nav-link <?php if ($current_page == 'orders.php') echo 'active'; ?>"
         href="<?php echo BASE_URL; ?>admin/orders.php">
         <i class="bi bi-receipt me-2"></i>Đơn hàng
      </a>
      <a class="nav-link <?php if ($current_page == 'posts.php') echo 'active'; ?>"
         href="<?php echo BASE_URL; ?>admin/posts.php">
         <i class="bi bi-pencil-square me-2"></i>Bài viết Blog
      </a>
      <a class="nav-link <?php if ($current_page == 'customers.php') echo 'active'; ?>"
         href="<?php echo BASE_URL; ?>admin/customers.php">
         <i class="bi bi-people me-2"></i>Khách hàng
      </a>
   </nav>
</div>
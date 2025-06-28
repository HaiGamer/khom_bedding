<?php
/**
 * Template để hiển thị một thẻ sản phẩm.
 * Yêu cầu phải có biến $product được truyền vào khi gọi file này.
 * Ví dụ: include __DIR__ . '/product-card.php';
 */
?>
<div class="col">
   <div class="card h-100 border-0 shadow-sm product-card">
      <a href="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
         <img src="<?php echo BASE_URL . htmlspecialchars($product['image_url'] ?? 'assets/images/placeholder.png'); ?>"
            class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
      </a>
      <div class="card-body text-center">
         <h5 class="card-title fs-6">
            <a href="<?php echo BASE_URL; ?>product-detail.php?slug=<?php echo htmlspecialchars($product['slug']); ?>"
               class="text-decoration-none text-dark">
               <?php echo htmlspecialchars($product['name']); ?>
            </a>
         </h5>
         <p class="card-text fw-bold" style="color: var(--accent-color);">
            <?php if (isset($product['original_price']) && $product['original_price'] > $product['price']): ?>
            <span class="me-2"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
            - <del class="price-original">
               <?php echo number_format($product['original_price'], 0, ',', '.'); ?>đ</del>
            <?php else: ?>
            <span> <?php echo number_format($product['price'], 0, ',', '.'); ?> đ</span>
            <?php endif; ?>
         </p>
      </div>
   </div>
</div>
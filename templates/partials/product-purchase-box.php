<h1 class="font-heading" style="font-family: var(--font-heading);"><?php echo htmlspecialchars($product['name']); ?>
</h1>
<div class="d-flex align-items-center mb-3 text-muted small product-meta">
   <div class="me-3">
      <span class="fw-bold text-dark"><?php echo $average_rating; ?></span>
      <?php for($i = 1; $i <= 5; $i++): ?><i
         class="bi <?php echo ($i <= $average_rating) ? 'bi-star-fill text-warning' : 'bi-star'; ?>"></i><?php endfor; ?>
   </div>
   <div class="border-start ps-3 me-3"><a href="#reviews-tab-panel" id="scroll-to-reviews"
         class="text-muted text-decoration-none"><?php echo count($reviews); ?> Đánh giá</a></div>
   <div class="border-start ps-3 me-3"><span class="fw-bold"><?php echo $sales_count; ?></span> Đã bán</div>
   <div class="border-start ps-3" id="product-sku-container" style="display: none;">Mã SP: <span
         id="product-sku"></span></div>
</div>
<div class="mb-3"><span class="fs-2 fw-bold" style="color: var(--accent-color);" id="product-price">...đ</span></div>
<?php if (!empty($product['short_description'])): ?>
<p class="lead text-muted"><?php echo nl2br(htmlspecialchars($product['short_description'])); ?></p>
<?php endif; ?>
<hr>
<div id="product-options">
   <?php foreach ($attributes_map as $name => $values): ?>
   <div class="mb-3">
      <label class="form-label fw-bold"><?php echo htmlspecialchars($name); ?>:</label>
      <div class="d-flex flex-wrap gap-2">
         <?php foreach ($values as $value): ?>
         <div class="option-item">
            <input type="radio" class="btn-check" name="option_<?php echo strtolower(str_replace(' ', '_', $name)); ?>"
               id="option_<?php echo md5($name.$value); ?>" value="<?php echo htmlspecialchars($value); ?>">
            <label class="btn btn-outline-secondary"
               for="option_<?php echo md5($name.$value); ?>"><?php echo htmlspecialchars($value); ?></label>
         </div>
         <?php endforeach; ?>
      </div>
   </div>
   <?php endforeach; ?>
</div>
<div class="mt-4">
   <div class="d-flex"><input type="number" class="form-control me-3" value="1" min="1" style="max-width: 80px;"><button
         class="btn btn-primary btn-lg flex-grow-1" id="add-to-cart-btn" disabled>Vui lòng chọn tùy chọn</button></div>
   <div id="stock-status" class="mt-2 text-muted"></div>
</div>
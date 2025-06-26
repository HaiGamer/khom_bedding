<?php if (!empty($product_images)): ?>
<div id="productImageSlider" class="carousel slide" data-bs-ride="carousel">
   <div class="carousel-inner rounded">
      <?php foreach ($product_images as $index => $image): ?>
      <div class="carousel-item <?php if ($index === 0) echo 'active'; ?>">
         <img src="<?php echo BASE_URL . htmlspecialchars($image['image_url']); ?>" class="d-block w-100"
            alt="Ảnh sản phẩm <?php echo $index + 1; ?>">
      </div>
      <?php endforeach; ?>
   </div>
   <button class="carousel-control-prev" type="button" data-bs-target="#productImageSlider" data-bs-slide="prev"><span
         class="carousel-control-prev-icon" aria-hidden="true"></span><span
         class="visually-hidden">Previous</span></button>
   <button class="carousel-control-next" type="button" data-bs-target="#productImageSlider" data-bs-slide="next"><span
         class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></button>
</div>
<div class="d-flex justify-content-center mt-3">
   <?php foreach ($product_images as $index => $image): ?>
   <button type="button" data-bs-target="#productImageSlider" data-bs-slide-to="<?php echo $index; ?>"
      class="<?php if ($index === 0) echo 'active'; ?> mx-1"
      style="background-image: url('<?php echo BASE_URL . htmlspecialchars($image['image_url']); ?>'); width: 80px; height: 80px; border: 2px solid transparent; background-size: cover;"></button>
   <?php endforeach; ?>
</div>
<?php else: ?>
<img src="<?php echo BASE_URL; ?>assets/images/placeholder.png" class="img-fluid rounded"
   alt="<?php echo htmlspecialchars($product['name']); ?>">
<?php endif; ?>
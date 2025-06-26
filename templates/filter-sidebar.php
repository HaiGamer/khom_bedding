<?php
/**
 * Template chứa nội dung của thanh bộ lọc.
 * $prefix được truyền từ file products.php để đảm bảo ID không bị trùng lặp.
 */
$prefix = $prefix ?? '';
?>

<div class="accordion" id="filterAccordion-<?php echo $prefix; ?>">

   <div class="accordion-item">
      <h2 class="accordion-header" id="headingPrice-<?php echo $prefix; ?>">
         <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapsePrice-<?php echo $prefix; ?>" aria-expanded="true">
            Giá sản phẩm
         </button>
      </h2>
      <div id="collapsePrice-<?php echo $prefix; ?>" class="accordion-collapse collapse show">
         <div class="accordion-body">
            <div class="form-check"><input class="form-check-input filter-input" type="radio" name="price_range"
                  id="<?php echo $prefix; ?>price_all" value="all" checked><label class="form-check-label"
                  for="<?php echo $prefix; ?>price_all">Tất cả</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="radio" name="price_range"
                  id="<?php echo $prefix; ?>price1" value="range1"><label class="form-check-label"
                  for="<?php echo $prefix; ?>price1">Dưới 500.000đ</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="radio" name="price_range"
                  id="<?php echo $prefix; ?>price2" value="range2"><label class="form-check-label"
                  for="<?php echo $prefix; ?>price2">500.000đ - 1.000.000đ</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="radio" name="price_range"
                  id="<?php echo $prefix; ?>price3" value="range3"><label class="form-check-label"
                  for="<?php echo $prefix; ?>price3">Trên 1.000.000đ</label></div>
         </div>
      </div>
   </div>

   <div class="accordion-item">
      <h2 class="accordion-header" id="headingSize-<?php echo $prefix; ?>">
         <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseSize-<?php echo $prefix; ?>" aria-expanded="true">
            Kích thước
         </button>
      </h2>
      <div id="collapseSize-<?php echo $prefix; ?>" class="accordion-collapse collapse show">
         <div class="accordion-body">
            <div class="form-check"><input class="form-check-input filter-input" type="checkbox" name="size[]"
                  value="1m6x2m" id="<?php echo $prefix; ?>size1"><label class="form-check-label"
                  for="<?php echo $prefix; ?>size1">1m6 x 2m</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="checkbox" name="size[]"
                  value="1m8x2m" id="<?php echo $prefix; ?>size2"><label class="form-check-label"
                  for="<?php echo $prefix; ?>size2">1m8 x 2m</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="checkbox" name="size[]"
                  value="2mx2m2" id="<?php echo $prefix; ?>size3"><label class="form-check-label"
                  for="<?php echo $prefix; ?>size3">2m x 2m2</label></div>
         </div>
      </div>
   </div>

   <div class="accordion-item">
      <h2 class="accordion-header" id="headingMaterial-<?php echo $prefix; ?>">
         <button class="accordion-button" type="button" data-bs-toggle="collapse"
            data-bs-target="#collapseMaterial-<?php echo $prefix; ?>" aria-expanded="true">
            Chất liệu
         </button>
      </h2>
      <div id="collapseMaterial-<?php echo $prefix; ?>" class="accordion-collapse collapse show">
         <div class="accordion-body">
            <div class="form-check"><input class="form-check-input filter-input" type="checkbox" name="material[]"
                  value="cotton" id="<?php echo $prefix; ?>material1"><label class="form-check-label"
                  for="<?php echo $prefix; ?>material1">Cotton</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="checkbox" name="material[]"
                  value="tencel" id="<?php echo $prefix; ?>material2"><label class="form-check-label"
                  for="<?php echo $prefix; ?>material2">Tencel</label></div>
            <div class="form-check"><input class="form-check-input filter-input" type="checkbox" name="material[]"
                  value="lua" id="<?php echo $prefix; ?>material3"><label class="form-check-label"
                  for="<?php echo $prefix; ?>material3">Lụa</label></div>
         </div>
      </div>
   </div>

</div>
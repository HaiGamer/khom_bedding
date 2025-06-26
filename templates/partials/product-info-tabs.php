<ul class="nav nav-tabs" id="productInfoTab" role="tablist">
   <li class="nav-item" role="presentation"><button class="nav-link active" id="description-tab-button"
         data-bs-toggle="tab" data-bs-target="#description-tab-panel" type="button" role="tab">Mô tả chi tiết</button>
   </li>
   <li class="nav-item" role="presentation"><button class="nav-link" id="reviews-tab-button" data-bs-toggle="tab"
         data-bs-target="#reviews-tab-panel" type="button" role="tab">Đánh giá (<?php echo count($reviews); ?>)</button>
   </li>
</ul>
<div class="tab-content border border-top-0 p-4 rounded-bottom" id="productInfoTabContent">
   <div class="tab-pane fade show active" id="description-tab-panel" role="tabpanel">
      <?php echo $product['description']; ?></div>
   <div class="tab-pane fade" id="reviews-tab-panel" role="tabpanel">
      <div class="row">
         <div class="col-md-7">
            <h4 class="mb-4">Tất cả đánh giá (<?php echo count($reviews); ?>)</h4>
            <?php if (empty($reviews)): ?>
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
            <?php else: 
                    $review_count = count($reviews);
                    foreach ($reviews as $index => $review): 
                ?>
            <div class="d-flex mb-4">
               <div class="flex-shrink-0"><i class="bi bi-person-circle fs-2"></i></div>
               <div class="ms-3">
                  <h5 class="mt-0 mb-1"><?php echo htmlspecialchars($review['full_name']); ?></h5>
                  <div class="mb-2">
                     <?php for($i = 1; $i <= 5; $i++): ?><i
                        class="bi <?php echo ($i <= $review['rating']) ? 'bi-star-fill text-warning' : 'bi-star'; ?>"></i><?php endfor; ?>
                  </div>
                  <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                  <small class="text-muted">Đánh giá vào ngày
                     <?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small>
               </div>
            </div>
            <?php if ($index < $review_count - 1): ?>
            <hr><?php endif; ?>
            <?php endforeach; endif; ?>
         </div>
         <div class="col-md-5">
            <h4 class="mb-4">Gửi đánh giá của bạn</h4>
            <?php if ($user_can_review): ?>
            <form action="review-handler.php" method="POST">
               <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
               <div class="mb-3">
                  <label class="form-label">Xếp hạng của bạn</label>
                  <div class="rating-stars">
                     <input type="radio" id="star5" name="rating" value="5" required /><label for="star5"
                        title="5 sao"></label>
                     <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 sao"></label>
                     <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 sao"></label>
                     <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 sao"></label>
                     <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 sao"></label>
                  </div>
               </div>
               <div class="mb-3">
                  <label for="comment" class="form-label">Nhận xét của bạn</label>
                  <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
               </div>
               <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
            </form>
            <?php elseif(isset($_SESSION['user_id'])): ?>
            <div class="alert alert-info">Bạn chỉ có thể đánh giá những sản phẩm bạn đã mua và đơn hàng đã được hoàn
               thành.</div>
            <?php else: ?>
            <div class="alert alert-warning">Vui lòng <a href="auth.php" class="alert-link">đăng nhập</a> để viết đánh
               giá.</div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
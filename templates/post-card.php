<?php
/**
 * Template để hiển thị một thẻ bài viết blog.
 * Yêu cầu phải có biến $post được truyền vào khi gọi file này.
 */
?>
<div class="col-lg-4 col-md-6 mb-4">
   <div class="card h-100 border-0 shadow-sm post-card">
      <a href="<?php echo BASE_URL; ?>blog/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>">
         <img src="/<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top"
            alt="<?php echo htmlspecialchars($post['title']); ?>" style="height: 220px; object-fit: cover;">
      </a>
      <div class="card-body d-flex flex-column">
         <h5 class="card-title">
            <a href="<?php echo BASE_URL; ?>blog/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>"
               class="text-decoration-none text-dark" style="font-family: var(--font-heading);">
               <?php echo htmlspecialchars($post['title']); ?>
            </a>
         </h5>
         <p class="card-text text-muted small mb-3">
            <i class="bi bi-person"></i> <?php echo htmlspecialchars($post['author_name']); ?>
            <span class="mx-2">|</span>
            <i class="bi bi-calendar-event"></i> <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
         </p>
         <p class="card-text flex-grow-1"><?php echo htmlspecialchars($post['excerpt']); ?></p>
         <a href="<?php echo BASE_URL; ?>blog/post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>"
            class="btn btn-outline-primary align-self-start">Đọc thêm</a>
      </div>
   </div>
</div>
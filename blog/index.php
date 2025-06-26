<?php
// Nạp header
include_once __DIR__ . '/../includes/header.php';

// --- LẤY DỮ LIỆU BÀI VIẾT TỪ DATABASE ---
$posts = [];
try {
    // Lấy tất cả các bài viết có trạng thái là 'published' và thông tin tác giả
    $sql = "
        SELECT 
            p.title, p.slug, p.excerpt, p.featured_image, p.created_at,
            u.full_name AS author_name
        FROM posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.status = 'published'
        ORDER BY p.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>

<div class="container my-5">
   <div class="text-center mb-5">
      <h1 style="font-family: var(--font-heading);">Góc chia sẻ của Khóm</h1>
      <p class="lead text-muted">Những kiến thức, mẹo vặt và câu chuyện giúp bạn chăm sóc giấc ngủ tốt hơn.</p>
   </div>

   <div class="row">
      <?php if (empty($posts)): ?>
      <div class="col-12 text-center">
         <p>Chưa có bài viết nào. Vui lòng quay lại sau.</p>
      </div>
      <?php else: ?>
      <?php foreach ($posts as $post): ?>
      <?php 
                    // Tái sử dụng component thẻ bài viết
                    include __DIR__ . '/../templates/post-card.php'; 
                ?>
      <?php endforeach; ?>
      <?php endif; ?>
   </div>

   <nav aria-label="Page navigation" class="mt-4">
      <ul class="pagination justify-content-center">
         <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
         <li class="page-item active"><a class="page-link" href="#">1</a></li>
         <li class="page-item"><a class="page-link" href="#">2</a></li>
         <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
   </nav>
</div>


<?php 
// Nạp footer
include_once __DIR__ . '/../includes/footer.php'; 
?>
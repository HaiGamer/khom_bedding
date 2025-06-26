<?php
include_once __DIR__ . '/../includes/header.php';

// Lấy slug từ URL
$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    echo "<div class='container my-5'><p class='alert alert-danger'>Bài viết không hợp lệ.</p></div>";
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}

// Lấy dữ liệu bài viết từ CSDL
$post = null;
try {
    $sql = "
        SELECT p.*, u.full_name as author_name 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.slug = ? AND p.status = 'published'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
} catch(PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

// Nếu không tìm thấy bài viết, hiển thị thông báo
if (!$post) {
    echo "<div class='container my-5'><p class='alert alert-danger'>Không tìm thấy bài viết.</p></div>";
    include_once __DIR__ . '/../includes/footer.php';
    exit();
}
?>

<div class="container my-5">
   <div class="row justify-content-center">
      <div class="col-lg-8">
         <h1 class="mb-3" style="font-family: var(--font-heading);"><?php echo htmlspecialchars($post['title']); ?></h1>

         <p class="text-muted mb-4">
            <i class="bi bi-person"></i> <?php echo htmlspecialchars($post['author_name']); ?>
            <span class="mx-2">|</span>
            <i class="bi bi-calendar-event"></i> <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
         </p>

         <img src="/<?php echo htmlspecialchars($post['featured_image']); ?>" class="img-fluid rounded mb-4"
            alt="<?php echo htmlspecialchars($post['title']); ?>">

         <div class="post-content">
            <?php 
                    // In ra nội dung HTML trực tiếp vì chúng ta đã lưu nó với các thẻ p, h4...
                    echo $post['content']; 
                ?>
         </div>

         <hr class="my-5">

         <a href="<?php echo BASE_URL; ?>blog/" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại trang Blog
         </a>
      </div>
   </div>
</div>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>
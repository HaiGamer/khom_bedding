<?php
include_once __DIR__ . '/includes/header.php';

$collections = $pdo->query("SELECT * FROM collections ORDER BY name ASC")->fetchAll();
?>

<div class="container my-5">
   <div class="text-center mb-5">
      <h1 style="font-family: var(--font-heading);">Tất cả Bộ sưu tập</h1>
      <p class="lead text-muted">Khám phá các dòng sản phẩm được tuyển chọn theo từng chủ đề đặc biệt của Khóm.</p>
   </div>

   <div class="row g-4">
      <?php foreach($collections as $collection): ?>
      <div class="col-lg-6">
         <div class="card text-white border-0 shadow">
            <img
               src="<?php echo BASE_URL . htmlspecialchars($collection['image_url'] ?? 'assets/images/placeholder.png'); ?>"
               class="card-img" alt="<?php echo htmlspecialchars($collection['name']); ?>"
               style="height: 350px; object-fit: cover;">
            <div class="card-img-overlay d-flex flex-column justify-content-center align-items-center"
               style="background-color: rgba(0,0,0,0.4);">
               <h2 class="card-title text-white" style="font-family: var(--font-heading);">
                  <?php echo htmlspecialchars($collection['name']); ?></h2>
               <p class="card-text text-center"><?php echo htmlspecialchars($collection['description']); ?></p>
               <a href="<?php echo BASE_URL; ?>products.php?collection=<?php echo $collection['slug']; ?>"
                  class="btn btn-primary mt-3">Khám phá ngay</a>
            </div>
         </div>
      </div>
      <?php endforeach; ?>
   </div>
</div>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
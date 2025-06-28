<?php 
require_once __DIR__ . '/../core/config.php'; 

// Lấy dữ liệu danh mục để hiển thị trong menu
try {
    $stmt_categories_menu = $pdo->query("SELECT name, slug FROM categories ORDER BY name ASC");
    $menu_categories = $stmt_categories_menu->fetchAll();
} catch (PDOException $e) {
    $menu_categories = [];
}

$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Khóm Bedding - Nâng tầm phòng ngủ, giá yêu thương cho mọi nhà.</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
   <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

   <script>
   // Khai báo một biến JavaScript toàn cục chứa giá trị từ PHP
   const BASE_URL = '<?php echo BASE_URL; ?>';
   </script>
</head>

<body>

   <header class="main-header">
      <div class="d-none d-lg-block">
         <div class="header-top">
            <div class="container d-flex justify-content-between align-items-center">
               <a href="<?php echo BASE_URL; ?>" class="brand-logo">Khóm Bedding</a>
               <div class="top-actions">
                  <div class="search-container position-relative">
                     <form class="d-none d-lg-flex input-group" style="width: 250px;">
                        <input type="text" class="form-control" id="live-search-input" placeholder="Tìm sản phẩm..."
                           autocomplete="off">
                        <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
                     </form>
                     <div class="search-results-dropdown position-absolute bg-white border shadow-sm mt-1 w-100"
                        id="search-results-container" style="display: none; z-index: 1031;">
                     </div>
                  </div>
                  <a href="tel:0987654321" class="hotline"><i class="bi bi-telephone-fill"></i>
                     <div>
                        <div class="small">Hotline hỗ trợ</div>
                        <div>0987.654.321</div>
                     </div>
                  </a>
                  <a href="<?php echo BASE_URL; ?>cart.php" class="icon-link position-relative"><i class="bi bi-bag"
                        style="color: coral;"></i><span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        id="cart-item-count" style="font-size: 0.7rem;"><?php echo $cart_count; ?></span></a>
               </div>
            </div>
         </div>
         <nav class="header-main-nav navbar navbar-expand-lg">
            <div class="container" style="margin-bottom: -10px;">
               <div class="collapse navbar-collapse main-nav">
                  <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo BASE_URL; ?>products.php" role="button">Sản
                           phẩm</a>
                        <ul class="dropdown-menu">
                           <?php foreach($menu_categories as $category): ?>
                           <li><a class="dropdown-item"
                                 href="<?php echo BASE_URL; ?>products.php?category=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                           </li>
                           <?php endforeach; ?>
                        </ul>
                     </li>
                     <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>collections.php">Bộ Sưu
                           Tập</a></li>
                     <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>blog/">Blog</a></li>
                     <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php">Về Khóm</a></li>
                     <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php">Liên Hệ</a></li>
                  </ul>
                  <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="<?php echo BASE_URL; ?>account.php" role="button">Tài
                           khoản</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                           <?php if (isset($_SESSION['user_id'])): ?>
                           <li><span class="dropdown-item-text">Xin chào,
                                 <?php echo htmlspecialchars($_SESSION['user_name']); ?></span></li>
                           <li>
                              <hr class="dropdown-divider">
                           </li>
                           <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>account.php">Tài khoản của tôi</a>
                           </li>
                           <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">Đăng xuất</a></li>
                           <?php else: ?>
                           <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth.php">Đăng nhập / Đăng ký</a>
                           </li>
                           <?php endif; ?>
                        </ul>
                     </li>
                  </ul>
               </div>
            </div>
         </nav>
      </div>

      <div class="d-lg-none mobile-header">
         <div class="container d-flex justify-content-between align-items-center">
            <button class="btn border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
               aria-controls="mobileMenu">
               <i class="bi bi-list fs-2"></i>
            </button>
            <a href="<?php echo BASE_URL; ?>" class="brand-logo-mobile">Khóm Bedding</a>
            <div class="d-flex align-items-center" style="gap: 1rem;">

               <button class="btn border-0 p-0 icon-link" data-bs-toggle="modal" data-bs-target="#mobileSearchModal">
                  <i class="bi bi-search fs-4"></i>
               </button>


               <a href="<?php echo BASE_URL; ?>cart.php" class="icon-link position-relative"><i class="bi bi-bag"
                     style="font-size: 25px;"></i><span
                     class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                     id="cart-item-count-mobile" style="font-size: 0.7rem;"><?php echo $cart_count; ?></span></a>
            </div>
         </div>
      </div>
   </header>

   <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
      <div class="offcanvas-header">
         <h5 class="offcanvas-title" id="mobileMenuLabel">Menu</h5>
         <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
         <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>products.php">Tất cả Sản phẩm</a></li>

            <?php foreach($menu_categories as $category): ?>
            <li class="nav-item"><a class="nav-link ps-4"
                  href="<?php echo BASE_URL; ?>products.php?category=<?php echo $category['slug']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
            </li>
            <?php endforeach; ?>


            <hr>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>collections.php">Bộ Sưu Tập</a></li>

            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>blog/">Blog</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>about.php">Về Khóm</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>contact.php">Liên Hệ</a></li>
            <hr>
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>account.php">Tài khoản của tôi</a>
            </li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">Đăng xuất</a></li>
            <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth.php">Đăng nhập / Đăng ký</a></li>
            <?php endif; ?>
         </ul>
      </div>
   </div>
   <div class="modal fade" id="mobileSearchModal" tabindex="-1" aria-labelledby="mobileSearchModalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="mobileSearchModalLabel">Tìm kiếm sản phẩm</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <form class="d-flex input-group">
                  <input type="text" class="form-control" id="mobile-search-input" placeholder="Nhập tên sản phẩm..."
                     autocomplete="off">
                  <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
               </form>
               <div class="search-results-dropdown mt-2" id="mobile-search-results">
               </div>
            </div>
         </div>
      </div>
   </div>

   <main>
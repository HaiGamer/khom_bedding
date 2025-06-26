<?php require_once __DIR__ . '/../../core/config.php'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Khóm Bedding - Trang Quản Trị</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
   <link rel="stylesheet" href="<?php echo BASE_URL; ?>admin/assets/css/admin-style.css">
   <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
</head>

<body>
   <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container-fluid">
         <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/">Khóm Bedding Admin</a>
         <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
               <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                     <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_name'] ?? ''); ?>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end">
                     <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>" target="_blank">Xem trang web</a></li>
                     <li>
                        <hr class="dropdown-divider">
                     </li>
                     <li><a class="dropdown-item" href="/logout.php">Đăng xuất</a></li>
                  </ul>
               </li>
            </ul>
         </div>
      </div>
   </nav>
   <?php include_once __DIR__ . '/sidebar.php'; ?>
   <div class="main-content">
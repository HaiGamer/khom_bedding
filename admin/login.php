<?php 
require_once __DIR__ . '/../core/config.php'; 

?>

<!DOCTYPE html>
<html lang="vi">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Login</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
   <div class="container">
      <div class="row justify-content-center align-items-center vh-100">
         <div class="col-lg-4">
            <div class="card">
               <div class="card-body">
                  <h3 class="card-title text-center mb-4">Admin Login</h3>
                  <?php if(isset($_GET['error'])): ?>
                  <div class="alert alert-danger">Email hoặc mật khẩu không đúng, hoặc bạn không có quyền truy cập.
                  </div>
                  <?php endif; ?>
                  <form action="login-handler.php" method="POST">
                     <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                     </div>
                     <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" name="password" required>
                     </div>
                     <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng nhập</button>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</body>

</html>
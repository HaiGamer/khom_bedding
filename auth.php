<?php
// Nạp header (header đã bao gồm config.php và session_start)
include_once __DIR__ . '/includes/header.php'; 

// Nếu người dùng đã đăng nhập, chuyển hướng họ về trang chủ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>

<div class="container my-5">
   <div class="row justify-content-center">
      <div class="col-lg-6">
         <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">

               <ul class="nav nav-pills nav-fill mb-4" id="authTab" role="tablist">
                  <li class="nav-item" role="presentation">
                     <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-panel"
                        type="button" role="tab" aria-controls="login-panel" aria-selected="true">Đăng nhập</button>
                  </li>
                  <li class="nav-item" role="presentation">
                     <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register-panel"
                        type="button" role="tab" aria-controls="register-panel" aria-selected="false">Đăng ký</button>
                  </li>
               </ul>

               <div id="auth-message" class="mb-3"></div>

               <div class="tab-content" id="authTabContent">
                  <div class="tab-pane fade show active" id="login-panel" role="tabpanel" aria-labelledby="login-tab">
                     <h3 class="text-center mb-3" style="font-family: var(--font-heading);">Chào mừng trở lại!</h3>
                     <form id="login-form">
                        <input type="hidden" name="action" value="login">
                        <div class="mb-3"><label for="login-email" class="form-label">Email</label><input type="email"
                              class="form-control" id="login-email" name="email" required></div>
                        <div class="mb-3"><label for="login-password" class="form-label">Mật khẩu</label><input
                              type="password" class="form-control" id="login-password" name="password" required></div>
                        <div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg">Đăng nhập</button>
                        </div>
                     </form>
                  </div>

                  <div class="tab-pane fade" id="register-panel" role="tabpanel" aria-labelledby="register-tab">
                     <h3 class="text-center mb-3" style="font-family: var(--font-heading);">Tạo tài khoản mới</h3>
                     <form id="register-form">
                        <input type="hidden" name="action" value="register">
                        <div class="mb-3"><label for="register-full_name" class="form-label">Họ và tên</label><input
                              type="text" class="form-control" id="register-full_name" name="full_name" required></div>
                        <div class="mb-3"><label for="register-email" class="form-label">Email</label><input
                              type="email" class="form-control" id="register-email" name="email" required></div>
                        <div class="mb-3"><label for="register-password" class="form-label">Mật khẩu</label><input
                              type="password" class="form-control" id="register-password" name="password" required>
                        </div>
                        <div class="mb-3"><label for="register-confirm_password" class="form-label">Nhập lại mật
                              khẩu</label><input type="password" class="form-control" id="register-confirm_password"
                              name="confirm_password" required></div>

                        <div class="d-flex justify-content-center mb-3">
                           <div class="h-captcha" data-sitekey="<?php echo HCAPTCHA_SITE_KEY; ?>"></div>
                        </div>

                        <div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg">Đăng ký</button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<script src="https://js.hcaptcha.com/1/api.js" async defer></script>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
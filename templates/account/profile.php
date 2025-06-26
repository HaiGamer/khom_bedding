<h1 class="mb-4" style="font-family: var(--font-heading);">Thông tin cá nhân</h1>

<div class="card mb-4">
   <div class="card-header">Cập nhật thông tin</div>
   <div class="card-body">
      <form action="account-handler.php" method="POST">
         <input type="hidden" name="action" value="update_profile">
         <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email"
               value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" disabled>
         </div>
         <div class="mb-3">
            <label for="full_name" class="form-label">Họ và tên</label>
            <input type="text" class="form-control" id="full_name" name="full_name"
               value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
         </div>
         <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
      </form>
   </div>
</div>

<div class="card">
   <div class="card-header">Đổi mật khẩu</div>
   <div class="card-body">
      <form action="account-handler.php" method="POST">
         <input type="hidden" name="action" value="change_password">
         <div class="mb-3">
            <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
         </div>
         <div class="mb-3">
            <label for="new_password" class="form-label">Mật khẩu mới</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
         </div>
         <div class="mb-3">
            <label for="confirm_new_password" class="form-label">Xác nhận mật khẩu mới</label>
            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" required>
         </div>
         <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
      </form>
   </div>
</div>
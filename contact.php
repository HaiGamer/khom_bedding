<?php
// === SỬA LỖI: Thêm Content Security Policy để cho phép reCAPTCHA ===
// Dòng này phải được gọi trước bất kỳ mã HTML nào được xuất ra
require_once __DIR__ . '/core/config.php';
header("Content-Security-Policy: frame-ancestors 'self' https://www.google.com/recaptcha/;");

// Nạp header
include_once __DIR__ . '/includes/header.php'; 
?>

<div class="container my-5">
   <div class="text-center mb-5">
      <h1 style="font-family: var(--font-heading);">Liên hệ với Khóm</h1>
      <p class="lead text-muted">Chúng tôi luôn sẵn lòng lắng nghe từ bạn. Đừng ngần ngại gửi cho chúng tôi bất kỳ câu
         hỏi hay phản hồi nào.</p>
   </div>

   <div class="row g-5">
      <div class="col-lg-7">
         <h4 class="mb-4">Gửi tin nhắn cho chúng tôi</h4>

         <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
         <div class="alert alert-success">Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.</div>
         <?php elseif(isset($_GET['status']) && $_GET['status'] == 'error'): ?>
         <div class="alert alert-danger">Có lỗi xảy ra Vui lòng thử lại.</div>
         <?php endif; ?>

         <form id="contact-form" action="contact-handler.php" method="POST">
            <div class="row">
               <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Họ và tên</label>
                  <input type="text" class="form-control" id="name" name="name" required>
               </div>
               <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" required>
               </div>
            </div>
            <div class="mb-3">
               <label for="subject" class="form-label">Chủ đề</label>
               <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
               <label for="message" class="form-label">Nội dung tin nhắn</label>
               <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
            </div>
            <div class="mb-3">
               <div class="h-captcha" data-sitekey="<?php echo HCAPTCHA_SITE_KEY; ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary">Gửi tin nhắn</button>
         </form>
      </div>

      <div class="col-lg-5">
         <h4 class="mb-4">Thông tin của chúng tôi</h4>
         <p><i class="bi bi-geo-alt-fill me-2" style="color: var(--accent-color);"></i>123 Đường ABC, Phường XYZ, Quận
            1, TP. HCM</p>
         <p><i class="bi bi-telephone-fill me-2" style="color: var(--accent-color);"></i>0987.654.321</p>
         <p><i class="bi bi-envelope-fill me-2" style="color: var(--accent-color);"></i>support@khombedding.com</p>
         <hr>
         <div class="ratio ratio-4x3">
            <iframe
               src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.447123987973!2d106.6953281748053!3d10.777014289371626!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f38f9ed8cb5%3A0x1bf728d2197f82b8!2zRGluaCDEkOG7mWMgTOG6rXAgKEhội%20trường%20Thống%20Nhất)!5e0!3m2!1svi!2s!4v1719512061298!5m2!1svi!2s"
               width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"
               referrerpolicy="no-referrer-when-downgrade"></iframe>
         </div>
      </div>
   </div>
</div>

<script src="https://js.hcaptcha.com/1/api.js" async defer></script>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
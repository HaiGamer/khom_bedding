</main>

<footer class="main-footer">
   <div class="container">
      <div class="row">
         <div class="col-lg-4 mb-4 mb-lg-0">
            <h4 class="footer-title">Khóm Bedding</h4>
            <p>Nâng tầm phòng ngủ, giá yêu thương cho mọi nhà.</p>
            <p><strong>Địa chỉ:</strong> 123 Đường ABC, Phường XYZ, Quận 1, TP. HCM</p>
            <p><strong>Hotline:</strong> 0987.654.321</p>
            <p><strong>Email:</strong> support@khombedding.com</p>
         </div>
         <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
            <h5 class="footer-title">Sản phẩm</h5>
            <ul class="list-unstyled footer-links">
               <li><a href="#">Vỏ Chăn</a></li>
               <li><a href="#">Ga Giường</a></li>
               <li><a href="#">Vỏ Gối</a></li>
               <li><a href="#">Ruột Gối</a></li>
            </ul>
         </div>
         <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
            <h5 class="footer-title">Chính sách</h5>
            <ul class="list-unstyled footer-links">
               <li><a href="policy.php">Chính sách bảo mật</a></li>
               <li><a href="#">Chính sách đổi trả</a></li>
               <li><a href="#">Chính sách vận chuyển</a></li>
               <li><a href="#">Câu hỏi thường gặp (FAQ)</a></li>
            </ul>
         </div>
         <div class="col-lg-3">
            <h5 class="footer-title">Đăng ký nhận tin</h5>
            <p>Nhận thông tin về sản phẩm mới và các chương trình khuyến mãi đặc biệt.</p>
            <form>
               <div class="input-group mb-3">
                  <input type="email" class="form-control" placeholder="Email của bạn" aria-label="Email của bạn">
                  <button class="btn btn-primary" type="button">Đăng ký</button>
               </div>
            </form>
            <div class="social-icons mt-4">
               <a href="#" class="icon fs-4 me-3"><i class="bi bi-facebook"></i></a>
               <a href="#" class="icon fs-4 me-3"><i class="bi bi-instagram"></i></a>
               <a href="#" class="icon fs-4 me-3"><i class="bi bi-tiktok"></i></a>
            </div>
         </div>
      </div>
      <hr class="my-4">
      <div class="text-center">
         <p>&copy; <?php echo date('Y'); ?> Khóm Bedding. All Rights Reserved.</p>
      </div>
   </div>
</footer>
<div class="toast-container position-fixed top-0 end-0 p-3">
   <div id="add-to-cart-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-success text-white">
         <i class="bi bi-check-circle-fill me-2"></i>
         <strong class="me-auto">Thành công</strong>
         <small>Vừa xong</small>
         <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="toast-body-content">
         Sản phẩm đã được thêm vào giỏ hàng!
      </div>
   </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>

</html>
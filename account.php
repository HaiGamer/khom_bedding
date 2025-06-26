<?php
// Nạp file cấu hình TRƯỚC TIÊN để có thể sử dụng session
require_once __DIR__ . '/core/config.php';

// --- BƯỚC BẢO VỆ: ĐƯỢC DI CHUYỂN LÊN ĐẦU FILE ---
// Kiểm tra đăng nhập TRƯỚC KHI hiển thị bất kỳ HTML nào
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit();
}

// SAU KHI ĐÃ KIỂM TRA XONG, MỚI NẠP HEADER ĐỂ BẮT ĐẦU HIỂN THỊ
include_once __DIR__ . '/includes/header.php';

// Lấy view từ URL, mặc định là 'orders' (lịch sử đơn hàng)
$view = $_GET['view'] ?? 'orders';

// Hàm tiện ích để format trạng thái (giữ lại ở file chính)
function format_order_status($status) {
    switch ($status) {
        case 'pending': return '<span class="badge bg-warning text-dark">Chờ xử lý</span>';
        case 'processing': return '<span class="badge bg-info text-dark">Đang xử lý</span>';
        case 'shipped': return '<span class="badge bg-primary">Đang giao</span>';
        case 'completed': return '<span class="badge bg-success">Hoàn thành</span>';
        case 'cancelled': return '<span class="badge bg-danger">Đã hủy</span>';
        default: return '<span class="badge bg-secondary">Không xác định</span>';
    }
}
?>

<div class="container my-5">
   <?php if(isset($_GET['success'])): ?>
   <div class="alert alert-success">Cập nhật thành công!</div>
   <?php elseif(isset($_GET['error'])): ?>
   <div class="alert alert-danger">
      <?php
                if($_GET['error'] == 'wrong_password') echo 'Mật khẩu hiện tại không đúng.';
                if($_GET['error'] == 'password_mismatch') echo 'Mật khẩu mới không khớp.';
            ?>
   </div>
   <?php endif; ?>

   <div class="row">
      <div class="col-lg-3">
         <div class="list-group">
            <a href="account.php?view=orders"
               class="list-group-item list-group-item-action <?php if($view == 'orders') echo 'active'; ?>">
               Lịch sử đơn hàng
            </a>
            <a href="account.php?view=profile"
               class="list-group-item list-group-item-action <?php if($view == 'profile') echo 'active'; ?>">
               Thông tin cá nhân
            </a>
            <a href="account.php?view=addresses"
               class="list-group-item list-group-item-action <?php if($view == 'addresses') echo 'active'; ?>">
               Địa chỉ đã lưu
            </a>
            <a href="logout.php" class="list-group-item list-group-item-action text-danger">Đăng xuất</a>
         </div>
      </div>

      <div class="col-lg-9">
         <?php
    // Dựa vào biến $view để nạp template tương ứng
    if ($view == 'profile') {
        include __DIR__ . '/templates/account/profile.php';
    } elseif ($view == 'addresses') { // Thêm điều kiện mới
        include __DIR__ . '/templates/account/addresses.php';
    } else {
        include __DIR__ . '/templates/account/orders.php';
    }
    ?>
      </div>
   </div>
</div>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
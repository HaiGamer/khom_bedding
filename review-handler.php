<?php
require_once __DIR__ . '/core/config.php';

// Chỉ chấp nhận POST và phải đăng nhập
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// 1. Lấy dữ liệu
$product_id = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$user_id = $_SESSION['user_id'];

// Lấy slug sản phẩm để chuyển hướng lại
$stmt_slug = $pdo->prepare("SELECT slug FROM products WHERE id = ?");
$stmt_slug->execute([$product_id]);
$slug = $stmt_slug->fetchColumn();

// Hàm chuyển hướng về lại trang sản phẩm
function redirect_back($slug, $status, $message) {
    header("Location: product-detail.php?slug=$slug&status=$status&message=" . urlencode($message) . "#reviews-tab-panel");
    exit();
}

// 2. Kiểm tra dữ liệu
if ($product_id <= 0 || $rating < 1 || $rating > 5 || empty($comment) || !$slug) {
    redirect_back($slug, 'error', 'Dữ liệu không hợp lệ.');
}

// 3. Kiểm tra lại xem user có thật sự đã mua sản phẩm này không
try {
    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) FROM orders o JOIN order_items oi ON o.id = oi.order_id
        JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE o.user_id = ? AND pv.product_id = ? AND o.status = 'completed'
    ");
    $stmt_check->execute([$user_id, $product_id]);
    if ($stmt_check->fetchColumn() == 0) {
        redirect_back($slug, 'error', 'Bạn không thể đánh giá sản phẩm này.');
    }
    
    // Kiểm tra xem user đã đánh giá sản phẩm này chưa
    $stmt_reviewed = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?");
    $stmt_reviewed->execute([$user_id, $product_id]);
    if ($stmt_reviewed->fetchColumn() > 0) {
        // Nếu đã đánh giá rồi -> Cập nhật lại đánh giá
         $sql = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND product_id = ?";
         $stmt = $pdo->prepare($sql);
         $stmt->execute([$rating, $comment, $user_id, $product_id]);
    } else {
        // Nếu chưa -> Thêm đánh giá mới
        $sql = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$product_id, $user_id, $rating, $comment]);
    }

    redirect_back($slug, 'success', 'Cảm ơn bạn đã gửi đánh giá!');

} catch (PDOException $e) {
    redirect_back($slug, 'error', 'Lỗi hệ thống, vui lòng thử lại sau.');
}
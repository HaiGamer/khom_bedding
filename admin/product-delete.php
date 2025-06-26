<?php
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';

// 1. Lấy ID sản phẩm từ URL và kiểm tra
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    header('Location: products.php?error=invalid_id');
    exit();
}

// Bắt đầu transaction để đảm bảo an toàn dữ liệu
$pdo->beginTransaction();
try {
    // 2. KIỂM TRA AN TOÀN: Sản phẩm có nằm trong đơn hàng nào không?
    $stmt_check = $pdo->prepare("
        SELECT 1 FROM order_items oi
        JOIN product_variants pv ON oi.variant_id = pv.id
        WHERE pv.product_id = ?
        LIMIT 1
    ");
    $stmt_check->execute([$product_id]);
    if ($stmt_check->fetch()) {
        // Nếu có, không cho xóa, rollback và báo lỗi
        $pdo->rollBack();
        header('Location: products.php?error=in_order');
        exit();
    }

    // 3. Lấy danh sách ảnh của tất cả các phiên bản để xóa file
    $stmt_images = $pdo->prepare("SELECT image_url FROM product_variants WHERE product_id = ?");
    $stmt_images->execute([$product_id]);
    $images_to_delete = $stmt_images->fetchAll(PDO::FETCH_COLUMN);

    // 4. Xóa sản phẩm khỏi bảng `products`
    // Do CSDL đã được thiết lập ON DELETE CASCADE, các dòng liên quan trong
    // `product_variants` và `variant_values` sẽ tự động bị xóa theo.
    $stmt_delete = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt_delete->execute([$product_id]);

    // 5. Xóa các file ảnh trên server
    foreach ($images_to_delete as $image_url) {
        if (!empty($image_url) && file_exists(__DIR__ . '/../' . $image_url)) {
            unlink(__DIR__ . '/../' . $image_url);
        }
    }

    // Nếu mọi thứ thành công, commit transaction
    $pdo->commit();
    header('Location: products.php?success=deleted');
    exit();

} catch (Exception $e) {
    // Nếu có lỗi, rollback transaction
    $pdo->rollBack();
    die("Có lỗi xảy ra khi xóa sản phẩm: " . $e->getMessage());
}
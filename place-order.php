<?php
require_once __DIR__ . '/core/config.php';

// --- BƯỚC 1: KIỂM TRA ĐẦU VÀO ---

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php?error=empty_cart');
    exit();
}

// Lấy và làm sạch dữ liệu từ form
$customer_name = trim($_POST['full_name'] ?? '');
$customer_email = trim($_POST['email'] ?? '');
$customer_phone = trim($_POST['phone_number'] ?? '');
$customer_address = trim($_POST['address'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
$note = trim($_POST['note'] ?? '');

// LẤY USER ID TỪ SESSION NẾU ĐÃ ĐĂNG NHẬP
$user_id = $_SESSION['user_id'] ?? null;

if (empty($customer_name) || empty($customer_email) || empty($customer_phone) || empty($customer_address) || empty($payment_method)) {
    header('Location: checkout.php?error=missing_fields');
    exit();
}

// --- BƯỚC 2: XỬ LÝ ĐƠN HÀNG TRONG DATABASE TRANSACTION ---

try {
    $pdo->beginTransaction();

    // 2.1. Lấy lại thông tin sản phẩm từ CSDL
    $variant_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($variant_ids), '?'));
    // Chú ý: fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE) để key của mảng là variant_id
    $sql_products = "SELECT id, price, stock_quantity FROM product_variants WHERE id IN ($placeholders)";
    $stmt_products = $pdo->prepare($sql_products);
    $stmt_products->execute($variant_ids);
    $products_from_db = $stmt_products->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);

    $total_price = 0;
    
    // 2.2. Kiểm tra tồn kho và tính tổng tiền
    foreach ($_SESSION['cart'] as $variant_id => $quantity) {
        if (!isset($products_from_db[$variant_id])) {
            throw new Exception("Sản phẩm với ID $variant_id không tồn tại.");
        }
        
        $product_stock = $products_from_db[$variant_id]['stock_quantity'];
        if ($quantity > $product_stock) {
            throw new Exception("Sản phẩm trong kho không đủ (ID: $variant_id).");
        }
        
        $total_price += $products_from_db[$variant_id]['price'] * $quantity;
    }

    // 2.3. Chèn đơn hàng mới vào bảng `orders` (ĐÃ THÊM user_id)
    $sql_order = "INSERT INTO orders (user_id, customer_name, customer_email, customer_phone, customer_address, order_total, payment_method, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $pdo->prepare($sql_order);
    // Thêm $user_id vào mảng execute
    $stmt_order->execute([$user_id, $customer_name, $customer_email, $customer_phone, $customer_address, $total_price, $payment_method, $note]);
    
    $order_id = $pdo->lastInsertId();

    // 2.4. Chèn các sản phẩm của đơn hàng vào bảng `order_items` và cập nhật tồn kho
    $sql_order_item = "INSERT INTO order_items (order_id, variant_id, quantity, price) VALUES (?, ?, ?, ?)";
    $sql_update_stock = "UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ?";
    
    $stmt_order_item = $pdo->prepare($sql_order_item);
    $stmt_update_stock = $pdo->prepare($sql_update_stock);

    foreach ($_SESSION['cart'] as $variant_id => $quantity) {
        $price_at_purchase = $products_from_db[$variant_id]['price'];
        $stmt_order_item->execute([$order_id, $variant_id, $quantity, $price_at_purchase]);
        $stmt_update_stock->execute([$quantity, $variant_id]);
    }

    $pdo->commit();

    // --- BƯỚC 3: DỌN DẸP VÀ CHUYỂN HƯỚNG ---
    unset($_SESSION['cart']);
    header("Location: order-success.php?order_id=" . $order_id);
    exit();

}   // Nếu có bất kỳ lỗi nào xảy ra, rollback transaction
catch (Exception $e) {
    // --- THÊM DÒNG NÀY VÀO ĐỂ DEBUG ---
    //die("Lỗi chi tiết: " . $e->getMessage());

    // Nếu có bất kỳ lỗi nào xảy ra, rollback transaction
    $pdo->rollBack();
    
    // Ghi lại lỗi để debug (trong thực tế có thể ghi vào file log)
    error_log("Order placement failed: " . $e->getMessage());

    // Chuyển hướng về trang thanh toán với thông báo lỗi chung
    header('Location: checkout.php?error=order_failed');
    exit();
}
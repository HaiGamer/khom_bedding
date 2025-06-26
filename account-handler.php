<?php
require_once __DIR__ . '/core/config.php';

// Bảo vệ: Nếu chưa đăng nhập hoặc không phải POST, thoát
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Xử lý CẬP NHẬT THÔNG TIN
if ($action === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    if (!empty($full_name)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            $stmt->execute([$full_name, $user_id]);
            // Cập nhật lại tên trong session để header hiển thị đúng ngay
            $_SESSION['user_name'] = $full_name;
            header('Location: account.php?view=profile&success=1');
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }
}

// Xử lý ĐỔI MẬT KHẨU
elseif ($action === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Kiểm tra mật khẩu mới có khớp không
    if ($new_password !== $confirm_new_password) {
        header('Location: account.php?view=profile&error=password_mismatch');
        exit();
    }

    try {
        // Lấy mật khẩu đã băm hiện tại của người dùng từ CSDL
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // So sánh mật khẩu hiện tại người dùng nhập với CSDL
        if ($user && password_verify($current_password, $user['password'])) {
            // Nếu đúng, băm mật khẩu mới và cập nhật
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt_update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_update->execute([$hashed_new_password, $user_id]);
            header('Location: account.php?view=profile&success=1');
        } else {
            // Nếu sai, báo lỗi
            header('Location: account.php?view=profile&error=wrong_password');
        }
    } catch (PDOException $e) {
        die("Lỗi: " . $e->getMessage());
    }
}

// Xử lý THÊM ĐỊA CHỈ MỚI
elseif ($action === 'add_address') {
    $address_full_name = trim($_POST['full_name'] ?? '');
    $address_phone = trim($_POST['phone_number'] ?? '');
    $address_line = trim($_POST['address_line'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if (!empty($address_full_name) && !empty($address_phone) && !empty($address_line)) {
        try {
            // Nếu người dùng chọn đây là địa chỉ mặc định
            if ($is_default == 1) {
                // Trước tiên, hãy bỏ trạng thái mặc định của tất cả các địa chỉ khác của người dùng này
                $stmt_reset = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt_reset->execute([$user_id]);
            }
            
            // Sau đó, chèn địa chỉ mới vào CSDL
            $stmt_insert = $pdo->prepare("INSERT INTO user_addresses (user_id, full_name, phone_number, address_line, is_default) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->execute([$user_id, $address_full_name, $address_phone, $address_line, $is_default]);

            header('Location: account.php?view=addresses&success=address_added');
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    } else {
         header('Location: account.php?view=addresses&error=missing_fields');
    }
}

// Xử lý SỬA ĐỊA CHỈ
elseif ($action === 'edit_address') {
    $address_id = (int)($_POST['address_id'] ?? 0);
    $address_full_name = trim($_POST['full_name'] ?? '');
    $address_phone = trim($_POST['phone_number'] ?? '');
    $address_line = trim($_POST['address_line'] ?? '');
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    if ($address_id > 0 && !empty($address_full_name) && !empty($address_phone) && !empty($address_line)) {
        try {
            // Nếu người dùng chọn đây là địa chỉ mặc định
            if ($is_default == 1) {
                // Bỏ trạng thái mặc định của tất cả các địa chỉ khác
                $stmt_reset = $pdo->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
                $stmt_reset->execute([$user_id]);
            }
            
            // Cập nhật địa chỉ được chỉ định
            $stmt_update = $pdo->prepare("UPDATE user_addresses SET full_name = ?, phone_number = ?, address_line = ?, is_default = ? WHERE id = ? AND user_id = ?");
            $stmt_update->execute([$address_full_name, $address_phone, $address_line, $is_default, $address_id, $user_id]);

            header('Location: account.php?view=addresses&success=address_updated');
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    } else {
         header('Location: account.php?view=addresses&error=missing_fields');
    }
}

// Xử lý XÓA ĐỊA CHỈ
elseif ($action === 'delete_address') {
    $address_id = (int)($_POST['address_id'] ?? 0);

    if ($address_id > 0) {
        try {
            // Xóa địa chỉ được chỉ định, đảm bảo nó thuộc về user đang đăng nhập
            $stmt = $pdo->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
            $stmt->execute([$address_id, $user_id]);
            header('Location: account.php?view=addresses&success=address_deleted');
        } catch (PDOException $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }
}


// Nếu không có action nào khớp, quay về trang tài khoản
else {
    header('Location: account.php');
}
exit();
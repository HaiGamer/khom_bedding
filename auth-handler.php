<?php
// === SỬA LỖI: Thêm dòng này để nạp file cấu hình và các hằng số hCaptcha ===
require_once __DIR__ . '/core/config.php';

// Chuẩn bị mảng phản hồi mặc định
$response = ['success' => false, 'message' => 'Hành động không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- XỬ LÝ ĐĂNG KÝ ---
    if ($action === 'register') {
        
        // --- BƯỚC XÁC THỰC HCAPTCHA ---
        $hcaptcha_token = $_POST['h-captcha-response'] ?? '';
        if (empty($hcaptcha_token)) {
            $response['message'] = 'Vui lòng xác thực bạn không phải là robot.';
        } else {
            $data = ['secret' => HCAPTCHA_SECRET_KEY, 'response' => $hcaptcha_token, 'remoteip' => $_SERVER['REMOTE_ADDR']];
            $options = ['http' => ['header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method'  => 'POST', 'content' => http_build_query($data)]];
            $context  = stream_context_create($options);
            $hcaptcha_response = file_get_contents('https://hcaptcha.com/siteverify', false, $context);
            $response_keys = json_decode($hcaptcha_response, true);

            if (!$response_keys || !isset($response_keys["success"]) || !$response_keys["success"]) {
                $response['message'] = 'Xác thực hCaptcha thất bại. Vui lòng thử lại.';
            } else {
                // --- NẾU VƯỢT QUA HCAPTCHA, TIẾP TỤC XỬ LÝ ĐĂNG KÝ ---
                $full_name = trim($_POST['full_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                if (empty($full_name) || empty($email) || empty($password)) {
                    $response['message'] = 'Vui lòng điền đầy đủ thông tin.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $response['message'] = 'Email không hợp lệ.';
                } elseif ($password !== $confirm_password) {
                    $response['message'] = 'Mật khẩu nhập lại không khớp.';
                } else {
                    try {
                        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $response['message'] = 'Email này đã được sử dụng.';
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                            $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
                            $stmt_insert = $pdo->prepare($sql);
                            $stmt_insert->execute([$full_name, $email, $hashed_password]);
                            $response = ['success' => true, 'message' => 'Đăng ký thành công! Vui lòng chuyển qua tab Đăng nhập.'];
                        }
                    } catch (PDOException $e) { $response['message'] = 'Lỗi hệ thống, vui lòng thử lại sau.'; }
                }
            }
        }
    }
    // --- XỬ LÝ ĐĂNG NHẬP ---
    elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $response['message'] = 'Vui lòng điền email và mật khẩu.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user || !password_verify($password, $user['password'])) {
                    $response['message'] = 'Sai email hoặc mật khẩu.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $response = ['success' => true, 'redirect' => 'index.php'];
                }
            } catch (PDOException $e) { $response['message'] = 'Lỗi hệ thống, vui lòng thử lại sau.'; }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit();
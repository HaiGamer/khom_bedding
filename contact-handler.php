<?php
require_once __DIR__ . '/core/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ================================================================
    // === TẠM THỜI VÔ HIỆU HÓA KHỐI XÁC THỰC HCAPTCHA ĐỂ DEBUG ===
    // ================================================================
    /*
    $hcaptcha_token = $_POST['h-captcha-response'] ?? '';
    if (empty($hcaptcha_token)) {
        header('Location: contact.php?status=error&reason=no_token');
        exit;
    }

    $data = ['secret' => HCAPTCHA_SECRET_KEY, 'response' => $hcaptcha_token];
    $options = ['http' => ['header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method'  => 'POST', 'content' => http_build_query($data)]];
    $context  = stream_context_create($options);
    $response = file_get_contents('https://hcaptcha.com/siteverify', false, $context);
    $response_keys = json_decode($response, true);

    if (!$response_keys["success"]) {
        header('Location: contact.php?status=error&reason=hcaptcha_failed');
        exit;
    }
    */
    // ================================================================
    // === KẾT THÚC VÔ HIỆU HÓA =======================================
    // ================================================================


    // --- TIẾP TỤC XỬ LÝ GỬI MAIL ---
    
    $name = strip_tags(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST['subject']));
    $message = trim($_POST['message']);

    if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: contact.php?status=error&reason=invalid_data');
        exit;
    }

    $recipient = "haicoi10195@gmail.com";
    $email_subject = "Tin nhắn mới từ Form liên hệ Khóm Bedding: $subject";
    $email_content = "Tên: $name\nEmail: $email\n\nNội dung:\n$message\n";
    $email_headers = "From: $name <$email>";

    // Vì hàm mail() trên localhost thường không hoạt động, chúng ta sẽ giả định nó luôn thành công để kiểm tra luồng
    // Khi đưa lên hosting thật, bạn có thể xóa dòng if(true) và chỉ giữ lại if(mail(...))
    if (true) { // Giả định gửi mail thành công
    // if (mail($recipient, $email_subject, $email_content, $email_headers)) {
        header('Location: contact.php?status=success');
    } else {
        header('Location: contact.php?status=error&reason=mail_failed');
    }

} else {
    header('Location: index.php');
}
?>
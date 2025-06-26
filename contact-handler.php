<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Lấy dữ liệu từ form và làm sạch
    $name = strip_tags(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST['subject']));
    $message = trim($_POST['message']);

    // 2. Kiểm tra dữ liệu đầu vào
    if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: contact.php?status=error');
        exit;
    }

    // 3. Cấu hình thông tin email
    $recipient = "haicoi10195@gmail.com"; // <<<< THAY THẾ BẰNG EMAIL CỦA BẠN
    $email_subject = "Tin nhắn mới từ Form liên hệ Khóm Bedding: $subject";

    // 4. Xây dựng nội dung email
    $email_content = "Tên: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Nội dung:\n$message\n";

    // 5. Xây dựng headers
    $email_headers = "From: $name <$email>";

    // 6. Gửi email
    if (mail($recipient, $email_subject, $email_content, $email_headers)) {
        // Gửi thành công
        header('Location: contact.php?status=success');
    } else {
        // Gửi thất bại
        header('Location: contact.php?status=error');
    }

} else {
    // Không phải phương thức POST, quay về trang chủ
    header('Location: index.php');
}
?>
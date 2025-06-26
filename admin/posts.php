<?php
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../core/functions.php';

// --- PHẦN LOGIC XỬ LÝ POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // XỬ LÝ THÊM BÀI VIẾT
    if ($action === 'add_post') {
        // ... (logic 'add_post' giữ nguyên như cũ) ...
    }
    // XỬ LÝ SỬA BÀI VIẾT
    elseif ($action === 'edit_post') {
        $post_id = (int)($_POST['post_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $slug = generate_slug($title);

        if ($post_id > 0 && !empty($title)) {
            try {
                // Lấy URL ảnh cũ
                $stmt_old_img = $pdo->prepare("SELECT featured_image FROM posts WHERE id = ?");
                $stmt_old_img->execute([$post_id]);
                $old_image_url = $stmt_old_img->fetchColumn();
                $image_url = $old_image_url; // Giữ lại ảnh cũ làm mặc định

                // Xử lý nếu có ảnh mới được upload
                if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
                    // Xóa file ảnh cũ nếu có
                    if ($old_image_url && file_exists(__DIR__ . '/../' . $old_image_url)) {
                        unlink(__DIR__ . '/../' . $old_image_url);
                    }
                    // Upload ảnh mới (logic tương tự trang add)
                    $file_tmp_path = $_FILES['featured_image']['tmp_name'];
                    $file_ext = strtolower(pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION));
                    $new_file_name = 'post_' . uniqid('', true) . '.' . $file_ext;
                    $dest_path = __DIR__ . '/../uploads/posts/' . $new_file_name;
                    if (move_uploaded_file($file_tmp_path, $dest_path)) {
                        $image_url = 'uploads/posts/' . $new_file_name;
                    }
                }

                $sql = "UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, status = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$title, $slug, $content, $excerpt, $image_url, $status, $post_id]);
                header('Location: posts.php?success=edited');
                exit();
            } catch (PDOException $e) {
                die("Lỗi khi cập nhật bài viết: " . $e->getMessage());
            }
        }
    }
}


// --- PHẦN LOGIC ĐIỀU HƯỚNG VIEW (GET REQUEST) ---
$action = $_GET['action'] ?? 'list';

// Hiển thị thông báo nếu có
if(isset($_GET['success'])): ?>
<div class="alert alert-success">Thao tác thành công!</div>
<?php endif; ?>

<?php
switch ($action) {
    case 'add':
        include __DIR__ . '/includes/post-add-view.php';
        break;

    case 'edit':
        $post_id = (int)($_GET['id'] ?? 0);
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $post = $stmt->fetch();
        if ($post) {
            include __DIR__ . '/includes/post-edit-view.php';
        } else {
            echo '<div class="alert alert-danger">Không tìm thấy bài viết.</div>';
        }
        break;
    
    case 'delete':
        $post_id = (int)($_GET['id'] ?? 0);
        // Lấy đường dẫn ảnh để xóa file
        $stmt_get = $pdo->prepare("SELECT featured_image FROM posts WHERE id = ?");
        $stmt_get->execute([$post_id]);
        $image_to_delete = $stmt_get->fetchColumn();
        
        // Xóa trong CSDL
        $stmt_delete = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt_delete->execute([$post_id]);
        
        // Xóa file vật lý
        if ($image_to_delete && file_exists(__DIR__ . '/../' . $image_to_delete)) {
            unlink(__DIR__ . '/../' . $image_to_delete);
        }
        header('Location: posts.php?success=deleted');
        exit();

    default: // 'list'
        $stmt = $pdo->prepare("SELECT p.*, u.full_name as author_name FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
        $stmt->execute();
        $posts = $stmt->fetchAll();
        include __DIR__ . '/includes/post-list-view.php';
        break;
}

include_once __DIR__ . '/includes/footer.php';
?>
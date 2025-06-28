<?php
// Nạp các file cần thiết cho logic trước tiên
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/functions.php';

// =======================================================
// === KHỐI LOGIC XỬ LÝ (POST & GET ACTIONS)
// === Toàn bộ khối này phải nằm trước khi include header
// =======================================================

// --- XỬ LÝ POST REQUEST (THÊM / SỬA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_post = $_POST['action'] ?? '';

    if ($action_post === 'add_collection' || $action_post === 'edit_collection') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = generate_slug($name);
        $image_url = $_POST['current_image'] ?? null;

        // Xử lý upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['image']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_file_name = 'collection_' . uniqid('', true) . '.' . $file_ext;
            $upload_dir = __DIR__ . '/../uploads/collections/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                if ($image_url && file_exists(__DIR__ . '/../' . $image_url)) { unlink(__DIR__ . '/../' . $image_url); }
                $image_url = 'uploads/collections/' . $new_file_name;
            }
        }

        // Xử lý cho SỬA
        if ($action_post === 'edit_collection') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0 && !empty($name)) {
                $stmt_check = $pdo->prepare("SELECT id FROM collections WHERE slug = ? AND id != ?");
                $stmt_check->execute([$slug, $id]);
                if ($stmt_check->fetch()) { header('Location: collections.php?action=edit&id=' . $id . '&error=slug_exists'); exit(); }
                
                $stmt = $pdo->prepare("UPDATE collections SET name = ?, slug = ?, description = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $image_url, $id]);
                header('Location: collections.php?success=edited');
                exit();
            }
        } 
        // Xử lý cho THÊM
        elseif ($action_post === 'add_collection' && !empty($name)) {
            $stmt_check = $pdo->prepare("SELECT id FROM collections WHERE slug = ?");
            $stmt_check->execute([$slug]);
            if ($stmt_check->fetch()) { header('Location: collections.php?error=slug_exists'); exit(); }

            $stmt = $pdo->prepare("INSERT INTO collections (name, slug, description, image_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $image_url]);
            header('Location: collections.php?success=added');
            exit();
        }
    }
}

// --- XỬ LÝ GET REQUEST (XÓA / HIỂN THỊ FORM SỬA) ---
$action_get = $_GET['action'] ?? 'list';
if ($action_get === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM product_collections WHERE collection_id = ?");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetchColumn() > 0) {
        header('Location: collections.php?error=in_use');
        exit();
    }
    
    $stmt_get_img = $pdo->prepare("SELECT image_url FROM collections WHERE id = ?");
    $stmt_get_img->execute([$id]);
    $image_to_delete = $stmt_get_img->fetchColumn();

    $stmt_delete = $pdo->prepare("DELETE FROM collections WHERE id = ?");
    $stmt_delete->execute([$id]);

    if ($image_to_delete && file_exists(__DIR__ . '/../' . $image_to_delete)) {
        unlink(__DIR__ . '/../' . $image_to_delete);
    }
    
    header('Location: collections.php?success=deleted');
    exit();
}

// =======================================================
// === NẾU KHÔNG CÓ CHUYỂN HƯỚNG, BẮT ĐẦU HIỂN THỊ GIAO DIỆN ===
// =======================================================
include_once __DIR__ . '/includes/header.php';

// Lấy dữ liệu để hiển thị
$is_editing = false;
$collection_to_edit = null;
if ($action_get === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM collections WHERE id = ?");
    $stmt->execute([$id]);
    $collection_to_edit = $stmt->fetch();
    if ($collection_to_edit) { $is_editing = true; }
}

$collections = $pdo->query("SELECT c.*, COUNT(pc.product_id) as product_count FROM collections c LEFT JOIN product_collections pc ON c.id = pc.collection_id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
?>

<div class="row">
   <div class="col-lg-4">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo $is_editing ? 'Chỉnh sửa' : 'Thêm mới'; ?> Bộ sưu tập</h5>
         </div>
         <div class="card-body">
            <?php if(isset($_GET['error']) && $_GET['error'] == 'slug_exists'): ?>
            <div class="alert alert-danger">Tên này đã được sử dụng. Vui lòng chọn một tên khác.</div>
            <?php endif; ?>
            <form action="collections.php" method="POST" enctype="multipart/form-data">
               <?php if ($is_editing): ?>
               <input type="hidden" name="action" value="edit_collection">
               <input type="hidden" name="id" value="<?php echo $collection_to_edit['id']; ?>">
               <?php else: ?>
               <input type="hidden" name="action" value="add_collection">
               <?php endif; ?>
               <div class="mb-3"><label for="name" class="form-label">Tên Bộ sưu tập</label><input type="text"
                     class="form-control" id="name" name="name"
                     value="<?php echo htmlspecialchars($collection_to_edit['name'] ?? ''); ?>" required></div>
               <div class="mb-3"><label for="description" class="form-label">Mô tả</label><textarea class="form-control"
                     id="description" name="description"
                     rows="4"><?php echo htmlspecialchars($collection_to_edit['description'] ?? ''); ?></textarea></div>
               <div class="mb-3">
                  <label for="image" class="form-label">Ảnh đại diện</label>
                  <input class="form-control" type="file" id="image" name="image" accept="image/*">
                  <?php if ($is_editing && !empty($collection_to_edit['image_url'])): ?>
                  <div class="mt-2"><img
                        src="<?php echo BASE_URL . htmlspecialchars($collection_to_edit['image_url']); ?>" height="80"
                        class="img-thumbnail"></div>
                  <input type="hidden" name="current_image"
                     value="<?php echo htmlspecialchars($collection_to_edit['image_url']); ?>">
                  <?php endif; ?>
               </div>
               <button type="submit"
                  class="btn btn-success"><?php echo $is_editing ? 'Lưu thay đổi' : 'Thêm mới'; ?></button>
               <?php if ($is_editing): ?><a href="collections.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
            </form>
         </div>
      </div>
   </div>

   <div class="col-lg-8">
      <h1 class="mb-4">Quản lý Bộ sưu tập</h1>
      <?php if(isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>
      <?php if(isset($_GET['error']) && $_GET['error'] == 'in_use'): ?><div class="alert alert-danger">Không thể xóa bộ
         sưu tập đang được gán cho sản phẩm.</div><?php endif; ?>
      <div class="card">
         <div class="card-body">
            <table class="table table-hover align-middle">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th style="width: 80px;">Ảnh</th>
                     <th>Tên</th>
                     <th class="text-center">Số sản phẩm</th>
                     <th class="text-end">Hành động</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($collections as $collection): ?>
                  <tr>
                     <td class="fw-bold"><?php echo $collection['id']; ?></td>
                     <td><img
                           src="<?php echo BASE_URL . htmlspecialchars($collection['image_url'] ?? 'assets/images/placeholder.png'); ?>"
                           class="img-thumbnail"></td>
                     <td><?php echo htmlspecialchars($collection['name']); ?></td>
                     <td class="text-center"><?php echo $collection['product_count']; ?></td>
                     <td class="text-end">
                        <a href="collections.php?action=edit&id=<?php echo $collection['id']; ?>"
                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i> Sửa</a>
                        <a href="collections.php?action=delete&id=<?php echo $collection['id']; ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa bộ sưu tập này?');"><i
                              class="bi bi-trash-fill"></i> Xóa</a>
                     </td>
                  </tr>
                  <?php endforeach; ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>
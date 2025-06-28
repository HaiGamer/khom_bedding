<?php
require_once __DIR__ . '/auth-guard.php';
require_once __DIR__ . '/../core/config.php'; // THÊM DÒNG QUAN TRỌNG NÀY

// --- XỬ LÝ POST REQUEST (THÊM / SỬA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    
    if ($action === 'add_attribute' && !empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO attributes (name) VALUES (?)");
        $stmt->execute([$name]);
        header('Location: attributes.php?success=added');
        exit();
    }
    
    elseif ($action === 'edit_attribute') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && !empty($name)) {
            $stmt = $pdo->prepare("UPDATE attributes SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
            header('Location: attributes.php?success=edited');
            exit();
        }
    }
}

// --- XỬ LÝ GET REQUEST (XÓA) ---
$action_get = $_GET['action'] ?? '';
if ($action_get === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    // Kiểm tra an toàn: thuộc tính có giá trị nào không?
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM attribute_values WHERE attribute_id = ?");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetchColumn() > 0) {
        header('Location: attributes.php?error=in_use');
    } else {
        $stmt_delete = $pdo->prepare("DELETE FROM attributes WHERE id = ?");
        $stmt_delete->execute([$id]);
        header('Location: attributes.php?success=deleted');
    }
    exit();
}

include_once __DIR__ . '/includes/header.php';

// Mặc định, lấy dữ liệu để hiển thị form Sửa (nếu có)
$is_editing = false;
$attribute_to_edit = null;
if ($action_get === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM attributes WHERE id = ?");
    $stmt->execute([$id]);
    $attribute_to_edit = $stmt->fetch();
    if ($attribute_to_edit) {
        $is_editing = true;
    }
}

// Lấy tất cả thuộc tính để hiển thị danh sách
$attributes = $pdo->query("SELECT * FROM attributes ORDER BY name ASC")->fetchAll();
?>

<div class="row">
   <div class="col-lg-4">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo $is_editing ? 'Chỉnh sửa Thuộc tính' : 'Thêm Thuộc tính mới'; ?></h5>
         </div>
         <div class="card-body">
            <form action="attributes.php" method="POST">
               <?php if ($is_editing): ?>
               <input type="hidden" name="action" value="edit_attribute">
               <input type="hidden" name="id" value="<?php echo $attribute_to_edit['id']; ?>">
               <?php else: ?>
               <input type="hidden" name="action" value="add_attribute">
               <?php endif; ?>
               <div class="mb-3">
                  <label for="name" class="form-label">Tên Thuộc tính (ví dụ: Kích thước, Màu sắc...)</label>
                  <input type="text" class="form-control" id="name" name="name"
                     value="<?php echo htmlspecialchars($attribute_to_edit['name'] ?? ''); ?>" required>
               </div>
               <button type="submit"
                  class="btn btn-success"><?php echo $is_editing ? 'Lưu thay đổi' : 'Thêm mới'; ?></button>
               <?php if ($is_editing): ?>
               <a href="attributes.php" class="btn btn-secondary">Hủy</a>
               <?php endif; ?>
            </form>
         </div>
      </div>
   </div>

   <div class="col-lg-8">
      <h1 class="mb-4">Quản lý Thuộc tính</h1>
      <?php if(isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>
      <?php if(isset($_GET['error'])): ?><div class="alert alert-danger">
         <?php if($_GET['error'] == 'in_use') echo 'Không thể xóa thuộc tính đang được sử dụng.'; else echo 'Có lỗi xảy ra.'; ?>
      </div><?php endif; ?>
      <div class="card">
         <div class="card-body">
            <table class="table table-hover align-middle">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Tên Thuộc tính</th>
                     <th class="text-end">Hành động</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($attributes as $attribute): ?>
                  <tr>
                     <td class="fw-bold"><?php echo $attribute['id']; ?></td>
                     <td><?php echo htmlspecialchars($attribute['name']); ?></td>
                     <td class="text-end">
                        <a href="attribute-values.php?attribute_id=<?php echo $attribute['id']; ?>"
                           class="btn btn-sm btn-info"><i class="bi bi-card-list"></i> Quản lý giá trị</a>
                        <a href="attributes.php?action=edit&id=<?php echo $attribute['id']; ?>"
                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i> Sửa</a>
                        <a href="attributes.php?action=delete&id=<?php echo $attribute['id']; ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa?');"><i class="bi bi-trash-fill"></i>
                           Xóa</a>
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
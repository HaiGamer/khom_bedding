<?php
require_once __DIR__ . '/auth-guard.php';
include_once __DIR__ . '/includes/header.php';

// --- PHẦN LOGIC PHP ---

// 1. Lấy và kiểm tra attribute_id từ URL. Đây là ID của thuộc tính cha (ví dụ: "Màu sắc")
$attribute_id = isset($_GET['attribute_id']) ? (int)$_GET['attribute_id'] : 0;
if ($attribute_id === 0) {
    header('Location: attributes.php?error=invalid_id');
    exit();
}

// Lấy tên của thuộc tính cha để hiển thị tiêu đề cho rõ ràng
$stmt_attr = $pdo->prepare("SELECT name FROM attributes WHERE id = ?");
$stmt_attr->execute([$attribute_id]);
$attribute = $stmt_attr->fetch();
if (!$attribute) {
    header('Location: attributes.php?error=not_found');
    exit();
}
$attribute_name = $attribute['name'];


// 2. XỬ LÝ POST REQUEST (THÊM / SỬA GIÁ TRỊ)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $value = trim($_POST['value'] ?? '');

    if ($action === 'add_value' && !empty($value)) {
        // Kiểm tra xem giá trị đã tồn tại cho thuộc tính này chưa
        $stmt_check = $pdo->prepare("SELECT 1 FROM attribute_values WHERE attribute_id = ? AND value = ?");
        $stmt_check->execute([$attribute_id, $value]);
        if (!$stmt_check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO attribute_values (attribute_id, value) VALUES (?, ?)");
            $stmt->execute([$attribute_id, $value]);
        }
        header('Location: attribute-values.php?attribute_id=' . $attribute_id . '&success=added');
        exit();
    }
    
    elseif ($action === 'edit_value') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0 && !empty($value)) {
            $stmt = $pdo->prepare("UPDATE attribute_values SET value = ? WHERE id = ? AND attribute_id = ?");
            $stmt->execute([$value, $id, $attribute_id]);
            header('Location: attribute-values.php?attribute_id=' . $attribute_id . '&success=edited');
            exit();
        }
    }
}

// 3. XỬ LÝ GET REQUEST (XÓA GIÁ TRỊ)
$action_get = $_GET['action'] ?? '';
if ($action_get === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    // Kiểm tra an toàn: giá trị này có đang được sản phẩm nào dùng không?
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM variant_values WHERE attribute_value_id = ?");
    $stmt_check->execute([$id]);
    if ($stmt_check->fetchColumn() > 0) {
        header('Location: attribute-values.php?attribute_id=' . $attribute_id . '&error=in_use');
    } else {
        $stmt_delete = $pdo->prepare("DELETE FROM attribute_values WHERE id = ?");
        $stmt_delete->execute([$id]);
        header('Location: attribute-values.php?attribute_id=' . $attribute_id . '&success=deleted');
    }
    exit();
}

// 4. Lấy dữ liệu để hiển thị form Sửa (nếu có)
$is_editing = false;
$value_to_edit = null;
if ($action_get === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM attribute_values WHERE id = ? AND attribute_id = ?");
    $stmt->execute([$id, $attribute_id]);
    $value_to_edit = $stmt->fetch();
    if ($value_to_edit) {
        $is_editing = true;
    }
}

// 5. Lấy tất cả các giá trị của thuộc tính này để hiển thị danh sách
$stmt_values = $pdo->prepare("SELECT * FROM attribute_values WHERE attribute_id = ? ORDER BY value ASC");
$stmt_values->execute([$attribute_id]);
$attribute_values = $stmt_values->fetchAll();
?>

<div class="row">
   <div class="col-lg-4">
      <div class="card">
         <div class="card-header">
            <h5 class="mb-0"><?php echo $is_editing ? 'Chỉnh sửa Giá trị' : 'Thêm Giá trị mới'; ?></h5>
         </div>
         <div class="card-body">
            <form action="attribute-values.php?attribute_id=<?php echo $attribute_id; ?>" method="POST">
               <?php if ($is_editing): ?>
               <input type="hidden" name="action" value="edit_value">
               <input type="hidden" name="id" value="<?php echo $value_to_edit['id']; ?>">
               <?php else: ?>
               <input type="hidden" name="action" value="add_value">
               <?php endif; ?>
               <div class="mb-3">
                  <label for="value" class="form-label">Giá trị cho thuộc tính
                     "<?php echo htmlspecialchars($attribute_name); ?>"</label>
                  <input type="text" class="form-control" id="value" name="value"
                     value="<?php echo htmlspecialchars($value_to_edit['value'] ?? ''); ?>" required>
               </div>
               <button type="submit"
                  class="btn btn-success"><?php echo $is_editing ? 'Lưu thay đổi' : 'Thêm mới'; ?></button>
               <?php if ($is_editing): ?>
               <a href="attribute-values.php?attribute_id=<?php echo $attribute_id; ?>"
                  class="btn btn-secondary">Hủy</a>
               <?php endif; ?>
            </form>
         </div>
      </div>
   </div>

   <div class="col-lg-8">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h1 class="mb-0">Quản lý Giá trị cho: <span
               class="text-primary"><?php echo htmlspecialchars($attribute_name); ?></span></h1>
         <a href="attributes.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Quay lại DS Thuộc tính</a>
      </div>

      <?php if(isset($_GET['success'])): ?><div class="alert alert-success">Thao tác thành công!</div><?php endif; ?>
      <?php if(isset($_GET['error'])): ?><div class="alert alert-danger">
         <?php if($_GET['error'] == 'in_use') echo 'Không thể xóa giá trị đang được sản phẩm sử dụng.'; else echo 'Có lỗi xảy ra.'; ?>
      </div><?php endif; ?>

      <div class="card">
         <div class="card-body">
            <table class="table table-hover align-middle">
               <thead>
                  <tr>
                     <th>ID</th>
                     <th>Giá trị</th>
                     <th class="text-end">Hành động</th>
                  </tr>
               </thead>
               <tbody>
                  <?php if (empty($attribute_values)): ?>
                  <tr>
                     <td colspan="3" class="text-center text-muted">Chưa có giá trị nào cho thuộc tính này.</td>
                  </tr>
                  <?php else: ?>
                  <?php foreach ($attribute_values as $value): ?>
                  <tr>
                     <td class="fw-bold"><?php echo $value['id']; ?></td>
                     <td><?php echo htmlspecialchars($value['value']); ?></td>
                     <td class="text-end">
                        <a href="attribute-values.php?attribute_id=<?php echo $attribute_id; ?>&action=edit&id=<?php echo $value['id']; ?>"
                           class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-fill"></i> Sửa</a>
                        <a href="attribute-values.php?attribute_id=<?php echo $attribute_id; ?>&action=delete&id=<?php echo $value['id']; ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Bạn có chắc chắn muốn xóa?');"><i class="bi bi-trash-fill"></i>
                           Xóa</a>
                     </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>

<?php 
include_once __DIR__ . '/includes/footer.php'; 
?>